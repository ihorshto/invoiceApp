<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useI18n } from '@/Composables/useI18n'

const { t, formatMoney, formatDate } = useI18n()

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

const statusCls = {
    draft:     'bg-gray-100 text-gray-700',
    sent:      'bg-blue-100 text-blue-700',
    paid:      'bg-green-100 text-green-700',
    overdue:   'bg-red-100 text-red-700',
    cancelled: 'bg-yellow-100 text-yellow-700',
}

const deleteInvoice = (id) => {
    if (confirm(t('invoices.confirm_delete'))) {
        router.delete(route('invoices.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">{{ t('invoices.title') }}</h2>
                <Link :href="route('invoices.create')" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800">
                    + {{ t('ui.action.new') }}
                </Link>
            </div>
        </template>

        <div class="py-8 max-w-6xl mx-auto px-4">
            <div class="mb-4 flex gap-3">
                <input v-model="search" type="text" :placeholder="t('invoices.search')"
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm w-64" />
                <select v-model="status" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">{{ t('invoices.filter.all_statuses') }}</option>
                    <option value="draft">{{ t('invoices.statuses.draft') }}</option>
                    <option value="sent">{{ t('invoices.statuses.sent') }}</option>
                    <option value="paid">{{ t('invoices.statuses.paid') }}</option>
                    <option value="overdue">{{ t('invoices.statuses.overdue') }}</option>
                    <option value="cancelled">{{ t('invoices.statuses.cancelled') }}</option>
                </select>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.number') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.client') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.date') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.due_date') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">{{ t('invoices.fields.total') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">{{ t('invoices.fields.status') }}</th>
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
                                <span :class="statusCls[inv.status]" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ t('invoices.statuses.' + inv.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('invoices.show', inv.id)" class="text-blue-600 hover:underline">{{ t('invoices.action.view') }}</Link>
                                <Link v-if="['draft','sent'].includes(inv.status)" :href="route('invoices.edit', inv.id)" class="text-indigo-600 hover:underline">{{ t('ui.action.edit') }}</Link>
                                <button v-if="inv.status === 'draft'" class="text-red-500 hover:underline" @click="deleteInvoice(inv.id)">{{ t('ui.action.delete') }}</button>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data?.length">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">{{ t('invoices.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="invoices.last_page > 1" class="mt-4 flex justify-center gap-2 text-sm">
                <Link v-for="link in invoices.links" :key="link.label" :href="link.url ?? '#'"
                    :class="['px-3 py-1 rounded border', link.active ? 'bg-blue-700 text-white border-blue-700' : 'border-gray-300 hover:bg-gray-50']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
