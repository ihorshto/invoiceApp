<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { ref } from 'vue'
import { useI18n } from '@/Composables/useI18n'

const { t } = useI18n()

const props = defineProps({
    company: Object,
    logoUrl: String,
})

const form = useForm({
    name:         props.company?.name         ?? '',
    address:      props.company?.address      ?? '',
    postal_code:  props.company?.postal_code  ?? '',
    city:         props.company?.city         ?? '',
    country:      props.company?.country      ?? '',
    email:        props.company?.email        ?? '',
    phone:        props.company?.phone        ?? '',
    vat_number:   props.company?.vat_number   ?? '',
    iban:         props.company?.iban         ?? '',
    legal_footer: props.company?.legal_footer ?? '',
    logo:         null,
})

const logoPreview = ref(props.logoUrl)
const fileInput   = ref(null)

const onLogoChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.logo = file
    logoPreview.value = URL.createObjectURL(file)
}

const removeLogo = () => {
    if (props.company?.logo_path) {
        router.delete(route('settings.company.logo.delete'))
    }
    logoPreview.value = null
    form.logo = null
    if (fileInput.value) fileInput.value.value = ''
}

const submit = () => {
    form.post(route('settings.company'), { forceFormData: true })
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">{{ t('settings.title') }}</h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto px-4">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-8 space-y-6">

                <div>
                    <InputLabel value="Logo" />
                    <div class="mt-2 flex items-center gap-4">
                        <img v-if="logoPreview" :src="logoPreview" class="h-20 w-auto rounded border object-contain" alt="Logo" />
                        <div v-else class="h-20 w-32 bg-gray-100 rounded border flex items-center justify-center text-gray-400 text-sm">
                            {{ t('settings.logo.no_logo') }}
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="text-sm text-blue-600 hover:underline" @click="fileInput.click()">
                                {{ t('settings.logo.choose') }}
                            </button>
                            <button v-if="logoPreview" type="button" class="text-sm text-red-500 hover:underline" @click="removeLogo">
                                {{ t('settings.logo.remove') }}
                            </button>
                        </div>
                        <input ref="fileInput" type="file" accept="image/png,image/jpeg" class="hidden" @change="onLogoChange" />
                    </div>
                    <InputError class="mt-1" :message="form.errors.logo" />
                </div>

                <div>
                    <InputLabel for="name" :value="t('settings.fields.name') + ' *'" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="address" :value="t('settings.fields.address') + ' *'" />
                    <textarea id="address" v-model="form.address" rows="2" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    <InputError class="mt-1" :message="form.errors.address" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="postal_code" :value="t('settings.fields.postal_code') + ' *'" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.postal_code" />
                    </div>
                    <div>
                        <InputLabel for="city" :value="t('settings.fields.city') + ' *'" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.city" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="country" :value="t('settings.fields.country') + ' *'" />
                        <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.country" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email *" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="phone" :value="t('settings.fields.phone')" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel for="vat_number" :value="t('settings.fields.vat_number')" />
                        <TextInput id="vat_number" v-model="form.vat_number" type="text" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.vat_number" />
                    </div>
                </div>

                <div>
                    <InputLabel for="iban" :value="t('settings.fields.iban')" />
                    <TextInput id="iban" v-model="form.iban" type="text" class="mt-1 block w-full font-mono" />
                    <InputError class="mt-1" :message="form.errors.iban" />
                </div>

                <div>
                    <InputLabel for="legal_footer" :value="t('settings.fields.legal_footer')" />
                    <textarea id="legal_footer" v-model="form.legal_footer" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    <InputError class="mt-1" :message="form.errors.legal_footer" />
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? '...' : t('ui.action.save') }}
                    </PrimaryButton>
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600">{{ t('ui.common.saved') }}</span>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
