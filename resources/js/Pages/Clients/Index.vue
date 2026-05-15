<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useI18n } from '@/Composables/useI18n'

const { t } = useI18n()

const props = defineProps({
    clients: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

const doSearch = useDebounceFn(() => {
    router.get(route('clients.index'), { search: search.value }, { preserveState: true, replace: true })
}, 300)

watch(search, doSearch)

const deleteClient = (id) => {
    if (confirm(t('clients.confirm_delete'))) {
        router.delete(route('clients.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-mint-900">{{ t('clients.title') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ clients.total }} {{ t('clients.count') }}</p>
            </div>
            <Link
                :href="route('clients.create')"
                class="inline-flex items-center rounded-md bg-mint-500 px-4 py-2 text-sm font-semibold text-white hover:bg-mint-600 transition-colors"
            >
                + {{ t('ui.action.new') }}
            </Link>
        </template>

        <div class="p-6">
            <div class="mb-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="t('clients.search')"
                    class="w-full rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-mint-400 focus:ring-mint-400"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('clients.fields.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('clients.fields.email') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('clients.fields.city') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('clients.fields.country') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="client in clients.data" :key="client.id" class="hover:bg-mint-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-mint-900">{{ client.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ client.email }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ client.city }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ client.country }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <Link :href="route('clients.edit', client.id)" class="text-mint-600 hover:text-mint-800 text-xs font-medium hover:underline">{{ t('ui.action.edit') }}</Link>
                                <button class="text-red-400 hover:text-red-600 text-xs font-medium hover:underline" @click="deleteClient(client.id)">{{ t('ui.action.delete') }}</button>
                            </td>
                        </tr>
                        <tr v-if="!clients.data?.length">
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">{{ t('clients.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="clients.last_page > 1" class="mt-4 flex justify-center gap-1.5 text-sm">
                <Link
                    v-for="link in clients.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-md border px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="link.active
                        ? 'bg-mint-500 text-white border-mint-500'
                        : 'border-slate-200 text-slate-600 hover:bg-mint-50'"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
