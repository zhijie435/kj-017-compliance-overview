<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SectionCard from '@/Components/SectionCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatDateTime } from '@/utils/kyb';
import { ScrollText, Filter, ArrowRight, FileText, Send, Search, Gavel, Download, UserPlus } from 'lucide-vue-next';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const actionFilter = ref(props.filters.action || '');

let debounce;
watch(actionFilter, () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('audit.index'), { action: actionFilter.value || undefined }, {
            preserveState: true, preserveScroll: true, replace: true,
        });
    }, 250);
});

const ACTION_OPTIONS = [
    { value: 'case.create', label: '创建案件' },
    { value: 'case.submit', label: '提交核验' },
    { value: 'case.screen', label: '风险筛查' },
    { value: 'case.review', label: '审核操作' },
    { value: 'case.report', label: '生成报告' },
];

const ACTION_ICON = {
    'case.create': FileText,
    'case.submit': Send,
    'case.screen': Search,
    'case.review': Gavel,
    'case.report': Download,
};
</script>

<template>
    <Head title="审计报告" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="KYB · 审计留痕" title="审计报告" description="全量操作日志,可追溯不可篡改" />

        <div class="ledger-card mb-4 flex items-center gap-2 p-3">
            <Filter class="h-4 w-4 text-ink-400" />
            <select v-model="actionFilter" class="field-input !w-auto !py-2">
                <option value="">全部操作</option>
                <option v-for="a in ACTION_OPTIONS" :key="a.value" :value="a.value">{{ a.label }}</option>
            </select>
            <span class="ml-auto text-xs text-ink-400">共 {{ logs.total }} 条记录</span>
        </div>

        <div class="ledger-card overflow-hidden">
            <div v-if="logs.data.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-700/60 bg-ink-850/60 text-left text-[11px] uppercase tracking-wider text-ink-400">
                            <th class="px-4 py-3 font-medium">时间</th>
                            <th class="px-4 py-3 font-medium">操作人</th>
                            <th class="px-4 py-3 font-medium">操作</th>
                            <th class="px-4 py-3 font-medium">对象</th>
                            <th class="px-4 py-3 font-medium">IP</th>
                            <th class="px-4 py-3 font-medium">详情</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-700/50">
                        <tr v-for="log in logs.data" :key="log.id" class="table-row-hover">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink-300">{{ formatDateTime(log.created_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-ink-700 text-[11px] font-semibold text-gold-300">
                                        {{ log.user?.name?.charAt(0) || '系' }}
                                    </div>
                                    <span class="text-sm text-ink-100">{{ log.user?.name || '系统' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-1.5 text-xs text-ink-200">
                                    <component :is="ACTION_ICON[log.action] || ScrollText" class="h-3.5 w-3.5 text-gold-300" />
                                    {{ log.meta?.label || log.action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] text-ink-300">{{ log.entity_type }} #{{ log.entity_id }}</td>
                            <td class="px-4 py-3 font-mono text-[11px] text-ink-400">{{ log.ip || '—' }}</td>
                            <td class="px-4 py-3 text-[11px] text-ink-400">
                                <span v-if="log.meta?.case_no" class="font-mono text-gold-300">{{ log.meta.case_no }}</span>
                                <span v-else>—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState v-else :icon="ScrollText" title="暂无审计记录" />

            <div v-if="logs.last_page > 1" class="flex items-center justify-between border-t border-ink-700/60 px-4 py-3">
                <p class="text-xs text-ink-400">第 {{ logs.current_page }} / {{ logs.last_page }} 页</p>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in logs.links" :key="i">
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
