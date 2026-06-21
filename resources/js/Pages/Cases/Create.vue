<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SectionCard from '@/Components/SectionCard.vue';
import { Building2, Users, Paperclip, Plus, Trash2, Save, Send, ChevronLeft, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    industries: Array,
    regions: Array,
    countries: Array,
});

const COUNTRIES = [
    { value: 'CN', label: '中国', hasUscc: true, hasEin: false },
    { value: 'US', label: '美国', hasUscc: false, hasEin: true },
    { value: 'OTHER', label: '其他', hasUscc: false, hasEin: false },
];

const EIN_REGEX = /^\d{2}-\d{7}$/;

const form = useForm({
    business: {
        name: '',
        uscc: '',
        ein: '',
        legal_rep: '',
        registered_capital: '',
        establish_date: '',
        address: '',
        scope: '',
        industry: '',
        region: '',
        country: 'CN',
    },
    ubos: [{ name: '', id_type: 'id_card', id_number: '', ownership_percent: '', is_pep: false }],
    documents: [],
    action: 'draft',
});

const currentCountry = () => COUNTRIES.find(c => c.value === form.business.country) || COUNTRIES[0];
const showUscc = () => currentCountry().hasUscc;
const showEin = () => currentCountry().hasEin;

function validateEin(value) {
    if (!showEin()) return true;
    if (!value) return false;
    return EIN_REGEX.test(value);
}

function formatEinInput(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '-' + value.slice(2, 9);
    }
    e.target.value = value;
    form.business.ein = value;
}

const ownershipSum = computed(() => form.ubos.reduce((s, u) => s + (Number(u.ownership_percent) || 0), 0));
const ownershipWarn = computed(() => {
    if (!form.ubos.length) return false;
    return Math.abs(ownershipSum.value - 100) > 0.01;
});

const ID_TYPES = [
    { value: 'id_card', label: '居民身份证' },
    { value: 'passport', label: '护照' },
    { value: 'other', label: '其他证件' },
];

function addUbo() {
    form.ubos.push({ name: '', id_type: 'id_card', id_number: '', ownership_percent: '', is_pep: false });
}
function removeUbo(i) {
    if (form.ubos.length > 1) form.ubos.splice(i, 1);
}

function handleFiles(e) {
    form.documents = Array.from(e.target.files);
}

