<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useI18n } from '@/Composables/useI18n'

const { formatMoney, formatDate } = useI18n()

const props = defineProps({
    devis:            Object,
    canConvert:       Boolean,
    convertedInvoice: Object,
})

const statusOptions = [
    { value: 'draft',     label: 'Brouillon' },
    { value: 'sent',      label: 'Envoyé' },
    { value: 'accepted',  label: 'Accepté' },
    { value: 'rejected',  label: 'Refusé' },
    { value: 'converted', label: 'Converti' },
]

const statusCls = {
    draft:     'bg-gray-100 text-gray-700',
    sent:      'bg-blue-100 text-blue-700',
    accepted:  'bg-green-100 text-green-700',
    rejected:  'bg-red-100 text-red-700',
    converted: 'bg-violet-100 text-violet-700',
}

const fmt = (v) => formatMoney(v, props.devis.currency ?? 'EUR')

const changeStatus = (newStatus) => {
    router.patch(route('devis.status', props.devis.id), { status: newStatus })
}

const downloadPdf = () => {
    window.open(route('devis.pdf', props.devis.id), '_blank')
}

const convertForm = useForm({})
const convert = () => {
    if (confirm('Convertir ce devis en facture ?')) {
        convertForm.post(route('devis.convert', props.devis.id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('devis.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                    <h2 class="text-xl font-semibold text-gray-800">Devis {{ devis.number }}</h2>
                    <select
                        :value="devis.status"
                        @change="changeStatus($event.target.value)"
                        :class="statusCls[devis.status]"
                        aria-label="Changer le statut"
                        class="px-2 py-0.5 rounded-full text-xs font-medium border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button @click="downloadPdf" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm">
                        ↓ PDF
                    </button>
                    <Link
                        v-if="['draft', 'sent'].includes(devis.status)"
                        :href="route('devis.edit', devis.id)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm"
                    >
                        Modifier
                    </Link>
                    <button
                        v-if="canConvert"
                        @click="convert"
                        :disabled="convertForm.processing"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm disabled:opacity-50"
                    >
                        Convertir en facture
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

            <!-- Converted invoice link -->
            <div v-if="convertedInvoice" class="rounded-md bg-violet-50 border border-violet-200 px-4 py-3 text-sm text-violet-800">
                Facture créée :
                <Link :href="route('invoices.show', convertedInvoice.id)" class="font-semibold underline">
                    {{ convertedInvoice.number }}
                </Link>
            </div>

            <!-- Header card -->
            <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Client</p>
                    <p class="font-semibold text-gray-800">{{ devis.client?.name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">Numéro</p>
                    <p class="font-mono font-bold text-gray-900">{{ devis.number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Date d'émission</p>
                    <p>{{ formatDate(devis.issue_date) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">Validité</p>
                    <p>{{ devis.valid_until ? formatDate(devis.valid_until) : '—' }}</p>
                </div>
                <div v-if="devis.estimated_start_date">
                    <p class="text-xs text-gray-500 uppercase mb-1">Début prévu</p>
                    <p>{{ formatDate(devis.estimated_start_date) }}</p>
                </div>
                <div v-if="devis.accepted_at" class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">Accepté le</p>
                    <p class="text-green-700">{{ formatDate(devis.accepted_at) }}</p>
                </div>
            </div>

            <!-- Chantier address -->
            <div v-if="devis.chantier_address" class="bg-white rounded-xl shadow p-6">
                <p class="text-xs text-gray-500 uppercase mb-1">Adresse du chantier</p>
                <p class="whitespace-pre-line text-sm text-gray-700">{{ devis.chantier_address }}</p>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Désignation</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Prix U. HT</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Quantité</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Unité</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in devis.items" :key="item.id">
                            <td class="px-4 py-3 text-gray-800">{{ item.description }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ fmt(item.unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ item.unit }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(item.total_ht) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-6 py-4 bg-gray-50 text-sm space-y-1">
                    <div class="flex justify-between font-bold text-gray-900 text-base">
                        <span>Total HT</span>
                        <span>{{ fmt(devis.total) }}</span>
                    </div>
                    <p class="text-xs text-gray-400 italic">TVA non applicable, article 293 B du CGI</p>
                </div>
            </div>

            <!-- Payment conditions + Notes -->
            <div
                v-if="devis.payment_conditions || devis.notes || devis.footer"
                class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6 text-sm text-gray-600"
            >
                <div v-if="devis.payment_conditions" class="col-span-2">
                    <p class="text-xs text-gray-400 uppercase mb-1">Conditions de paiement</p>
                    <p class="whitespace-pre-line">{{ devis.payment_conditions }}</p>
                </div>
                <div v-if="devis.notes">
                    <p class="text-xs text-gray-400 uppercase mb-1">Notes</p>
                    <p class="whitespace-pre-line">{{ devis.notes }}</p>
                </div>
                <div v-if="devis.footer">
                    <p class="text-xs text-gray-400 uppercase mb-1">Pied de page</p>
                    <p class="whitespace-pre-line">{{ devis.footer }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
