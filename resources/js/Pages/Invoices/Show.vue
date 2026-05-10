<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ invoice: Object })

const fmt  = (v) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.invoice.currency ?? 'EUR' }).format(v)
const fmtd = (d) => new Date(d).toLocaleDateString('fr-FR')

const statusLabel = {
    draft:     { label: 'Brouillon / Чернетка',   cls: 'bg-gray-100 text-gray-700' },
    sent:      { label: 'Envoyée / Відправлено',   cls: 'bg-blue-100 text-blue-700' },
    paid:      { label: 'Payée / Оплачено',        cls: 'bg-green-100 text-green-700' },
    overdue:   { label: 'En retard / Прострочено', cls: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Annulée / Скасовано',     cls: 'bg-yellow-100 text-yellow-700' },
}

const markPaid = () => {
    if (confirm('Marquer comme payée / Позначити оплаченим?')) {
        router.post(route('invoices.mark-paid', props.invoice.id))
    }
}

const downloadPdf = () => {
    window.open(route('invoices.pdf', props.invoice.id), '_blank')
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('invoices.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                    <h2 class="text-xl font-semibold text-gray-800">Facture {{ invoice.number }}</h2>
                    <span :class="statusLabel[invoice.status]?.cls" class="px-2 py-0.5 rounded-full text-xs font-medium">
                        {{ statusLabel[invoice.status]?.label }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button @click="downloadPdf" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm">
                        ↓ PDF
                    </button>
                    <Link v-if="['draft','sent'].includes(invoice.status)" :href="route('invoices.edit', invoice.id)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm">
                        Modifier / Редагувати
                    </Link>
                    <button v-if="['sent','overdue'].includes(invoice.status)" @click="markPaid"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm">
                        Marquer payée / Оплачено
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
            <!-- Meta -->
            <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Client</p>
                    <p class="font-semibold text-gray-800">{{ invoice.client?.name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">N° Facture</p>
                    <p class="font-mono font-bold text-gray-900">{{ invoice.number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Date d'émission</p>
                    <p>{{ fmtd(invoice.issue_date) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">Date d'échéance</p>
                    <p>{{ fmtd(invoice.due_date) }}</p>
                </div>
            </div>

            <!-- Items table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Description</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Prix U.</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Qté</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">TVA</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="px-4 py-3 text-gray-800">{{ item.description }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ fmt(item.unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ item.quantity }} {{ item.unit }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ item.vat_rate }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(item.total_ht) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="px-6 py-4 bg-gray-50 text-sm space-y-1">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total HT</span><span class="font-medium text-gray-900">{{ fmt(invoice.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>TVA</span><span class="font-medium text-gray-900">{{ fmt(invoice.vat_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900 border-t pt-2 mt-2">
                        <span>Total TTC</span><span>{{ fmt(invoice.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="invoice.notes || invoice.footer" class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6 text-sm text-gray-600">
                <div v-if="invoice.notes">
                    <p class="text-xs text-gray-400 uppercase mb-1">Notes</p>
                    <p class="whitespace-pre-line">{{ invoice.notes }}</p>
                </div>
                <div v-if="invoice.footer">
                    <p class="text-xs text-gray-400 uppercase mb-1">Pied de page</p>
                    <p class="whitespace-pre-line">{{ invoice.footer }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
