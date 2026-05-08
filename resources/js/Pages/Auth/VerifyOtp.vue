<script setup>
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const form = useForm({
    code: '',
})

const submit = () => {
    form.post(route('login.verify'))
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-blue-800 mb-2 text-center">InvoiceApp</h1>
            <p class="text-center text-gray-500 mb-6 text-sm">
                Code envoyé par email / Код надіслано на email
            </p>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="code" value="Code OTP (6 chiffres)" />
                    <TextInput
                        id="code"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        class="mt-1 block w-full text-center text-2xl tracking-widest font-mono"
                        autofocus
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>

                <PrimaryButton class="w-full justify-center" :disabled="form.processing">
                    {{ form.processing ? '...' : 'Vérifier / Підтвердити' }}
                </PrimaryButton>

                <p class="text-center text-sm text-gray-500">
                    <a :href="route('login')" class="text-blue-600 hover:underline">
                        ← Retour / Назад
                    </a>
                </p>
            </form>
        </div>
    </div>
</template>
