<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useI18n } from '@/Composables/useI18n'

const { t, formatMoney, formatDate } = useI18n()

const props = defineProps({
    invoice:      Object,
    sourceDevis:  { type: Object, default: null },
})

const statusCls = {
    draft:     'bg-gray-100 text-gray-700',
    sent:      'bg-blue-100 text-blue-700',
    paid:      'bg-green-100 text-green-700',
    overdue:   'bg-red-100 text-red-700',
    cancelled: 'bg-yellow-100 text-yellow-700',
}

const fmt     = (v) => formatMoney(v, props.invoice.currency ?? 'EUR')

const changeStatus = (newStatus) => {
    router.patch(route('invoices.status', props.invoice.id), { status: newStatus })
}

const downloadPdf = (locale) => {
    window.open(route('invoices.pdf', props.invoice.id) + '?locale=' + locale, '_blank')
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('invoices.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                    <h2 class="text-xl font-semibold text-gray-800">{{ t('invoices.title') }} {{ invoice.number }}</h2>
                    <select
                        :value="invoice.status"
                        @change="changeStatus($event.target.value)"
                        :class="statusCls[invoice.status]"
                        :aria-label="t('invoices.action.change_status')"
                        class="px-2 py-0.5 rounded-full text-xs font-medium border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="s in ['draft', 'sent', 'paid', 'overdue', 'cancelled']" :key="s" :value="s">
                            {{ t('invoices.statuses.' + s) }}
                        </option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button @click="downloadPdf('fr')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm">🇫🇷 PDF</button>
                    <button @click="downloadPdf('uk')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm">🇺🇦 PDF</button>
                    <Link v-if="['draft','sent'].includes(invoice.status)" :href="route('invoices.edit', invoice.id)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm">
                        {{ t('ui.action.edit') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
            <div v-if="sourceDevis" class="rounded-md bg-violet-50 border border-violet-200 px-4 py-3 text-sm text-violet-800">
                Créé depuis le devis
                <Link :href="route('devis.show', sourceDevis.id)" class="font-semibold underline">{{ sourceDevis.number }}</Link>
            </div>
            <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">{{ t('invoices.fields.client') }}</p>
                    <p class="font-semibold text-gray-800">{{ invoice.client?.name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">{{ t('invoices.fields.number') }}</p>
                    <p class="font-mono font-bold text-gray-900">{{ invoice.number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">{{ t('invoices.fields.issue_date') }}</p>
                    <p>{{ formatDate(invoice.issue_date) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase mb-1">{{ t('invoices.fields.due_date') }}</p>
                    <p>{{ formatDate(invoice.due_date) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.description') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">{{ t('invoices.fields.unit_price') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">{{ t('invoices.fields.quantity') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">{{ t('invoices.fields.vat') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">{{ t('invoices.fields.line_total') }}</th>
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

                <div class="px-6 py-4 bg-gray-50 text-sm space-y-1">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ t('invoices.fields.subtotal') }}</span>
                        <span class="font-medium text-gray-900">{{ fmt(invoice.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ t('invoices.fields.vat') }}</span>
                        <span class="font-medium text-gray-900">{{ fmt(invoice.vat_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900 border-t pt-2 mt-2">
                        <span>{{ t('invoices.fields.total') }}</span>
                        <span>{{ fmt(invoice.total) }}</span>
                    </div>
                </div>
            </div>

            <div v-if="invoice.notes || invoice.footer" class="bg-white rounded-xl shadow p-6 grid grid-cols-2 gap-6 text-sm text-gray-600">
                <div v-if="invoice.notes">
                    <p class="text-xs text-gray-400 uppercase mb-1">{{ t('invoices.fields.notes') }}</p>
                    <p class="whitespace-pre-line">{{ invoice.notes }}</p>
                </div>
                <div v-if="invoice.footer">
                    <p class="text-xs text-gray-400 uppercase mb-1">{{ t('invoices.fields.footer') }}</p>
                    <p class="whitespace-pre-line">{{ invoice.footer }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
