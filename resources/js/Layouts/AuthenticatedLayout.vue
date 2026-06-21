<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import {
    LayoutDashboard, FolderSearch, FilePlus2, ShieldAlert,
    ScrollText, LogOut, Menu, X, ChevronRight, Building2, ChevronDown,
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileNav = ref(false);
const userMenu = ref(false);

const ROLE_META = {
    admin: { label: '系统管理员', tone: 'text-gold-300 border-gold-400/40 bg-gold-400/10' },
    manager: { label: '复核经理', tone: 'text-emerald2 border-emerald2/40 bg-emerald2/10' },
    analyst: { label: '合规专员', tone: 'text-ink-200 border-ink-500/50 bg-ink-700/40' },
};
const roleMeta = computed(() => ROLE_META[user.value?.role] || ROLE_META.analyst);

const navItems = [
    { name: '工作台', route: 'dashboard', icon: LayoutDashboard },
    { name: '核验案件', route: 'cases.index', icon: FolderSearch },
    { name: '新建核验', route: 'cases.create', icon: FilePlus2 },
    { name: '风险评估', route: 'risk.index', icon: ShieldAlert },
    { name: '审计报告', route: 'audit.index', icon: ScrollText },
];

function isActive(routeName) {
    if (routeName === 'cases.index') {
        return route().current('cases.index') || route().current('cases.show');
    }
    return route().current(routeName);
}

const breadcrumb = computed(() => page.component?.split('/').pop() || '');
</script>

<template>
    <div class="min-h-screen bg-ink-950">
        <FlashMessage />

        <!-- Sidebar (desktop) -->
        <aside class="fixed inset-y-0 left-0 z-50 hidden w-64 flex-col border-r border-ink-700/60 bg-ink-900/95 backdrop-blur-xl lg:flex">
            <div class="flex h-full flex-col">
                <div class="relative flex h-16 items-center gap-3 border-b border-ink-700/60 px-5">
                    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400/40 to-transparent"></div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-gold-sheen text-ink-950 shadow-glow-gold">
                        <Building2 class="h-5 w-5" :stroke-width="2.2" />
                    </div>
                    <div class="leading-tight">
                        <p class="font-display text-lg font-semibold tracking-tight text-ink-100">KYB<span class="text-gold-400">.</span></p>
                        <p class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">合规账册</p>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <p class="eyebrow px-3 pb-2">导航</p>
                    <Link
                        v-for="item in navItems" :key="item.route"
                        :href="route(item.route)"
                        class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition"
                        :class="isActive(item.route)
                            ? 'border border-gold-400/30 bg-gold-400/10 text-gold-100'
                            : 'border border-transparent text-ink-200 hover:bg-ink-800/60 hover:text-ink-100'"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" :class="isActive(item.route) ? 'text-gold-300' : 'text-ink-400 group-hover:text-ink-200'" />
                        <span class="flex-1">{{ item.name }}</span>
                        <ChevronRight v-if="isActive(item.route)" class="h-3.5 w-3.5 text-gold-400" />
                    </Link>
                </nav>

                <div class="border-t border-ink-700/60 p-3">
                    <div class="relative rounded-md border border-ink-700 bg-ink-850/80 p-3">
                        <button @click="userMenu = !userMenu" class="flex w-full items-center gap-3 text-left">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-700 font-display text-sm font-semibold text-gold-300">
                                {{ user?.name?.charAt(0) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink-100">{{ user?.name }}</p>
                                <p class="truncate text-[11px] text-ink-400">{{ user?.email }}</p>
                            </div>
                            <ChevronDown class="h-4 w-4 text-ink-400" :class="{ 'rotate-180': userMenu }" />
                        </button>
                        <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                            <div v-if="userMenu" class="absolute bottom-full left-0 right-0 mb-2 rounded-md border border-ink-700 bg-ink-850 p-2 shadow-ledger">
                                <div class="mb-2 flex items-center justify-between px-2 py-1.5">
                                    <span class="chip" :class="roleMeta.tone">{{ roleMeta.label }}</span>
                                </div>
                                <Link :href="route('logout')" method="post" as="button"
                                    class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-[13px] text-crimson transition hover:bg-ink-800">
                                    <LogOut class="h-4 w-4" /> 退出登录
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile sidebar -->
        <Transition enter-active-class="transition duration-300" enter-from-class="-translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-300" leave-from-class="translate-x-0" leave-to-class="-translate-x-full">
            <aside v-if="mobileNav" class="fixed inset-y-0 left-0 z-50 w-64 border-r border-ink-700/60 bg-ink-900 lg:hidden">
                <div class="flex h-16 items-center gap-3 border-b border-ink-700/60 px-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-gold-sheen text-ink-950">
                        <Building2 class="h-5 w-5" />
                    </div>
                    <p class="font-display text-lg font-semibold text-ink-100">KYB<span class="text-gold-400">.</span></p>
                    <button @click="mobileNav = false" class="ml-auto text-ink-400"><X class="h-5 w-5" /></button>
                </div>
                <nav class="space-y-1 px-3 py-4">
                    <Link v-for="item in navItems" :key="item.route" :href="route(item.route)" @click="mobileNav = false"
                        class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition"
                        :class="isActive(item.route) ? 'border border-gold-400/30 bg-gold-400/10 text-gold-100' : 'text-ink-200 hover:bg-ink-800/60'">
                        <component :is="item.icon" class="h-4 w-4" :class="isActive(item.route) ? 'text-gold-300' : 'text-ink-400'" />
                        {{ item.name }}
                    </Link>
                    <div class="mt-4 border-t border-ink-700/60 pt-3">
                        <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2 rounded-md px-3 py-2.5 text-sm text-crimson">
                            <LogOut class="h-4 w-4" /> 退出登录
                        </Link>
                    </div>
                </nav>
            </aside>
        </Transition>
        <div v-if="mobileNav" @click="mobileNav = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Main -->
        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 border-b border-ink-700/60 bg-ink-950/80 backdrop-blur-xl">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button @click="mobileNav = true" class="rounded-md border border-ink-600 p-2 text-ink-200 lg:hidden">
                            <Menu class="h-5 w-5" />
                        </button>
                        <div class="hidden items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-ink-300 sm:flex">
                            <span>合规中心</span>
                            <ChevronRight class="h-3 w-3 text-ink-500" />
                            <span class="text-gold-300">{{ breadcrumb }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="hidden items-center gap-2 rounded-full border border-emerald2/30 bg-emerald2/5 px-3 py-1 sm:flex">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald2 opacity-60"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald2"></span>
                            </span>
                            <span class="text-[11px] font-medium text-emerald2">系统正常</span>
                        </div>
                        <p class="font-mono text-[11px] text-ink-300">{{ new Date().toLocaleDateString('zh-CN') }}</p>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
