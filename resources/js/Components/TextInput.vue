<script setup>
import { onMounted, ref } from 'vue'

const model = defineModel({ type: String, required: true })
const input = ref(null)

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus()
    }
})

defineExpose({ focus: () => input.value.focus() })
</script>

<template>
    <input
        class="rounded-md border-slate-200 bg-slate-50 shadow-none focus:border-mint-400 focus:ring-mint-400"
        v-model="model"
        ref="input"
    />
</template>
