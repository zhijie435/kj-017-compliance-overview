<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import RiskBadge from '@/Components/RiskBadge.vue';
import TrendChart from '@/Components/TrendChart.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatDate, formatDateTime, RISK_META } from '@/utils/kyb';
import {
    FolderOpen, Clock, ShieldAlert, BadgeCheck,
    TrendingUp, ArrowRight, FileText, UserPlus, ShieldCheck,
    AlertTriangle, Gavel, Search, Eye, Activity,
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    trend: Array,
    todos: Array,
    timeline: Array,
});

const riskComposition = computed(() => {
    const sum = { low: 0, medium: 0, high: 0, prohibited: 0 };
    props.trend.forEach(m => {
        sum.low += m.low || 0;
        sum.medium += m.medium || 0;
        sum.high += m.high || 0;
        sum.prohibited += m.prohibited || 0;
    });
    const total = sum.low + sum.medium + sum.high + sum.prohibited || 1;
    return ['low', 'medium', 'high', 'prohibited'].map(k => ({
        key: k,
        label: RISK_META[k].label,
        color: RISK_META[k].color,
        count: sum[k],
        pct: Math.round((sum[k] / total) * 100),
    }));
});

const trendData = computed(() => (props.trend || []).map(m => ({ month: m.month, count: m.total })));

const ACTION_META = {
    created: { label: '创建案件', icon: FileText, tone: 'text-gold-300' },
    submitted: { label: '提交核验', icon: ArrowRight, tone: 'text-ink-200' },
    screened: { label: '风险筛查', icon: Search, tone: 'text-amber2' },
    reviewed: { label: '审核决议', icon: Gavel, tone: 'text-emerald2' },
    approved: { label: '审核通过', icon: ShieldCheck, tone: 'text-emerald2' },
    rejected: { label: '案件驳回', icon: AlertTriangle, tone: 'text-crimson' },
    viewed: { label: '查阅案件', icon: Eye, tone: 'text-ink-300' },
    user_login: { label: '登录系统', icon: UserPlus, tone: 'text-ink-300' },
};
function actionMeta(action) {
    return ACTION_META[action] || { label: action || '操作', icon: Activity, tone: 'text-ink-300' };
}
</script>

<template>
    <Head title="工作台" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="KYB · 合规工作台" title="工作台" description="企业身份核验与合规治理总览">
            <template #actions>
                <Link :href="route('cases.create')" class="btn-gold">
                    <FileText class="h-4 w-4" /> 新建核验
                </Link>
            </template>
        </PageHeader>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <StatCard label="案件总量" :value="stats.total" hint="全部核验记录" :icon="FolderOpen" />
            <StatCard label="进行中" :value="stats.pending" hint="待筛查 / 审核中" :icon="Clock" />
            <StatCard label="高风险" :value="stats.highRisk" hint="高 + 禁止准入" :icon="ShieldAlert" />
            <StatCard label="本月通过" :value="stats.approvedMonth" hint="当月审批通过" :icon="BadgeCheck" />
        </div>

        <!-- Trend + Risk composition -->
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <SectionCard class="lg:col-span-2" title="案件趋势" eyebrow="近 6 个月" :icon="TrendingUp">
                <div class="flex items-baseline justify-between">
                    <div>
                        <p class="font-display text-3xl font-semibold text-ink-100">{{ stats.total }}</p>
                        <p class="text-xs text-ink-300">累计核验案件</p>
                    </div>
                </div>
                <div class="mt-4">
                    <TrendChart :data="trendData" :height="150" />
                </div>
            </SectionCard>

            <SectionCard title="风险构成" eyebrow="6 个月汇总" :icon="ShieldAlert">
                <div class="space-y-3">
                    <div v-for="r in riskComposition" :key="r.key">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-ink-200">
                                <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: r.color }"></span>
                                {{ r.label }}
                            </span>
                            <span class="font-mono text-ink-300">{{ r.count }} · {{ r.pct }}%</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-700/50">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: r.pct + '%', backgroundColor: r.color }"></div>
                        </div>
                    </div>
                </div>
            </SectionCard>
        </div>

        <!-- Todos + Timeline -->
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <SectionCard class="lg:col-span-2" title="待办案件" eyebrow="需要您处理" :icon="Clock">
                <template #action>
                    <Link :href="route('cases.index')" class="flex items-center gap-1 text-xs text-gold-300 hover:text-gold-200">
                        全部 <ArrowRight class="h-3 w-3" />
                    </Link>
                </template>
                <div v-if="todos.length" class="-mx-2 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-ink-400">
                                <th class="px-2 py-2 font-medium">案件</th>
                                <th class="px-2 py-2 font-medium">企业</th>
                                <th class="px-2 py-2 font-medium">风险</th>
                                <th class="px-2 py-2 font-medium">状态</th>
                                <th class="px-2 py-2 font-medium">更新</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-700/50">
                            <tr v-for="c in todos" :key="c.id" class="table-row-hover">
                                <td class="px-2 py-2.5">
                                    <Link :href="route('cases.show', c.id)" class="font-mono text-xs text-gold-300 hover:underline">{{ c.case_no }}</Link>
                                </td>
                                <td class="px-2 py-2.5">
                                    <p class="text-sm text-ink-100">{{ c.business?.name }}</p>
                                    <p class="text-[11px] text-ink-400">{{ c.business?.industry }}</p>
                                </td>
                                <td class="px-2 py-2.5">
                                    <RiskBadge v-if="c.risk_level" :level="c.risk_level" :score="c.risk_score" />
                                    <span v-else class="text-xs text-ink-400">未筛查</span>
                                </td>
                                <td class="px-2 py-2.5"><StatusBadge :status="c.status" size="sm" /></td>
                                <td class="px-2 py-2.5 text-[11px] text-ink-400">{{ formatDateTime(c.updated_at) }}</td>
                                <td class="px-2 py-2.5 text-right">
                                    <Link :href="route('cases.show', c.id)" class="text-ink-400 hover:text-gold-300">
                                        <ArrowRight class="h-4 w-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState v-else title="暂无待办" description="所有案件均已处理完毕" />
            </SectionCard>

            <SectionCard title="审计动态" eyebrow="最近活动" :icon="Activity">
                <ol v-if="timeline.length" class="relative space-y-4 before:absolute before:left-[7px] before:top-1 before:h-[calc(100%-1rem)] before:w-px before:bg-ink-700">
                    <li v-for="log in timeline" :key="log.id" class="relative flex gap-3 pl-6">
                        <span class="absolute left-0 top-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full border border-ink-600 bg-ink-850">
                            <component :is="actionMeta(log.action).icon" class="h-2 w-2" :class="actionMeta(log.action).tone" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-ink-100">
                                <span class="font-medium">{{ log.user?.name || '系统' }}</span>
                                <span class="text-ink-400"> · {{ actionMeta(log.action).label }}</span>
                            </p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-400">{{ log.entity_type }} #{{ log.entity_id }} · {{ formatDateTime(log.created_at) }}</p>
                        </div>
                    </li>
                </ol>
                <EmptyState v-else title="暂无动态" />
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
