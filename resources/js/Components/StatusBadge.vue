<script setup>
import { computed } from 'vue';
import { statusChip } from '@/utils/kyb';

const props = defineProps({
    status: String,
    size: { type: String, default: 'md' },
});

const meta = computed(() => statusChip(props.status));
const sizeClass = computed(() => props.size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-0.5 text-[11px]');
</script>

<template>
    <span class="chip" :class="[meta.chipClass, sizeClass]">
        <span class="h-1.5 w-1.5 rounded-full" :class="meta.dot"></span>
        {{ meta.label }}
    </span>
</template>
