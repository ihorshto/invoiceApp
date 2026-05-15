<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useI18n } from '@/Composables/useI18n'

const { t, formatMoney } = useI18n()

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
    if (confirm(t('products.confirm_delete'))) {
        router.delete(route('products.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-mint-900">{{ t('products.title') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ products.total }} {{ t('products.count') }}</p>
            </div>
            <Link
                :href="route('products.create')"
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
                    :placeholder="t('products.search')"
                    class="w-full rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-mint-400 focus:ring-mint-400"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('products.fields.name') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('products.fields.price') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('products.fields.vat_rate') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('products.fields.unit') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('products.fields.active') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-mint-50 transition-colors" :class="{ 'opacity-50': !product.is_active }">
                            <td class="px-4 py-3 font-medium text-mint-900">{{ product.name }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-mint-900">{{ formatMoney(product.unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ product.vat_rate }}%</td>
                            <td class="px-4 py-3 text-slate-500">{{ product.unit }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border"
                                    :class="product.is_active
                                        ? 'bg-green-100 text-green-700 border-green-200'
                                        : 'bg-slate-100 text-slate-500 border-slate-200'"
                                >
                                    {{ product.is_active ? t('products.status.active') : t('products.status.archived') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <Link :href="route('products.edit', product.id)" class="text-mint-600 hover:text-mint-800 text-xs font-medium hover:underline">{{ t('ui.action.edit') }}</Link>
                                <button class="text-red-400 hover:text-red-600 text-xs font-medium hover:underline" @click="deleteProduct(product.id)">{{ t('ui.action.delete') }}</button>
                            </td>
                        </tr>
                        <tr v-if="!products.data?.length">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">{{ t('products.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.last_page > 1" class="mt-4 flex justify-center gap-1.5">
                <Link
                    v-for="link in products.links"
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
