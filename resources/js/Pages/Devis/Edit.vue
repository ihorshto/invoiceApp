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
    devis:    Object,
    clients:  Array,
    products: Array,
})

const form = useForm({
    client_id:            props.devis.client_id,
    issue_date:           props.devis.issue_date?.slice(0, 10),
    valid_until:          props.devis.valid_until?.slice(0, 10) ?? '',
    estimated_start_date: props.devis.estimated_start_date?.slice(0, 10) ?? '',
    currency:             props.devis.currency,
    chantier_address:     props.devis.chantier_address ?? '',
    payment_conditions:   props.devis.payment_conditions ?? '',
    notes:                props.devis.notes ?? '',
    footer:               props.devis.footer ?? '',
    items: props.devis.items.map(i => ({
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
    form.put(route('devis.update', props.devis.id))
}

const readonly = ['converted', 'rejected'].includes(props.devis.status)
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('devis.show', devis.id)" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">Modifier {{ devis.number }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4">
            <div v-if="readonly" class="mb-4 rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                Ce devis est {{ devis.status === 'converted' ? 'converti' : 'refusé' }} et ne peut plus être modifié.
            </div>

            <form @submit.prevent="submit" class="space-y-6" :class="{ 'opacity-60 pointer-events-none': readonly }">

                <!-- Client + Dates -->
                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="client_id" value="Client *" />
                        <select id="client_id" v-model="form.client_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.client_id" />
                    </div>
                    <div>
                        <InputLabel for="currency" value="Devise" />
                        <select id="currency" v-model="form.currency"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="EUR">EUR €</option>
                            <option value="USD">USD $</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="issue_date" value="Date d'émission *" />
                        <TextInput id="issue_date" v-model="form.issue_date" type="date" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="valid_until" value="Validité *" />
                        <TextInput id="valid_until" v-model="form.valid_until" type="date" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.valid_until" />
                    </div>
                    <div class="col-span-2">
                        <InputLabel for="estimated_start_date" value="Date de début estimée" />
                        <TextInput id="estimated_start_date" v-model="form.estimated_start_date" type="date" class="mt-1 block w-full" />
                    </div>
                </div>

                <!-- Chantier address -->
                <div class="bg-white rounded-xl shadow p-6">
                    <InputLabel for="chantier_address" value="Adresse du chantier" />
                    <textarea id="chantier_address" v-model="form.chantier_address" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                </div>

                <!-- Work items -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Prestations / Fournitures</h3>
                    <div class="grid grid-cols-12 gap-2 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <div class="col-span-3">Produit</div>
                        <div class="col-span-3">Désignation</div>
                        <div class="col-span-2">Prix unit. HT</div>
                        <div class="col-span-1">Qté</div>
                        <div class="col-span-1">Unité</div>
                        <div class="col-span-1 text-right">Total HT</div>
                        <div class="col-span-1"></div>
                    </div>
                    <div v-for="(item, index) in items" :key="index" class="grid grid-cols-12 gap-2 mb-3 items-start">
                        <div class="col-span-3">
                            <select v-model="item.product_id" @change="onProductChange(index)"
                                class="block w-full border-gray-300 rounded-md text-sm shadow-sm">
                                <option :value="null">Aucun produit</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <TextInput v-model="item.description" type="text" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-2">
                            <TextInput v-model="item.unit_price" type="number" step="0.01" min="0" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.quantity" type="number" step="0.001" min="0.001" class="block w-full text-sm" required />
                        </div>
                        <div class="col-span-1">
                            <TextInput v-model="item.unit" type="text" class="block w-full text-sm" />
                        </div>
                        <div class="col-span-1 pt-2 text-right text-sm text-gray-700 font-medium">{{ fmt(lineHT(item)) }}</div>
                        <div class="col-span-1 pt-1.5">
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
                        </div>
                    </div>
                    <button type="button" @click="addItem" class="text-blue-600 text-sm hover:underline mt-2">+ Ajouter une ligne</button>

                    <div class="mt-6 border-t pt-4 space-y-1 text-sm text-right">
                        <div class="text-gray-600">Total HT : <span class="font-medium text-gray-900">{{ fmt(subtotal) }}</span></div>
                        <div class="text-xs text-gray-400 italic">TVA non applicable, article 293 B du CGI</div>
                    </div>
                </div>

                <!-- Payment conditions + Notes -->
                <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <InputLabel for="payment_conditions" value="Conditions de paiement" />
                        <textarea id="payment_conditions" v-model="form.payment_conditions" rows="2"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                    </div>
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

                <div v-if="!readonly" class="flex justify-end">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? '...' : 'Enregistrer' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
