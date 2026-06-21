<script setup>
import { computed } from 'vue';
import { riskChip } from '@/utils/kyb';

const props = defineProps({
    level: String,
    score: { type: [Number, String], default: null },
    showScore: { type: Boolean, default: true },
});

const meta = computed(() => riskChip(props.level));
const scoreNum = computed(() => {
    const n = Number(props.score);
    return Number.isNaN(n) ? null : n;
});
</script>

<template>
    <span class="chip" :class="meta.chipClass">
        <span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: meta.color }"></span>
        {{ meta.label }}
        <span v-if="showScore && scoreNum !== null" class="font-mono opacity-70">· {{ scoreNum }}</span>
    </span>
</template>
