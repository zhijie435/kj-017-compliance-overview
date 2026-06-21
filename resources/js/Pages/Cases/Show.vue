<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import RiskBadge from '@/Components/RiskBadge.vue';
import RiskGauge from '@/Components/RiskGauge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatDate, formatDateTime, formatCapital, idTypeLabel, reviewDecisionChip, REVIEW_LEVEL_META } from '@/utils/kyb';
import {
    ChevronLeft, FileText, Download, ShieldCheck, Search,
    Building2, Users, Paperclip, Gavel, CheckCircle2, XCircle, RotateCcw,
    AlertTriangle, MapPin, Calendar, Briefcase, Hash,
} from 'lucide-vue-next';

const props = defineProps({
    case: Object,
});
const c = computed(() => props.case);
const page = usePage();
const user = computed(() => page.props.auth?.user);

const risk = computed(() => c.value.risk_assessment);
const factors = computed(() => risk.value?.factors || []);
const hitFactors = computed(() => factors.value.filter(f => f.hit));
const flags = computed(() => {
    if (!risk.value) return [];
    return [
        { key: 'pep', label: 'PEP 命中', hit: risk.value.pep_hit },
        { key: 'sanctions', label: '制裁名单', hit: risk.value.sanctions_hit },
        { key: 'adverse', label: '负面舆情', hit: risk.value.adverse_media },
        { key: 'shell', label: '空壳特征', hit: risk.value.shell_company },
    ];
});

const canScreen = computed(() => c.value.status === 'draft');
const canReview = computed(() => ['pending_review', 'reviewing'].includes(c.value.status) && ['analyst', 'manager'].includes(user.value?.role));
const reviewLevelHint = computed(() => {
    if (user.value?.role === 'manager') return '终审决议';
    return '初审决议';
});

const reviewForm = useForm({ decision: 'approve', comment: '' });
function submitReview() {
    reviewForm.post(route('cases.review', c.value.id), {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset(),
    });
}

function runScreen() {
    router.post(route('cases.screen', c.value.id), {}, { preserveScroll: true });
}

function maskId(num) {
    if (!num) return '—';
    const s = String(num);
    if (s.length <= 6) return s;
    return s.slice(0, 3) + '••••' + s.slice(-4);
}

const COUNTRY_LABELS = { CN: '中国', US: '美国', OTHER: '其他' };

const businessInfo = computed(() => {
    const b = c.value.business || {};
    const info = [];

    const country = COUNTRY_LABELS[b.country] || b.country || '—';
    info.push({ label: '注册国家', value: country, icon: MapPin });

    if (b.country === 'US' && b.ein) {
        info.push({ label: 'EIN (雇主识别号)', value: b.ein, mono: true, icon: Hash });
    } else if (b.country === 'CN' && b.uscc) {
        info.push({ label: '统一社会信用代码', value: b.uscc, mono: true, icon: Hash });
    } else if (b.uscc) {
        info.push({ label: '企业注册号', value: b.uscc, mono: true, icon: Hash });
    }

    info.push({ label: '法定代表人', value: b.legal_rep, icon: Users });
    info.push({ label: '注册资本', value: formatCapital(b.registered_capital), icon: Briefcase });
    info.push({ label: '成立日期', value: formatDate(b.establish_date), icon: Calendar });
    info.push({ label: '所属行业', value: b.industry, icon: Briefcase });
    info.push({ label: '注册地区', value: b.region, icon: MapPin });

    return info;
});
</script>

