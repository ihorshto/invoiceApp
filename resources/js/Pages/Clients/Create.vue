<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useI18n } from '@/Composables/useI18n'

const { t } = useI18n()

const form = useForm({
    name: '', email: '', phone: '',
    address: '', postal_code: '', city: '', country: '',
    vat_number: '', notes: '',
})

const submit = () => form.post(route('clients.store'))
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('clients.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ t('clients.new') }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-2xl mx-auto px-4">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-8 space-y-5">
                <div>
                    <InputLabel for="name" :value="t('clients.fields.name') + ' *'" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="email" value="Email *" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="phone" :value="t('clients.fields.phone')" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                    </div>
                </div>
                <div>
                    <InputLabel for="address" :value="t('clients.fields.address') + ' *'" />
                    <textarea id="address" v-model="form.address" rows="2" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    <InputError class="mt-1" :message="form.errors.address" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="postal_code" :value="t('clients.fields.postal_code') + ' *'" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.postal_code" />
                    </div>
                    <div>
                        <InputLabel for="city" :value="t('clients.fields.city') + ' *'" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.city" />
                    </div>
                    <div>
                        <InputLabel for="country" :value="t('clients.fields.country') + ' *'" />
                        <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.country" />
                    </div>
                </div>
                <div>
                    <InputLabel for="vat_number" :value="t('clients.fields.vat_number')" />
                    <TextInput id="vat_number" v-model="form.vat_number" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="notes" :value="t('clients.fields.notes')" />
                    <textarea id="notes" v-model="form.notes" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? '...' : t('ui.action.create') }}
                </PrimaryButton>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
