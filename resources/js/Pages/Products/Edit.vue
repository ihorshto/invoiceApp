<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useI18n } from '@/Composables/useI18n'

const { t } = useI18n()

const props = defineProps({ product: Object })

const form = useForm({
    name:        props.product.name,
    description: props.product.description ?? '',
    unit_price:  props.product.unit_price,
    unit:        props.product.unit,
    vat_rate:    props.product.vat_rate,
    is_active:   props.product.is_active,
})

const submit = () => form.put(route('products.update', props.product.id))
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('products.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ t('products.edit') }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-2xl mx-auto px-4">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-8 space-y-5">
                <div>
                    <InputLabel for="name" :value="t('products.fields.name') + ' *'" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="description" :value="t('products.fields.description')" />
                    <textarea id="description" v-model="form.description" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    <InputError class="mt-1" :message="form.errors.description" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="unit_price" :value="t('products.fields.price') + ' *'" />
                        <TextInput id="unit_price" v-model="form.unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.unit_price" />
                    </div>
                    <div>
                        <InputLabel for="unit" :value="t('products.fields.unit') + ' *'" />
                        <TextInput id="unit" v-model="form.unit" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.unit" />
                    </div>
                    <div>
                        <InputLabel for="vat_rate" :value="t('products.fields.vat_rate') + ' *'" />
                        <TextInput id="vat_rate" v-model="form.vat_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.vat_rate" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500" />
                    <InputLabel for="is_active" :value="t('products.fields.active')" class="mb-0" />
                </div>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? '...' : t('ui.action.save') }}
                </PrimaryButton>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
