<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useI18n } from '@/Composables/useI18n'

const { t, formatMoney, formatDate } = useI18n()

const props = defineProps({
    invoices: { type: Object, required: true },
    filters:  Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

const doSearch = useDebounceFn(() => {
    router.get(route('invoices.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true })
}, 300)

watch([search, status], doSearch)

const filterOptions = [
    { value: '',        label: 'invoices.filter.all_statuses' },
    { value: 'paid',    label: 'invoices.statuses.paid' },
    { value: 'sent',    label: 'invoices.statuses.sent' },
    { value: 'overdue', label: 'invoices.statuses.overdue' },
    { value: 'draft',   label: 'invoices.statuses.draft' },
]

const deleteInvoice = (id) => {
    if (confirm(t('invoices.confirm_delete'))) {
        router.delete(route('invoices.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-mint-900">{{ t('invoices.title') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ invoices.total }} {{ t('invoices.count') }}</p>
            </div>
            <Link
                :href="route('invoices.create')"
                class="inline-flex items-center rounded-md bg-mint-500 px-4 py-2 text-sm font-semibold text-white hover:bg-mint-600 transition-colors"
            >
                + {{ t('ui.action.new') }}
            </Link>
        </template>

        <div class="p-6">
            <!-- Toolbar: search flex-1 + filter buttons -->
            <div class="mb-4 flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="t('invoices.search')"
                    class="flex-1 min-w-0 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-mint-400 focus:ring-mint-400"
                />
                <button
                    v-for="opt in filterOptions"
                    :key="opt.value"
                    @click="status = opt.value"
                    class="flex-shrink-0 rounded-md border px-3 py-2 text-xs font-medium transition-colors"
                    :class="status === opt.value
                        ? 'border-mint-500 bg-mint-50 text-mint-700 font-semibold'
                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-mint-300 hover:bg-mint-50'"
                >
                    {{ t(opt.label) }}
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.client') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.due_date') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.total') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('invoices.fields.status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-mint-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-mint-900">{{ inv.number }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ inv.client?.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(inv.issue_date) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(inv.due_date) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-mint-900">{{ formatMoney(inv.total) }}</td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="inv.status">{{ t('invoices.statuses.' + inv.status) }}</StatusBadge>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <Link :href="route('invoices.show', inv.id)" class="text-mint-600 hover:text-mint-800 text-xs font-medium hover:underline">{{ t('invoices.action.view') }}</Link>
                                <Link v-if="['draft','sent'].includes(inv.status)" :href="route('invoices.edit', inv.id)" class="text-slate-500 hover:text-slate-700 text-xs font-medium hover:underline">{{ t('ui.action.edit') }}</Link>
                                <button v-if="inv.status === 'draft'" class="text-red-400 hover:text-red-600 text-xs font-medium hover:underline" @click="deleteInvoice(inv.id)">{{ t('ui.action.delete') }}</button>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data?.length">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">{{ t('invoices.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.last_page > 1" class="mt-4 flex justify-center gap-1.5 text-sm">
                <Link
                    v-for="link in invoices.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-md border px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="link.active
                        ? 'bg-mint-500 text-white border-mint-500'
                        : 'border-slate-200 text-slate-600 hover:bg-mint-50 hover:border-mint-300'"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
