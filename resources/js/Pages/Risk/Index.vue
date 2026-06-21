<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatCard from '@/Components/StatCard.vue';
import RiskBadge from '@/Components/RiskBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { RISK_META } from '@/utils/kyb';
import { ShieldAlert, AlertTriangle, Ban, Newspaper, Building2, ArrowRight, Activity } from 'lucide-vue-next';

const props = defineProps({
    cases: Object,
    distribution: Array,
    flags: Object,
});

const distTotal = computed(() => props.distribution.reduce((s, d) => s + d.count, 0) || 1);
const distRows = computed(() => props.distribution.map(d => ({
    ...d,
    label: RISK_META[d.level].label,
    color: RISK_META[d.level].color,
    pct: Math.round((d.count / distTotal.value) * 100),
})));

const flagCards = computed(() => [
    { key: 'pep', label: 'PEP 命中', value: props.flags.pep, icon: AlertTriangle, tone: 'text-amber2' },
    { key: 'sanctions', label: '制裁名单', value: props.flags.sanctions, icon: Ban, tone: 'text-crimson' },
    { key: 'adverse', label: '负面舆情', value: props.flags.adverse, icon: Newspaper, tone: 'text-amber2' },
    { key: 'shell', label: '空壳特征', value: props.flags.shell, icon: Building2, tone: 'text-ink-200' },
]);
</script>

<template>
    <Head title="风险评估" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="KYB · 风险洞察" title="风险评估" description="风险评分排序与命中因子全景" />

        <!-- Flag stats -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div v-for="f in flagCards" :key="f.key" class="ledger-card relative overflow-hidden p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="eyebrow">{{ f.label }}</p>
                        <p class="stat-num mt-2" :class="f.tone">{{ f.value }}</p>
                    </div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-md border border-ink-600 bg-ink-800" :class="f.tone">
                        <component :is="f.icon" class="h-4 w-4" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-ink-400">累计命中案件</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <!-- Distribution -->
            <SectionCard title="风险等级分布" eyebrow="DISTRIBUTION" :icon="ShieldAlert" class="lg:col-span-1">
                <div class="space-y-3">
                    <div v-for="d in distRows" :key="d.level">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-ink-200">
                                <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: d.color }"></span>{{ d.label }}
                            </span>
                            <span class="font-mono text-ink-300">{{ d.count }} · {{ d.pct }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-ink-700/50">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: d.pct + '%', backgroundColor: d.color }"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 border-t border-ink-700/60 pt-3 text-xs text-ink-400">
                    已评估案件合计 <span class="font-mono text-gold-300">{{ distTotal }}</span> 件
                </div>
            </SectionCard>

            <!-- Ranked table -->
            <SectionCard title="高风险案件排序" eyebrow="BY RISK SCORE" :icon="Activity" class="lg:col-span-2">
                <template #action>
                    <Link :href="route('cases.index')" class="flex items-center gap-1 text-xs text-gold-300 hover:text-gold-200">
                        全部 <ArrowRight class="h-3 w-3" />
                    </Link>
                </template>
                <div v-if="cases.data.length" class="-mx-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-ink-400">
                                <th class="px-2 py-2 font-medium">案件 / 企业</th>
                                <th class="px-2 py-2 font-medium">评分</th>
                                <th class="px-2 py-2 font-medium">等级</th>
                                <th class="px-2 py-2 font-medium">负责人</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-700/50">
                            <tr v-for="c in cases.data" :key="c.id" class="table-row-hover">
                                <td class="px-2 py-2.5">
                                    <Link :href="route('cases.show', c.id)" class="font-mono text-xs text-gold-300 hover:underline">{{ c.case_no }}</Link>
                                    <p class="truncate text-sm text-ink-100">{{ c.business?.name }}</p>
                                </td>
                                <td class="px-2 py-2.5">
                                    <span class="font-display text-lg font-semibold" :style="{ color: RISK_META[c.risk_level]?.color }">{{ c.risk_score }}</span>
                                </td>
                                <td class="px-2 py-2.5"><RiskBadge :level="c.risk_level" :show-score="false" /></td>
                                <td class="px-2 py-2.5 text-xs text-ink-200">{{ c.assignee?.name || '—' }}</td>
                                <td class="px-2 py-2.5 text-right">
                                    <Link :href="route('cases.show', c.id)" class="text-ink-400 hover:text-gold-300"><ArrowRight class="h-4 w-4" /></Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState v-else title="暂无已评估案件" />
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
