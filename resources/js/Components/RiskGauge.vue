<script setup>
import { computed } from 'vue';
import { riskColor, riskLevelFromScore, RISK_META } from '@/utils/kyb';

const props = defineProps({
    score: { type: Number, default: 0 },
    max: { type: Number, default: 100 },
    size: { type: Number, default: 180 },
});

const R = 80;
const CIRC = Math.PI * R;
const normalized = computed(() => Math.max(0, Math.min(props.max, props.score || 0)));
const pct = computed(() => normalized.value / props.max);
const offset = computed(() => CIRC * (1 - pct.value));
const color = computed(() => riskColor(normalized.value));
const level = computed(() => RISK_META[riskLevelFromScore(normalized.value)]);
const width = computed(() => props.size);
const height = computed(() => props.size * 0.62);
</script>

<template>
    <div class="flex flex-col items-center">
        <svg :viewBox="`0 0 200 124`" :width="width" :height="height" class="overflow-visible">
            <defs>
                <linearGradient :id="`gauge-${score}`" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#34D399" />
                    <stop offset="50%" stop-color="#FBBF24" />
                    <stop offset="100%" stop-color="#DC2626" />
                </linearGradient>
            </defs>
            <path d="M 20 110 A 80 80 0 0 1 180 110" fill="none" stroke="#1A2435" stroke-width="14" stroke-linecap="round" />
            <path d="M 20 110 A 80 80 0 0 1 180 110" fill="none" :stroke="`url(#gauge-${score})`" stroke-width="14" stroke-linecap="round" :stroke-dasharray="CIRC" :stroke-dashoffset="offset" style="transition: stroke-dashoffset 0.9s cubic-bezier(0.22,1,0.36,1)" />
            <text x="100" y="92" text-anchor="middle" class="font-display" :fill="color" style="font-size: 38px; font-weight: 600;">{{ Math.round(normalized) }}</text>
            <text x="100" y="112" text-anchor="middle" fill="#7C8AA0" style="font-size: 9px; letter-spacing: 0.2em;">RISK SCORE</text>
        </svg>
        <div class="mt-1 flex items-center gap-1.5 text-sm">
            <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: color }"></span>
            <span class="font-medium" :style="{ color }">{{ level.label }}</span>
        </div>
    </div>
</template>
