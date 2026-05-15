<script setup>
defineProps({ value: { type: String } })
</script>

<template>
    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
