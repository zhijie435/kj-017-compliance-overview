<script setup>
defineProps({
    title: String,
    eyebrow: { type: String, default: 'KYB · COMPLIANCE' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="mb-6">
        <p class="eyebrow">{{ eyebrow }}</p>
        <div class="mt-1.5 flex items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold tracking-tight text-ink-100">{{ title }}</h1>
                <p v-if="description" class="mt-1 text-sm text-ink-300">{{ description }}</p>
            </div>
            <div class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
        <div class="ledger-divider mt-4"></div>
    </div>
</template>
