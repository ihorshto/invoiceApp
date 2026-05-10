<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useInvoiceItems } from '@/Composables/useInvoiceItems'

const props = defineProps({
    invoice: Object,
    clients: Array,
    products: Array,
})

const form = useForm({
    client_id:  props.invoice.client_id,
    issue_date: props.invoice.issue_date?.slice(0, 10),
    due_date:   props.invoice.due_date?.slice(0, 10),
    currency:   props.invoice.currency,
    notes:      props.invoice.notes ?? '',
    footer:     props.invoice.footer ?? '',
    items:      props.invoice.items.map(i => ({
        product_id:  i.product_id,
        description: i.description,
        unit_price:  i.unit_price,
        unit:        i.unit,
        quantity:    i.quantity,
        vat_rate:    i.vat_rate,
    })),
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
    form.put(route('invoices.update', props.invoice.id))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('invoices.show', invoice.id)" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">Modifier facture {{ invoice.number }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Header info -->
                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="client_id" value="Client *" />
                        <select id="client_id" v-model="form.client_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.client_id" />
                    </div>
                    <div>
                        <InputLabel for="currency" value="Devise / Валюта" />
                        <select id="currency" v-model="form.currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="EUR">EUR €</option>
                            <option value="UAH">UAH ₴</option>
                            <option value="USD">USD $</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="issue_date" value="Date d'émission *" />
                        <TextInput id="issue_date" v-model="form.issue_date" type="date" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="due_date" value="Date d'échéance *" />
                        <TextInput id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full" required />
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Lignes / Рядки</h3>
                    <div v-for="(item, index) in items" :key="index" class="grid grid-cols-12 gap-2 mb-3 items-start">
                        <div class="col-span-3">
                            <select v-model="item.product_id" @change="onProductChange(index)"
                                class="block w-full border-gray-300 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option :value="null">— Produit —</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <TextInput v-model="item.description" type="text" placeholder="Description *" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-2">
                            <TextInput v-model="item.unit_price" type="number" step="0.01" min="0" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.quantity" type="number" step="0.001" min="0.001" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.vat_rate" type="number" step="0.01" min="0" max="100" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1 pt-2 text-right text-sm text-gray-700 font-medium">
                            {{ fmt(lineHT(item)) }}
                        </div>
                        <div class="col-span-1 pt-1.5">
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
                        </div>
                    </div>
                    <button type="button" @click="addItem" class="text-blue-600 text-sm hover:underline mt-2">+ Ajouter ligne</button>

                    <div class="mt-6 border-t pt-4 space-y-1 text-sm text-right">
                        <div class="text-gray-600">Sous-total HT: <span class="font-medium text-gray-900">{{ fmt(subtotal) }}</span></div>
                        <div class="text-gray-600">TVA: <span class="font-medium text-gray-900">{{ fmt(vatTotal) }}</span></div>
                        <div class="text-base font-bold text-gray-900">Total TTC: {{ fmt(total) }}</div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <textarea id="notes" v-model="form.notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                    </div>
                    <div>
                        <InputLabel for="footer" value="Pied de page" />
                        <textarea id="footer" v-model="form.footer" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? '...' : 'Enregistrer / Зберегти' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
