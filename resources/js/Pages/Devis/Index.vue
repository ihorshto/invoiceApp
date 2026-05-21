<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useI18n } from '@/Composables/useI18n'

const { formatMoney, formatDate } = useI18n()

const props = defineProps({
    devis:   { type: Object, required: true },
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

const doSearch = useDebounceFn(() => {
    router.get(route('devis.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true })
}, 300)

watch([search, status], doSearch)

const filterOptions = [
    { value: '',          label: 'Tous' },
    { value: 'draft',     label: 'Brouillon' },
    { value: 'sent',      label: 'Envoyé' },
    { value: 'accepted',  label: 'Accepté' },
    { value: 'rejected',  label: 'Refusé' },
    { value: 'converted', label: 'Converti' },
]

const statusCls = {
    draft:     'bg-slate-100 text-slate-600 border border-slate-200',
    sent:      'bg-yellow-50 text-yellow-800 border border-yellow-200',
    accepted:  'bg-green-100 text-green-700 border border-green-200',
    rejected:  'bg-red-100 text-red-700 border border-red-200',
    converted: 'bg-violet-100 text-violet-700 border border-violet-200',
}

const deleteDevis = (id) => {
    if (confirm('Supprimer ce devis ?')) {
        router.delete(route('devis.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-mint-900">Devis</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ devis.total }} devis</p>
            </div>
            <Link
                :href="route('devis.create')"
                class="inline-flex items-center rounded-md bg-mint-500 px-4 py-2 text-sm font-semibold text-white hover:bg-mint-600 transition-colors"
            >
                + Nouveau devis
            </Link>
        </template>

        <div class="p-6">
            <!-- Toolbar -->
            <div class="mb-4 flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher par numéro ou client…"
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
                    {{ opt.label }}
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Numéro</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Émission</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Validité</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total HT</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="d in devis.data" :key="d.id" class="hover:bg-mint-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-mint-900">{{ d.number }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ d.client?.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(d.issue_date) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ d.valid_until ? formatDate(d.valid_until) : '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-mint-900">{{ formatMoney(d.total) }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold"
                                    :class="statusCls[d.status] ?? statusCls.draft"
                                >
                                    {{ filterOptions.find(o => o.value === d.status)?.label ?? d.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <Link :href="route('devis.show', d.id)" class="text-mint-600 hover:text-mint-800 text-xs font-medium hover:underline">Voir</Link>
                                <Link
                                    v-if="['draft', 'sent'].includes(d.status)"
                                    :href="route('devis.edit', d.id)"
                                    class="text-slate-500 hover:text-slate-700 text-xs font-medium hover:underline"
                                >Modifier</Link>
                                <button
                                    v-if="d.status === 'draft'"
                                    class="text-red-400 hover:text-red-600 text-xs font-medium hover:underline"
                                    @click="deleteDevis(d.id)"
                                >Supprimer</button>
                            </td>
                        </tr>
                        <tr v-if="!devis.data?.length">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Aucun devis trouvé.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="devis.last_page > 1" class="mt-4 flex justify-center gap-1.5 text-sm">
                <Link
                    v-for="link in devis.links"
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
