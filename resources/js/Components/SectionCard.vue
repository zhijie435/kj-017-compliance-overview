<script setup>
defineProps({
    title: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
    action: { type: Boolean, default: false },
});
</script>

<template>
    <section class="ledger-card overflow-hidden">
        <header v-if="title || $slots.header" class="flex items-center justify-between gap-4 border-b border-ink-700/60 px-5 py-3.5">
            <div class="flex items-center gap-2.5">
                <component v-if="icon" :is="icon" class="h-4 w-4 text-gold-300" />
                <div>
                    <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
                    <h3 class="font-display text-base font-semibold text-ink-100">{{ title }}</h3>
                </div>
            </div>
            <slot name="action" />
        </header>
        <div class="p-5">
            <slot />
        </div>
    </section>
</template>
