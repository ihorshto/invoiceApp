<script setup>
defineProps({
    type: { type: String, default: 'button' },
})
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-mint-50 hover:border-mint-300 focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
