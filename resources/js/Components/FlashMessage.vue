<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, X } from 'lucide-vue-next';

const page = usePage();
const visible = ref(false);
const message = computed(() => page.props.flash?.success);

watch(message, (val) => {
    if (val) {
        visible.value = true;
        setTimeout(() => (visible.value = false), 3500);
    }
});

function close() {
    visible.value = false;
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-3"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-3"
    >
        <div v-if="visible && message" class="fixed bottom-6 right-6 z-[60] flex items-center gap-3 rounded-lg border border-emerald2/40 bg-ink-850/95 px-4 py-3 shadow-ledger backdrop-blur-xl">
            <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald2" />
            <p class="text-sm text-ink-100">{{ message }}</p>
            <button @click="close" class="ml-2 text-ink-400 hover:text-ink-100">
                <X class="h-4 w-4" />
            </button>
        </div>
    </Transition>
</template>
