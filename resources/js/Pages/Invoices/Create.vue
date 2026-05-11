<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useInvoiceItems } from '@/Composables/useInvoiceItems'
import { useI18n } from '@/Composables/useI18n'

const { t } = useI18n()

const props = defineProps({
    clients: Array,
    products: Array,
})

const today = new Date().toISOString().slice(0, 10)
const due30 = new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10)

const form = useForm({
    client_id:  '',
    issue_date: today,
    due_date:   due30,
    currency:   'EUR',
    notes:      '',
    footer:     '',
    items:      [{ product_id: null, description: '', unit_price: 0, unit: 'unité', quantity: 1, vat_rate: 20 }],
})

const items = ref(form.items)
const { addItem, removeItem, fillFromProduct, lineHT, subtotal, vatTotal, total, fmt } = useInvoiceItems(items)

const onProductChange = (index) => {
    const pid = items.value[index].product_id
    const product = props.products.find(p => p.id == pid)
    fillFromProduct(index, product)
}

const submit = () => {
    form.items = items.value
    form.post(route('invoices.store'))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('invoices.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ t('invoices.new') }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="client_id" :value="t('invoices.fields.client') + ' *'" />
                        <select id="client_id" v-model="form.client_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ t('invoices.select_client') }}</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.client_id" />
                    </div>
                    <div>
                        <InputLabel for="currency" :value="t('invoices.fields.currency')" />
                        <select id="currency" v-model="form.currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="EUR">EUR €</option>
                            <option value="UAH">UAH ₴</option>
                            <option value="USD">USD $</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="issue_date" :value="t('invoices.fields.issue_date') + ' *'" />
                        <TextInput id="issue_date" v-model="form.issue_date" type="date" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.issue_date" />
                    </div>
                    <div>
                        <InputLabel for="due_date" :value="t('invoices.fields.due_date') + ' *'" />
                        <TextInput id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.due_date" />
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">{{ t('invoices.fields.items') }}</h3>
                    <div v-for="(item, index) in items" :key="index" class="grid grid-cols-12 gap-2 mb-3 items-start">
                        <div class="col-span-3">
                            <select v-model="item.product_id" @change="onProductChange(index)"
                                class="block w-full border-gray-300 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option :value="null">{{ t('invoices.select_product') }}</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <TextInput v-model="item.description" type="text" :placeholder="t('invoices.fields.description') + ' *'" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-2">
                            <TextInput v-model="item.unit_price" type="number" step="0.01" min="0" :placeholder="t('invoices.fields.unit_price')" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.quantity" type="number" step="0.001" min="0.001" :placeholder="t('invoices.fields.quantity')" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.vat_rate" type="number" step="0.01" min="0" max="100" :placeholder="t('invoices.fields.vat_rate') + '%'" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1 pt-2 text-right text-sm text-gray-700 font-medium">{{ fmt(lineHT(item)) }}</div>
                        <div class="col-span-1 pt-1.5">
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
                        </div>
                    </div>
                    <button type="button" @click="addItem" class="text-blue-600 text-sm hover:underline mt-2">{{ t('invoices.action.add_line') }}</button>
                    <InputError class="mt-1" :message="form.errors.items" />

                    <div class="mt-6 border-t pt-4 space-y-1 text-sm text-right">
                        <div class="text-gray-600">{{ t('invoices.fields.subtotal') }}: <span class="font-medium text-gray-900">{{ fmt(subtotal) }}</span></div>
                        <div class="text-gray-600">{{ t('invoices.fields.vat') }}: <span class="font-medium text-gray-900">{{ fmt(vatTotal) }}</span></div>
                        <div class="text-base font-bold text-gray-900">{{ t('invoices.fields.total') }}: {{ fmt(total) }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="notes" :value="t('invoices.fields.notes')" />
                        <textarea id="notes" v-model="form.notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                    </div>
                    <div>
                        <InputLabel for="footer" :value="t('invoices.fields.footer')" />
                        <textarea id="footer" v-model="form.footer" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? '...' : t('ui.action.create') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
