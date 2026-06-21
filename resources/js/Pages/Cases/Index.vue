<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import RiskBadge from '@/Components/RiskBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatDateTime, STATUS_META, RISK_META } from '@/utils/kyb';
import { FolderSearch, Search, SlidersHorizontal, ArrowRight, FilePlus2, Building2 } from 'lucide-vue-next';

const props = defineProps({
    cases: Object,
    filters: Object,
});

const search = ref(props.filters.q || '');
const statusFilter = ref(props.filters.status || '');
const riskFilter = ref(props.filters.risk_level || '');

let debounce;
watch([search, statusFilter, riskFilter], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('cases.index'), {
            q: search.value || undefined,
            status: statusFilter.value || undefined,
            risk_level: riskFilter.value || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

function resetFilters() {
    search.value = '';
    statusFilter.value = '';
    riskFilter.value = '';
}
</script>

<template>
    <Head title="核验案件" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="KYB · 案件管理" title="核验案件" description="企业身份核验案件全量列表">
            <template #actions>
                <Link :href="route('cases.create')" class="btn-gold">
                    <FilePlus2 class="h-4 w-4" /> 新建核验
                </Link>
            </template>
        </PageHeader>

        <!-- Filters -->
        <div class="ledger-card mb-4 flex flex-col gap-3 p-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input v-model="search" type="text" placeholder="搜索企业名称、统一社会信用代码或案件编号…"
                    class="field-input pl-9" />
            </div>
            <div class="flex items-center gap-2">
                <SlidersHorizontal class="h-4 w-4 text-ink-400" />
                <select v-model="statusFilter" class="field-input !w-auto !py-2">
                    <option value="">全部状态</option>
                    <option v-for="(m, k) in STATUS_META" :key="k" :value="k">{{ m.label }}</option>
                </select>
                <select v-model="riskFilter" class="field-input !w-auto !py-2">
                    <option value="">全部风险</option>
                    <option v-for="(m, k) in RISK_META" :key="k" :value="k">{{ m.label }}</option>
                </select>
                <button @click="resetFilters" class="btn-ghost !py-2">重置</button>
            </div>
        </div>

        <!-- Table -->
        <div class="ledger-card overflow-hidden">
            <div v-if="cases.data.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-700/60 bg-ink-850/60 text-left text-[11px] uppercase tracking-wider text-ink-400">
                            <th class="px-4 py-3 font-medium">案件编号</th>
                            <th class="px-4 py-3 font-medium">企业主体</th>
                            <th class="px-4 py-3 font-medium">行业 / 地区</th>
                            <th class="px-4 py-3 font-medium">风险等级</th>
                            <th class="px-4 py-3 font-medium">状态</th>
                            <th class="px-4 py-3 font-medium">负责人</th>
                            <th class="px-4 py-3 font-medium">更新时间</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-700/50">
                        <tr v-for="c in cases.data" :key="c.id" class="table-row-hover">
                            <td class="px-4 py-3">
                                <Link :href="route('cases.show', c.id)" class="font-mono text-xs text-gold-300 hover:underline">{{ c.case_no }}</Link>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-ink-600 bg-ink-800 text-ink-300">
                                        <Building2 class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-ink-100">{{ c.business?.name }}</p>
                                        <p class="truncate font-mono text-[10px] text-ink-400">{{ c.business?.uscc }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-ink-200">{{ c.business?.industry || '—' }}</p>
                                <p class="text-[11px] text-ink-400">{{ c.business?.region || '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <RiskBadge v-if="c.risk_level" :level="c.risk_level" :score="c.risk_score" />
                                <span v-else class="text-xs text-ink-400">未筛查</span>
                            </td>
                            <td class="px-4 py-3"><StatusBadge :status="c.status" size="sm" /></td>
                            <td class="px-4 py-3 text-xs text-ink-200">{{ c.assignee?.name || '—' }}</td>
                            <td class="px-4 py-3 text-[11px] text-ink-400">{{ formatDateTime(c.updated_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('cases.show', c.id)" class="inline-flex items-center text-ink-400 hover:text-gold-300">
                                    详情 <ArrowRight class="ml-1 h-3.5 w-3.5" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState v-else :icon="FolderSearch" title="未找到匹配案件" description="尝试调整筛选条件或新建核验案件">
                <template #action>
                    <Link :href="route('cases.create')" class="btn-gold"><FilePlus2 class="h-4 w-4" /> 新建核验</Link>
                </template>
            </EmptyState>

            <!-- Pagination -->
            <div v-if="cases.last_page > 1" class="flex items-center justify-between border-t border-ink-700/60 px-4 py-3">
                <p class="text-xs text-ink-400">第 {{ cases.current_page }} / {{ cases.last_page }} 页 · 共 {{ cases.total }} 条</p>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in cases.links" :key="i">
                        <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded border px-2 text-xs transition"
                            :class="link.active ? 'border-gold-400/40 bg-gold-400/10 text-gold-200' : 'border-ink-600 text-ink-300 hover:bg-ink-800'"
                            v-html="link.label" />
                        <span v-else class="inline-flex h-8 min-w-8 items-center justify-center rounded border border-ink-700 px-2 text-xs text-ink-500" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
