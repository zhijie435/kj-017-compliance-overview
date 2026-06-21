<script setup>
defineProps({
    label: String,
    value: [String, Number],
    hint: { type: String, default: '' },
    accent: { type: String, default: 'gold' },
    icon: { type: [Object, Function], default: null },
});
</script>

<template>
    <div class="ledger-card relative overflow-hidden p-5">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400/30 to-transparent"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">{{ label }}</p>
                <p class="stat-num mt-2">{{ value }}</p>
            </div>
            <div v-if="icon" class="flex h-9 w-9 items-center justify-center rounded-md border border-gold-400/20 bg-gold-400/5 text-gold-300">
                <component :is="icon" class="h-4 w-4" />
            </div>
        </div>
        <p v-if="hint" class="mt-2 text-xs text-ink-300">{{ hint }}</p>
        <slot name="footer" />
    </div>
</template>
