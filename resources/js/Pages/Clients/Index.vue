<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

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
    if (confirm('Supprimer ce client / Видалити клієнта?')) {
        router.delete(route('clients.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Clients / Клієнти</h2>
                <Link :href="route('clients.create')" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800">
                    + Nouveau / Новий
                </Link>
            </div>
        </template>

        <div class="py-8 max-w-6xl mx-auto px-4">
            <!-- Search -->
            <div class="mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher par nom ou email…"
                    class="w-full max-w-sm border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Nom / Ім'я</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Ville / Місто</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Pays / Країна</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ client.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ client.email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ client.city }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ client.country }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('clients.edit', client.id)" class="text-blue-600 hover:underline">
                                    Modifier / Ред.
                                </Link>
                                <button class="text-red-500 hover:underline" @click="deleteClient(client.id)">
                                    Supprimer / Вид.
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!clients.data?.length">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                Aucun client / Клієнтів немає
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="clients.last_page > 1" class="mt-4 flex justify-center gap-2 text-sm">
                <Link
                    v-for="link in clients.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1 rounded border', link.active ? 'bg-blue-700 text-white border-blue-700' : 'border-gray-300 hover:bg-gray-50']"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