<template>
    <Head :title="`案件 ${c.case_no}`" />
    <AuthenticatedLayout>
        <PageHeader :eyebrow="`案件编号 · ${c.case_no}`" :title="c.business?.name || '未命名企业'" :description="c.summary">
            <template #actions>
                <Link :href="route('cases.index')" class="btn-ghost"><ChevronLeft class="h-4 w-4" /> 返回</Link>
                <a :href="route('cases.report', c.id)" target="_blank" class="btn-ghost"><Download class="h-4 w-4" /> 合规报告</a>
            </template>
        </PageHeader>

        <!-- Meta strip -->
        <div class="ledger-card mb-5 p-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <div class="flex items-center gap-2">
                    <span class="eyebrow">状态</span>
                    <StatusBadge :status="c.status" />
                </div>
                <div class="flex items-center gap-2">
                    <span class="eyebrow">风险</span>
                    <RiskBadge v-if="c.risk_level" :level="c.risk_level" :score="c.risk_score" />
                    <span v-else class="text-xs text-ink-400">未筛查</span>
                </div>
                <div class="h-4 w-px bg-ink-700"></div>
                <div class="flex items-center gap-1.5 text-xs text-ink-300">
                    <Users class="h-3.5 w-3.5" /> 创建人 <span class="text-ink-100">{{ c.creator?.name || '—' }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-ink-300">
                    <Gavel class="h-3.5 w-3.5" /> 负责人 <span class="text-ink-100">{{ c.assignee?.name || '—' }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-ink-300">
                    <Calendar class="h-3.5 w-3.5" /> 创建 <span class="font-mono text-ink-200">{{ formatDateTime(c.created_at) }}</span>
                </div>
                <div v-if="c.submitted_at" class="flex items-center gap-1.5 text-xs text-ink-300">
                    <Search class="h-3.5 w-3.5" /> 提交 <span class="font-mono text-ink-200">{{ formatDateTime(c.submitted_at) }}</span>
                </div>
                <div v-if="c.decided_at" class="flex items-center gap-1.5 text-xs text-ink-300">
                    <CheckCircle2 class="h-3.5 w-3.5 text-emerald2" /> 决议 <span class="font-mono text-ink-200">{{ formatDateTime(c.decided_at) }}</span>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <button v-if="canScreen" @click="runScreen" class="btn-gold">
                        <Search class="h-4 w-4" /> 发起风险筛查
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            <!-- Left: business + ubos + docs -->
            <div class="space-y-5 lg:col-span-2">
                <!-- Business -->
                <SectionCard title="企业主体信息" eyebrow="BUSINESS" :icon="Building2">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="f in businessInfo" :key="f.label" class="rounded-md border border-ink-700/60 bg-ink-900/40 p-3">
                            <div class="flex items-center gap-1.5 text-ink-400">
                                <component :is="f.icon" class="h-3.5 w-3.5" />
                                <span class="text-[10px] uppercase tracking-wider">{{ f.label }}</span>
                            </div>
                            <p class="mt-1.5 text-sm text-ink-100" :class="{ 'font-mono': f.mono }">{{ f.value || '—' }}</p>
                        </div>
                    </div>
                    <div v-if="c.business?.address" class="mt-3 rounded-md border border-ink-700/60 bg-ink-900/40 p-3">
                        <div class="flex items-center gap-1.5 text-ink-400">
                            <MapPin class="h-3.5 w-3.5" /><span class="text-[10px] uppercase tracking-wider">注册地址</span>
                        </div>
                        <p class="mt-1.5 text-sm text-ink-100">{{ c.business.address }}</p>
                    </div>
                    <div v-if="c.business?.scope" class="mt-3 rounded-md border border-ink-700/60 bg-ink-900/40 p-3">
                        <div class="flex items-center gap-1.5 text-ink-400">
                            <FileText class="h-3.5 w-3.5" /><span class="text-[10px] uppercase tracking-wider">经营范围</span>
                        </div>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-200">{{ c.business.scope }}</p>
                    </div>
                </SectionCard>

                <!-- UBOs -->
                <SectionCard title="受益人 (UBO)" eyebrow="BENEFICIAL OWNERS" :icon="Users">
                    <div v-if="c.beneficial_owners?.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] uppercase tracking-wider text-ink-400">
                                    <th class="px-2 py-2 font-medium">姓名</th>
                                    <th class="px-2 py-2 font-medium">证件类型</th>
                                    <th class="px-2 py-2 font-medium">证件号码</th>
                                    <th class="px-2 py-2 font-medium">持股</th>
                                    <th class="px-2 py-2 font-medium">PEP</th>
                                    <th class="px-2 py-2 font-medium">核验</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-700/50">
                                <tr v-for="ubo in c.beneficial_owners" :key="ubo.id" class="table-row-hover">
                                    <td class="px-2 py-2.5 text-ink-100">{{ ubo.name }}</td>
                                    <td class="px-2 py-2.5 text-xs text-ink-300">{{ idTypeLabel(ubo.id_type) }}</td>
                                    <td class="px-2 py-2.5 font-mono text-xs text-ink-200">{{ maskId(ubo.id_number) }}</td>
                                    <td class="px-2 py-2.5">
                                        <span class="font-mono text-gold-300">{{ ubo.ownership_percent }}%</span>
                                    </td>
                                    <td class="px-2 py-2.5">
                                        <span v-if="ubo.is_pep" class="chip border-amber2/40 bg-amber2/10 text-amber2">是</span>
                                        <span v-else class="text-xs text-ink-400">否</span>
                                    </td>
                                    <td class="px-2 py-2.5">
                                        <span class="text-[11px] text-ink-300">{{ ubo.verification_status || 'pending' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <EmptyState v-else title="暂无受益人" />
                </SectionCard>

                <!-- Documents -->
                <SectionCard title="证照文件" eyebrow="DOCUMENTS" :icon="Paperclip">
                    <div v-if="c.documents?.length" class="space-y-2">
                        <div v-for="doc in c.documents" :key="doc.id" class="flex items-center gap-3 rounded-md border border-ink-700/60 bg-ink-900/40 p-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-ink-700 text-gold-300">
                                <Paperclip class="h-4 w-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm text-ink-100">{{ doc.filename }}</p>
                                <p class="text-[11px] text-ink-400">{{ doc.mime_type }} · {{ doc.size ? Math.round(doc.size/1024) + ' KB' : '' }}</p>
                            </div>
                            <span class="chip border-ink-500/50 bg-ink-700/40 text-ink-300">{{ doc.ocr_status || 'pending' }}</span>
                        </div>
                    </div>
                    <EmptyState v-else title="未上传证照" description="该案件暂无证照文件" />
                </SectionCard>
            </div>

            <!-- Right: risk + reviews -->
            <div class="space-y-5">
                <!-- Risk assessment -->
                <SectionCard title="风险评估" eyebrow="RISK SCREENING" :icon="ShieldCheck">
                    <template v-if="risk">
                        <div class="flex flex-col items-center">
                            <RiskGauge :score="risk.score" />
                            <p class="mt-2 text-[11px] text-ink-400">筛查时间 · {{ formatDateTime(risk.screened_at) }}</p>
                        </div>

                        <!-- Flags -->
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div v-for="f in flags" :key="f.key"
                                class="flex items-center gap-2 rounded-md border px-2.5 py-2 text-xs"
                                :class="f.hit ? 'border-crimson/40 bg-crimson/10 text-crimson' : 'border-ink-700 bg-ink-900/40 text-ink-400'">
                                <AlertTriangle v-if="f.hit" class="h-3.5 w-3.5" />
                                <CheckCircle2 v-else class="h-3.5 w-3.5 text-emerald2" />
                                {{ f.label }}
                            </div>
                        </div>

                        <!-- Factors -->
                        <div class="mt-4">
                            <p class="eyebrow mb-2">风险因子分解</p>
                            <div class="space-y-2">
                                <div v-for="(f, i) in factors" :key="i" class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span :class="f.hit ? 'text-ink-100' : 'text-ink-400'">{{ f.label }}</span>
                                            <span class="font-mono" :class="f.hit ? 'text-crimson' : 'text-ink-400'">+{{ f.weight }}</span>
                                        </div>
                                        <div class="mt-1 h-1 overflow-hidden rounded-full bg-ink-700/50">
                                            <div class="h-full rounded-full transition-all duration-700"
                                                :class="f.hit ? 'bg-crimson' : 'bg-ink-600'"
                                                :style="{ width: (f.weight / 40 * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-ink-700/60 pt-2.5 text-xs">
                                <span class="text-ink-300">命中因子</span>
                                <span class="font-mono text-crimson">{{ hitFactors.length }} / {{ factors.length }}</span>
                            </div>
                        </div>
                    </template>
                    <EmptyState v-else :icon="Search" title="尚未进行风险筛查" description="发起筛查后将生成风险评估">
                        <template v-if="canScreen" #action>
                            <button @click="runScreen" class="btn-gold"><Search class="h-4 w-4" /> 发起筛查</button>
                        </template>
                    </EmptyState>
                </SectionCard>

                <!-- Review form -->
                <SectionCard v-if="canReview" title="审核决议" :eyebrow="reviewLevelHint" :icon="Gavel">
                    <form @submit.prevent="submitReview" class="space-y-3">
                        <div>
                            <label class="field-label">决议</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="reviewForm.decision = 'approve'"
                                    class="rounded-md border px-3 py-2 text-xs transition"
                                    :class="reviewForm.decision === 'approve' ? 'border-emerald2/50 bg-emerald2/10 text-emerald2' : 'border-ink-600 text-ink-300 hover:bg-ink-800'">
                                    <CheckCircle2 class="mx-auto mb-1 h-4 w-4" /> 通过
                                </button>
                                <button type="button" @click="reviewForm.decision = 'reject'"
                                    class="rounded-md border px-3 py-2 text-xs transition"
                                    :class="reviewForm.decision === 'reject' ? 'border-crimson/50 bg-crimson/10 text-crimson' : 'border-ink-600 text-ink-300 hover:bg-ink-800'">
                                    <XCircle class="mx-auto mb-1 h-4 w-4" /> 驳回
                                </button>
                                <button type="button" @click="reviewForm.decision = 'return'"
                                    class="rounded-md border px-3 py-2 text-xs transition"
                                    :class="reviewForm.decision === 'return' ? 'border-amber2/50 bg-amber2/10 text-amber2' : 'border-ink-600 text-ink-300 hover:bg-ink-800'">
                                    <RotateCcw class="mx-auto mb-1 h-4 w-4" /> 退回
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">审核意见 <span class="text-crimson">*</span></label>
                            <textarea v-model="reviewForm.comment" rows="4" class="field-input resize-none" placeholder="请填写审核意见与依据…"></textarea>
                            <p v-if="reviewForm.errors.comment" class="mt-1 text-xs text-crimson">{{ reviewForm.errors.comment }}</p>
                        </div>
                        <button type="submit" :disabled="reviewForm.processing" class="btn-gold w-full">
                            <Gavel class="h-4 w-4" /> 提交决议
                        </button>
                    </form>
                </SectionCard>

                <!-- Reviews timeline -->
                <SectionCard title="审核轨迹" eyebrow="REVIEW TRAIL" :icon="Gavel">
                    <ol v-if="c.reviews?.length" class="relative space-y-4 before:absolute before:left-[7px] before:top-1 before:h-[calc(100%-1rem)] before:w-px before:bg-ink-700">
                        <li v-for="r in c.reviews" :key="r.id" class="relative flex gap-3 pl-6">
                            <span class="absolute left-0 top-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full border"
                                :class="r.decision === 'approve' ? 'border-emerald2 bg-emerald2/15' : r.decision === 'reject' ? 'border-crimson bg-crimson/15' : 'border-amber2 bg-amber2/15'">
                                <component :is="r.decision === 'approve' ? CheckCircle2 : r.decision === 'reject' ? XCircle : RotateCcw"
                                    class="h-2 w-2"
                                    :class="r.decision === 'approve' ? 'text-emerald2' : r.decision === 'reject' ? 'text-crimson' : 'text-amber2'" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-ink-100">{{ r.user?.name }}</span>
                                    <span class="chip" :class="reviewDecisionChip(r.decision).chipClass">{{ reviewDecisionChip(r.decision).label }}</span>
                                    <span class="text-[10px] text-ink-400">{{ REVIEW_LEVEL_META[r.level]?.label || r.level }}</span>
                                </div>
                                <p class="mt-1 text-xs leading-relaxed text-ink-300">{{ r.comment }}</p>
                                <p class="mt-1 text-[10px] text-ink-500">{{ formatDateTime(r.created_at) }}</p>
                            </div>
                        </li>
                    </ol>
                    <EmptyState v-else title="暂无审核记录" />
                </SectionCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
