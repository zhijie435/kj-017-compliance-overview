<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Mail, Lock, ArrowRight, ShieldCheck } from 'lucide-vue-next';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const demoAccounts = [
    { role: '合规专员', email: 'analyst@kyb.test' },
    { role: '复核经理', email: 'manager@kyb.test' },
    { role: '系统管理员', email: 'admin@kyb.test' },
];
</script>

<template>
    <Head title="登录 · KYB 合规账册" />
    <div class="flex min-h-screen bg-ink-950">
        <!-- Brand panel -->
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden border-r border-ink-700/60 bg-ink-900 p-12 lg:flex">
            <div class="absolute inset-0 bg-ledger-grid bg-grid-sm opacity-40"></div>
            <div class="absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-gold-400/10 blur-3xl"></div>
            <div class="relative">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gold-sheen text-ink-950 shadow-glow-gold">
                        <Building2 class="h-6 w-6" :stroke-width="2.2" />
                    </div>
                    <div>
                        <p class="font-display text-xl font-semibold tracking-tight text-ink-100">KYB<span class="text-gold-400">.</span></p>
                        <p class="font-mono text-[10px] uppercase tracking-[0.25em] text-ink-300">合规账册</p>
                    </div>
                </div>
            </div>
            <div class="relative">
                <p class="eyebrow text-gold-300">Know Your Business</p>
                <h1 class="mt-3 font-display text-4xl font-semibold leading-tight text-ink-100">
                    企业身份核验<br />
                    <span class="bg-gold-sheen bg-clip-text text-transparent">与合规治理</span> 平台
                </h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-ink-300">
                    覆盖企业主体核验、受益人识别、风险筛查与多级审核流转，为机构提供可审计、可追溯的合规决策依据。
                </p>
                <div class="mt-8 flex items-center gap-6">
                    <div v-for="f in [{k:'主体核验',v:'UBO'},{k:'风险筛查',v:'PEP·制裁'},{k:'审核流转',v:'双签'},{k:'审计留痕',v:'日志'}]" :key="f.k" class="border-l border-gold-400/30 pl-3">
                        <p class="font-display text-sm font-semibold text-gold-200">{{ f.v }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-ink-400">{{ f.k }}</p>
                    </div>
                </div>
            </div>
            <div class="relative flex items-center gap-2 text-[11px] text-ink-400">
                <ShieldCheck class="h-4 w-4 text-emerald2" />
                <span>端到端审计 · 符合监管要求</span>
            </div>
        </div>

        <!-- Form panel -->
        <div class="flex w-full flex-col justify-center px-6 py-12 sm:px-12 lg:w-1/2">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-8 lg:hidden">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gold-sheen text-ink-950">
                            <Building2 class="h-6 w-6" />
                        </div>
                        <p class="font-display text-xl font-semibold text-ink-100">KYB<span class="text-gold-400">.</span></p>
                    </div>
                </div>

                <p class="eyebrow">登录</p>
                <h2 class="mt-1.5 font-display text-2xl font-semibold text-ink-100">进入合规工作台</h2>
                <p class="mt-1.5 text-sm text-ink-300">请使用机构分配的账号登录</p>

                <div v-if="status" class="mt-5 rounded-md border border-emerald2/30 bg-emerald2/5 px-3 py-2 text-xs text-emerald2">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <label class="field-label" for="email">邮箱</label>
                        <div class="relative">
                            <Mail class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input id="email" v-model="form.email" type="email" autocomplete="username" autofocus required
                                class="field-input pl-9" placeholder="you@firm.com" />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-crimson">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="field-label" for="password">密码</label>
                        <div class="relative">
                            <Lock class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
                                class="field-input pl-9" placeholder="••••••••" />
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-crimson">{{ form.errors.password }}</p>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" v-model="form.remember" class="h-4 w-4 rounded border-ink-500 bg-ink-800 text-gold-400 focus:ring-gold-400/30" />
                        <span class="text-xs text-ink-300">保持登录状态</span>
                    </label>

                    <button type="submit" :disabled="form.processing"
                        class="btn-gold w-full !py-3 disabled:opacity-50">
                        <span>{{ form.processing ? '登录中…' : '登录' }}</span>
                        <ArrowRight v-if="!form.processing" class="h-4 w-4" />
                    </button>
                </form>

                <!-- Demo accounts -->
                <div class="mt-8">
                    <div class="ledger-divider mb-4"></div>
                    <p class="eyebrow mb-2.5">演示账号 · 密码统一 <span class="font-mono text-gold-300">password</span></p>
                    <div class="space-y-1.5">
                        <button v-for="acc in demoAccounts" :key="acc.email" type="button"
                            @click="form.email = acc.email; form.password = 'password'"
                            class="flex w-full items-center justify-between rounded-md border border-ink-700 bg-ink-850/60 px-3 py-2 text-left transition hover:border-gold-400/40 hover:bg-ink-800/60">
                            <span class="text-xs text-ink-200">{{ acc.role }}</span>
                            <span class="font-mono text-[11px] text-gold-300">{{ acc.email }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
