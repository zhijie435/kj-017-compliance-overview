<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
    height: { type: Number, default: 140 },
    color: { type: String, default: '#D4B062' },
});

const W = 320;
const H = computed(() => props.height);
const pad = 8;

const points = computed(() => {
    const max = Math.max(1, ...props.data.map(d => d.count));
    const n = props.data.length;
    if (n === 0) return [];
    return props.data.map((d, i) => {
        const x = n === 1 ? W / 2 : pad + (i / (n - 1)) * (W - pad * 2);
        const y = H.value - pad - (d.count / max) * (H.value - pad * 2 - 12);
        return { x, y, ...d };
    });
});

const linePath = computed(() => points.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' '));
const areaPath = computed(() => points.value.length ? `${linePath.value} L ${points.value[points.value.length-1].x.toFixed(1)} ${H.value - pad} L ${points.value[0].x.toFixed(1)} ${H.value - pad} Z` : '');
const gradientId = computed(() => `trend-${Math.random().toString(36).slice(2, 8)}`);
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" :height="H" class="w-full" preserveAspectRatio="none">
        <defs>
            <linearGradient :id="gradientId" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="color" stop-opacity="0.28" />
                <stop offset="100%" :stop-color="color" stop-opacity="0" />
            </linearGradient>
        </defs>
        <g class="grid" stroke="#1A2435" stroke-width="1" stroke-dasharray="2 4" opacity="0.7">
            <line :x1="pad" :x2="W-pad" :y1="H/2" :y2="H/2" />
        </g>
        <path v-if="areaPath" :d="areaPath" :fill="`url(#${gradientId})`" />
        <path v-if="linePath" :d="linePath" fill="none" :stroke="color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <g v-for="(p, i) in points" :key="i">
            <circle :cx="p.x" :cy="p.y" r="3" :fill="color" />
            <text :x="p.x" :y="H - 2" text-anchor="middle" fill="#51607A" style="font-size: 9px;">{{ p.month }}</text>
        </g>
    </svg>
</template>
