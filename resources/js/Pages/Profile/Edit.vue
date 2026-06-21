<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { User, Lock, Trash2 } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="个人设置" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <div class="text-xs uppercase tracking-[0.2em] text-gold-400/80 font-mono mb-2">
                KYB · 个人中心
            </div>
            <h1 class="text-3xl font-semibold text-ink-100 font-serif tracking-tight">
                个人设置
            </h1>
            <p class="mt-2 text-ink-400 text-sm">
                管理您的账户信息与安全设置
            </p>
        </div>

        <div class="space-y-6">
            <div class="ledger-card p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-gold-500/10 border border-gold-500/20 flex items-center justify-center">
                        <User class="w-5 h-5 text-gold-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-ink-100">
                            基本信息
                        </h3>
                        <p class="text-sm text-ink-400">
                            更新您的账户资料与联系邮箱
                        </p>
                    </div>
                </div>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="ledger-card p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-ink-700/50 border border-ink-600/50 flex items-center justify-center">
                        <Lock class="w-5 h-5 text-ink-300" />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-ink-100">
                            登录密码
                        </h3>
                        <p class="text-sm text-ink-400">
                            定期更换密码以确保账户安全
                        </p>
                    </div>
                </div>
                <UpdatePasswordForm />
            </div>

            <div class="ledger-card p-6 border-rose-500/20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
                        <Trash2 class="w-5 h-5 text-rose-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-ink-100">
                            注销账户
                        </h3>
                        <p class="text-sm text-ink-400">
                            永久删除您的账户及所有数据
                        </p>
                    </div>
                </div>
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
