<script setup>
import { Inbox } from 'lucide-vue-next';
defineProps({
    title: { type: String, default: '暂无数据' },
    description: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full border border-ink-600 bg-ink-800/50 text-ink-400">
            <component :is="icon || Inbox" class="h-5 w-5" />
        </div>
        <p class="mt-3 text-sm font-medium text-ink-200">{{ title }}</p>
        <p v-if="description" class="mt-1 text-xs text-ink-400">{{ description }}</p>
        <div v-if="$slots.action" class="mt-4">
            <slot name="action" />
        </div>
    </div>
</template>
