<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
    invoices: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

const doSearch = useDebounceFn(() => {
    router.get(route('invoices.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true })
}, 300)

watch([search, status], doSearch)

const statusLabel = {
    draft:     { fr: 'Brouillon', uk: 'Чернетка',  cls: 'bg-gray-100 text-gray-700' },
    sent:      { fr: 'Envoyée',   uk: 'Відправлено', cls: 'bg-blue-100 text-blue-700' },
    paid:      { fr: 'Payée',     uk: 'Оплачено',   cls: 'bg-green-100 text-green-700' },
    overdue:   { fr: 'En retard', uk: 'Прострочено', cls: 'bg-red-100 text-red-700' },
    cancelled: { fr: 'Annulée',   uk: 'Скасовано',  cls: 'bg-yellow-100 text-yellow-700' },
}

const formatMoney = (v) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(v)
const formatDate  = (d) => new Date(d).toLocaleDateString('fr-FR')

const deleteInvoice = (id) => {
    if (confirm('Supprimer cette facture / Видалити рахунок?')) {
        router.delete(route('invoices.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Factures / Рахунки</h2>
                <Link :href="route('invoices.create')" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800">
                    + Nouvelle / Нова
                </Link>
            </div>
        </template>

        <div class="py-8 max-w-6xl mx-auto px-4">
            <!-- Filters -->
            <div class="mb-4 flex gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="N° ou client…"
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm w-64"
                />
                <select v-model="status" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Tous statuts / Усі статуси</option>
                    <option value="draft">Brouillon / Чернетка</option>
                    <option value="sent">Envoyée / Відправлено</option>
                    <option value="paid">Payée / Оплачено</option>
                    <option value="overdue">En retard / Прострочено</option>
                    <option value="cancelled">Annulée / Скасовано</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">N°</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Échéance / Термін</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Total TTC</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-medium text-gray-900">{{ inv.number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ inv.client?.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ formatDate(inv.issue_date) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ formatDate(inv.due_date) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatMoney(inv.total) }}</td>
                            <td class="px-4 py-3">
                                <span :class="statusLabel[inv.status]?.cls" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ statusLabel[inv.status]?.fr }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('invoices.show', inv.id)" class="text-blue-600 hover:underline">Voir / Огл.</Link>
                                <Link v-if="['draft','sent'].includes(inv.status)" :href="route('invoices.edit', inv.id)" class="text-indigo-600 hover:underline">Modifier / Ред.</Link>
                                <button v-if="inv.status === 'draft'" class="text-red-500 hover:underline" @click="deleteInvoice(inv.id)">Supprimer</button>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data?.length">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune facture / Рахунків немає</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.last_page > 1" class="mt-4 flex justify-center gap-2 text-sm">
                <Link
                    v-for="link in invoices.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1 rounded border', link.active ? 'bg-blue-700 text-white border-blue-700' : 'border-gray-300 hover:bg-gray-50']"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
