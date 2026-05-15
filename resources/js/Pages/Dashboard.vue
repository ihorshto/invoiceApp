<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'

const { t, formatMoney } = useI18n()

const props = defineProps({
    stats: Object,
})

const paidPct    = computed(() => props.stats.total_count ? Math.round(props.stats.paid_count    / props.stats.total_count * 100) : 0)
const pendingPct = computed(() => props.stats.total_count ? Math.round(props.stats.pending_count / props.stats.total_count * 100) : 0)
const overduePct = computed(() => props.stats.total_count ? Math.round(props.stats.overdue_count / props.stats.total_count * 100) : 0)
</script>

<template>
    <Head :title="t('ui.dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-mint-900">{{ t('ui.dashboard.title') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('ui.dashboard.subtitle') }}</p>
            </div>
        </template>

        <div class="p-6">
            <!-- Stats grid -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-6">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-medium text-slate-500 mb-2 flex items-center gap-1.5">
                        <span class="inline-block h-2 w-2 rounded-full bg-mint-500" aria-hidden="true"></span>
                        {{ t('ui.dashboard.stats.total') }}
                    </p>
                    <p class="text-2xl font-bold text-mint-900">{{ formatMoney(stats.total_amount) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ stats.total_count }} {{ t('ui.dashboard.stats.invoices') }}</p>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-medium text-slate-500 mb-2 flex items-center gap-1.5">
                        <span class="inline-block h-2 w-2 rounded-full bg-green-500" aria-hidden="true"></span>
                        {{ t('ui.dashboard.stats.paid') }}
                    </p>
                    <p class="text-2xl font-bold text-green-700">{{ formatMoney(stats.paid_amount) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ stats.paid_count }} {{ t('ui.dashboard.stats.invoices') }} · {{ paidPct }}%</p>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-medium text-slate-500 mb-2 flex items-center gap-1.5">
                        <span class="inline-block h-2 w-2 rounded-full bg-yellow-400" aria-hidden="true"></span>
                        {{ t('ui.dashboard.stats.pending') }}
                    </p>
                    <p class="text-2xl font-bold text-yellow-700">{{ formatMoney(stats.pending_amount) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ stats.pending_count }} {{ t('ui.dashboard.stats.invoices') }} · {{ pendingPct }}%</p>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-medium text-slate-500 mb-2 flex items-center gap-1.5">
                        <span class="inline-block h-2 w-2 rounded-full bg-red-500" aria-hidden="true"></span>
                        {{ t('ui.dashboard.stats.overdue') }}
                    </p>
                    <p class="text-2xl font-bold text-red-600">{{ formatMoney(stats.overdue_amount) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ stats.overdue_count }} {{ t('ui.dashboard.stats.invoices') }} · {{ overduePct }}%</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
