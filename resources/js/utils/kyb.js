export const STATUS_META = {
    draft: { label: '草稿', tone: 'slate', dot: 'bg-ink-300' },
    screening: { label: '风险筛查中', tone: 'gold', dot: 'bg-gold-400 animate-pulse' },
    pending_review: { label: '待初审', tone: 'amber', dot: 'bg-amber2' },
    reviewing: { label: '复审中', tone: 'amber', dot: 'bg-amber2 animate-pulse' },
    approved: { label: '已通过', tone: 'emerald', dot: 'bg-emerald2' },
    rejected: { label: '已驳回', tone: 'crimson', dot: 'bg-crimson' },
};

export const RISK_META = {
    low: { label: '低风险', tone: 'emerald', color: '#34D399' },
    medium: { label: '中风险', tone: 'amber', color: '#FBBF24' },
    high: { label: '高风险', tone: 'crimson', color: '#F87171' },
    prohibited: { label: '禁止准入', tone: 'crimsonDeep', color: '#DC2626' },
};

export const REVIEW_LEVEL_META = {
    first: { label: '初审' },
    final: { label: '终审' },
};

export const REVIEW_DECISION_META = {
    approve: { label: '通过', tone: 'emerald' },
    reject: { label: '驳回', tone: 'crimson' },
    return: { label: '退回', tone: 'amber' },
};

const TONE_CLASSES = {
    slate: 'border-ink-500/50 bg-ink-700/40 text-ink-200',
    gold: 'border-gold-400/40 bg-gold-400/10 text-gold-200',
    amber: 'border-amber2/40 bg-amber2/10 text-amber2',
    emerald: 'border-emerald2/40 bg-emerald2/10 text-emerald2',
    crimson: 'border-crimson/40 bg-crimson/10 text-crimson',
    crimsonDeep: 'border-crimsonDeep/50 bg-crimsonDeep/15 text-crimson',
};

export function statusChip(status) {
    const m = STATUS_META[status] || { label: status, tone: 'slate', dot: 'bg-ink-300' };
    return { ...m, chipClass: TONE_CLASSES[m.tone] };
}

export function riskChip(level) {
    const m = RISK_META[level] || { label: level, tone: 'slate', color: '#7C8AA0' };
    return { ...m, chipClass: TONE_CLASSES[m.tone] || TONE_CLASSES.slate };
}

export function reviewDecisionChip(decision) {
    const m = REVIEW_DECISION_META[decision] || { label: decision, tone: 'slate' };
    return { ...m, chipClass: TONE_CLASSES[m.tone] || TONE_CLASSES.slate };
}

export function formatDate(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '—';
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

export function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '—';
    return `${formatDate(value)} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

export function formatCapital(value) {
    if (value === null || value === undefined || value === '') return '—';
    const num = Number(value);
    if (Number.isNaN(num)) return String(value);
    if (num >= 100000000) return `${(num / 100000000).toFixed(2)} 亿元`;
    if (num >= 10000) return `${(num / 10000).toFixed(2)} 万元`;
    return `${num.toLocaleString('zh-CN')} 元`;
}

export function riskColor(score) {
    if (score === null || score === undefined) return '#7C8AA0';
    if (score >= 70) return '#DC2626';
    if (score >= 45) return '#F87171';
    if (score >= 20) return '#FBBF24';
    return '#34D399';
}

export function riskLevelFromScore(score) {
    if (score >= 70) return 'prohibited';
    if (score >= 45) return 'high';
    if (score >= 20) return 'medium';
    return 'low';
}

export const ID_TYPE_LABELS = {
    id_card: '居民身份证',
    passport: '护照',
    other: '其他证件',
};

export function idTypeLabel(value) {
    return ID_TYPE_LABELS[value] || value || '—';
}

export const DOCUMENT_TYPE_LABELS = {
    business_license: '营业执照',
    tax_registration: '税务登记证',
    id_card: '法人身份证件',
    articles: '公司章程',
    other: '其他材料',
};

export const DOCUMENT_TYPES_REQUIRED = ['business_license', 'tax_registration'];

export function documentTypeLabel(value) {
    return DOCUMENT_TYPE_LABELS[value] || value || '—';
}

export function documentReviewStatusLabel(status) {
    return {
        approved: '审核通过',
        rejected: '审核驳回',
        pending: '待审核',
    }[status] || '待审核';
}