function submit(action) {
    form.action = action;
    form.post(route('cases.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="新建核验" />
    <AuthenticatedLayout>
        <PageHeader eyebrow="KYB · 新建案件" title="新建核验" description="录入企业主体信息与受益人,发起身份核验">
            <template #actions>
                <Link :href="route('cases.index')" class="btn-ghost">
                    <ChevronLeft class="h-4 w-4" /> 返回列表
                </Link>
            </template>
        </PageHeader>

        <form @submit.prevent="submit('draft')" class="space-y-5">
            <!-- Business -->
            <SectionCard title="企业主体信息" eyebrow="SECTION 01" :icon="Building2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="field-label">企业名称 <span class="text-crimson">*</span></label>
                        <input v-model="form.business.name" type="text" class="field-input" placeholder="如：深圳前海科技有限公司" />
                        <p v-if="form.errors['business.name']" class="mt-1 text-xs text-crimson">{{ form.errors['business.name'] }}</p>
                    </div>
                    <div>
                        <label class="field-label">注册国家 <span class="text-crimson">*</span></label>
                        <select v-model="form.business.country" class="field-input">
                            <option v-for="c in COUNTRIES" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                        <p v-if="form.errors['business.country']" class="mt-1 text-xs text-crimson">{{ form.errors['business.country'] }}</p>
                    </div>
                    <div v-if="showUscc()">
                        <label class="field-label">统一社会信用代码 <span class="text-crimson">*</span></label>
                        <input v-model="form.business.uscc" type="text" class="field-input font-mono" placeholder="18 位代码" />
                        <p v-if="form.errors['business.uscc']" class="mt-1 text-xs text-crimson">{{ form.errors['business.uscc'] }}</p>
                    </div>
                    <div v-if="showEin()">
                        <label class="field-label">EIN (雇主识别号) <span class="text-crimson">*</span></label>
                        <input
                            v-model="form.business.ein"
                            type="text"
                            class="field-input font-mono"
                            placeholder="XX-XXXXXXX"
                            maxlength="10"
                            @input="formatEinInput"
                        />
                        <p class="mt-1 text-[10px] text-ink-400">格式：2 位数字 - 7 位数字，如 12-3456789</p>
                        <p v-if="form.errors['business.ein']" class="mt-1 text-xs text-crimson">{{ form.errors['business.ein'] }}</p>
                    </div>
                    <div>
                        <label class="field-label">法定代表人 <span class="text-crimson">*</span></label>
                        <input v-model="form.business.legal_rep" type="text" class="field-input" placeholder="姓名" />
                        <p v-if="form.errors['business.legal_rep']" class="mt-1 text-xs text-crimson">{{ form.errors['business.legal_rep'] }}</p>
                    </div>
                    <div>
                        <label class="field-label">注册资本</label>
                        <input v-model="form.business.registered_capital" type="text" class="field-input" placeholder="如 5000 万元" />
                    </div>
                    <div>
                        <label class="field-label">成立日期</label>
                        <input v-model="form.business.establish_date" type="date" class="field-input" />
                    </div>
                    <div>
                        <label class="field-label">所属行业</label>
                        <select v-model="form.business.industry" class="field-input">
                            <option value="">请选择</option>
                            <option v-for="i in industries" :key="i" :value="i">{{ i }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">注册地区</label>
                        <select v-model="form.business.region" class="field-input">
                            <option value="">请选择</option>
                            <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">注册地址</label>
                        <input v-model="form.business.address" type="text" class="field-input" placeholder="详细地址" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">经营范围</label>
                        <textarea v-model="form.business.scope" rows="3" class="field-input resize-none" placeholder="简要描述主营业务"></textarea>
                    </div>
                </div>
            </SectionCard>

            <!-- UBOs -->
            <SectionCard title="受益人 (UBO)" eyebrow="SECTION 02" :icon="Users">
                <template #action>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-ink-300">持股合计</span>
                        <span class="font-mono font-semibold" :class="ownershipWarn ? 'text-crimson' : 'text-emerald2'">{{ ownershipSum.toFixed(2) }}%</span>
                        <span v-if="ownershipWarn" class="flex items-center gap-1 text-crimson"><AlertCircle class="h-3 w-3" /> 应为 100%</span>
                    </div>
                </template>
                <div class="space-y-3">
                    <div v-for="(ubo, i) in form.ubos" :key="i" class="rounded-md border border-ink-700 bg-ink-900/40 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-ink-300">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gold-400/15 font-mono text-[10px] text-gold-300">{{ i + 1 }}</span>
                                受益人 {{ i + 1 }}
                            </span>
                            <button v-if="form.ubos.length > 1" type="button" @click="removeUbo(i)" class="text-ink-400 hover:text-crimson">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="field-label">姓名 <span class="text-crimson">*</span></label>
                                <input v-model="ubo.name" type="text" class="field-input" placeholder="姓名" />
                            </div>
                            <div>
                                <label class="field-label">证件类型 <span class="text-crimson">*</span></label>
                                <select v-model="ubo.id_type" class="field-input">
                                    <option v-for="t in ID_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">证件号码 <span class="text-crimson">*</span></label>
                                <input v-model="ubo.id_number" type="text" class="field-input font-mono" placeholder="证件号" />
                            </div>
                            <div>
                                <label class="field-label">持股比例 % <span class="text-crimson">*</span></label>
                                <input v-model="ubo.ownership_percent" type="number" step="0.01" min="0" max="100" class="field-input" placeholder="如 60" />
                            </div>
                        </div>
                        <label class="mt-3 flex cursor-pointer items-center gap-2">
                            <input type="checkbox" v-model="ubo.is_pep" class="h-4 w-4 rounded border-ink-500 bg-ink-800 text-gold-400 focus:ring-gold-400/30" />
                            <span class="text-xs text-ink-200">该受益人为 <span class="text-amber2">政治公众人物 (PEP)</span></span>
                        </label>
                        <p v-if="form.errors[`ubos.${i}.name`]" class="mt-1 text-xs text-crimson">{{ form.errors[`ubos.${i}.name`] }}</p>
                    </div>
                </div>
                <button type="button" @click="addUbo" class="btn-ghost mt-4 w-full border-dashed">
                    <Plus class="h-4 w-4" /> 添加受益人
                </button>
                <p v-if="form.errors.ubos" class="mt-2 text-xs text-crimson">{{ form.errors.ubos }}</p>
            </SectionCard>

            <!-- Documents -->
            <SectionCard title="证照文件" eyebrow="SECTION 03 · 可选" :icon="Paperclip">
                <label class="flex cursor-pointer items-center justify-center rounded-md border border-dashed border-ink-600 bg-ink-900/30 px-6 py-8 text-center transition hover:border-gold-400/40">
                    <input type="file" multiple @change="handleFiles" class="hidden" accept="image/*,.pdf" />
                    <div>
                        <Paperclip class="mx-auto h-6 w-6 text-ink-400" />
                        <p class="mt-2 text-sm text-ink-200">点击上传营业执照、法人证件等材料</p>
                        <p class="mt-1 text-xs text-ink-400">支持图片 / PDF,可多选</p>
                        <p v-if="form.documents.length" class="mt-2 font-mono text-xs text-gold-300">已选择 {{ form.documents.length }} 个文件</p>
                    </div>
                </label>
            </SectionCard>

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="submit('draft')" :disabled="form.processing" class="btn-ghost">
                    <Save class="h-4 w-4" /> 保存草稿
                </button>
                <button type="button" @click="submit('submit')" :disabled="form.processing" class="btn-gold">
                    <Send class="h-4 w-4" /> 提交并筛查
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
