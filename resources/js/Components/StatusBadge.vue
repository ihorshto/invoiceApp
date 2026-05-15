<script setup>
defineProps({
    status: { type: String, required: true },
})

const cls = {
    paid:      'bg-green-100 text-green-700 border border-green-200',
    sent:      'bg-yellow-50 text-yellow-800 border border-yellow-200',
    overdue:   'bg-red-100 text-red-700 border border-red-200',
    draft:     'bg-slate-100 text-slate-600 border border-slate-200',
    cancelled: 'bg-slate-100 text-slate-500 border border-slate-200',
}
</script>

<template>
    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold" :class="cls[status] ?? cls.draft">
        <slot>{{ status }}</slot>
    </span>
</template>
