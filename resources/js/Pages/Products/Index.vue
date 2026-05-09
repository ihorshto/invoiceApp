<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
    products: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

const doSearch = useDebounceFn(() => {
    router.get(route('products.index'), { search: search.value }, { preserveState: true, replace: true })
}, 300)

watch(search, doSearch)

const deleteProduct = (id) => {
    if (confirm('Supprimer ce produit / Видалити товар?')) {
        router.delete(route('products.destroy', id))
    }
}

const formatPrice = (price) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price)
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Produits / Товари</h2>
                <Link :href="route('products.create')" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800">
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
                    placeholder="Rechercher par nom…"
                    class="w-full max-w-sm border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Nom / Назва</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Prix HT / Ціна</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">TVA / ПДВ</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Unité / Одиниця</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Statut / Статус</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50" :class="{ 'opacity-50': !product.is_active }">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ product.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ formatPrice(product.unit_price) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ product.vat_rate }}%</td>
                            <td class="px-4 py-3 text-gray-600">{{ product.unit }}</td>
                            <td class="px-4 py-3">
                                <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ product.is_active ? 'Actif / Активний' : 'Archivé / Архів' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('products.edit', product.id)" class="text-blue-600 hover:underline">
                                    Modifier / Ред.
                                </Link>
                                <button class="text-red-500 hover:underline" @click="deleteProduct(product.id)">
                                    Supprimer / Вид.
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!products.data?.length">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Aucun produit / Товарів немає
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="mt-4 flex justify-center gap-2 text-sm">
                <Link
                    v-for="link in products.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1 rounded border', link.active ? 'bg-blue-700 text-white border-blue-700' : 'border-gray-300 hover:bg-gray-50']"
                    v-html="link.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
