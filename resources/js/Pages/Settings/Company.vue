<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { ref } from 'vue'

const props = defineProps({
    company: Object,
    logoUrl: String,
})

const form = useForm({
    name:         props.company?.name         ?? '',
    address:      props.company?.address      ?? '',
    postal_code:  props.company?.postal_code  ?? '',
    city:         props.company?.city         ?? '',
    country:      props.company?.country      ?? '',
    email:        props.company?.email        ?? '',
    phone:        props.company?.phone        ?? '',
    vat_number:   props.company?.vat_number   ?? '',
    iban:         props.company?.iban         ?? '',
    legal_footer: props.company?.legal_footer ?? '',
    logo:         null,
})

const logoPreview = ref(props.logoUrl)
const fileInput   = ref(null)

const onLogoChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.logo = file
    logoPreview.value = URL.createObjectURL(file)
}

const removeLogo = () => {
    if (props.company?.logo_path) {
        router.delete(route('settings.company.logo.delete'))
    }
    logoPreview.value = null
    form.logo = null
    if (fileInput.value) fileInput.value.value = ''
}

const submit = () => {
    form.post(route('settings.company'), {
        forceFormData: true,
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Paramètres société / Налаштування компанії
            </h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto px-4">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-8 space-y-6">

                <!-- Logo -->
                <div>
                    <InputLabel value="Logo" />
                    <div class="mt-2 flex items-center gap-4">
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            class="h-20 w-auto rounded border object-contain"
                            alt="Logo"
                        />
                        <div
                            v-else
                            class="h-20 w-32 bg-gray-100 rounded border flex items-center justify-center text-gray-400 text-sm"
                        >
                            Pas de logo
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="text-sm text-blue-600 hover:underline"
                                @click="fileInput.click()"
                            >
                                Choisir / Обрати
                            </button>
                            <button
                                v-if="logoPreview"
                                type="button"
                                class="text-sm text-red-500 hover:underline"
                                @click="removeLogo"
                            >
                                Supprimer / Видалити
                            </button>
                        </div>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/png,image/jpeg"
                            class="hidden"
                            @change="onLogoChange"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.logo" />
                </div>

                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Nom de la société / Назва компанії *" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <!-- Address -->
                <div>
                    <InputLabel for="address" value="Adresse / Адреса *" />
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="2"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                    <InputError class="mt-1" :message="form.errors.address" />
                </div>

                <!-- Postal + City -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="postal_code" value="Code postal / Поштовий індекс *" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.postal_code" />
                    </div>
                    <div>
                        <InputLabel for="city" value="Ville / Місто *" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.city" />
                    </div>
                </div>

                <!-- Country + Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="country" value="Pays / Країна *" />
                        <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.country" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email *" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                </div>

                <!-- Phone + VAT -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="phone" value="Téléphone / Телефон" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel for="vat_number" value="N° TVA / ЄДРПОУ" />
                        <TextInput id="vat_number" v-model="form.vat_number" type="text" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.vat_number" />
                    </div>
                </div>

                <!-- IBAN -->
                <div>
                    <InputLabel for="iban" value="IBAN" />
                    <TextInput id="iban" v-model="form.iban" type="text" class="mt-1 block w-full font-mono" />
                    <InputError class="mt-1" :message="form.errors.iban" />
                </div>

                <!-- Legal footer -->
                <div>
                    <InputLabel for="legal_footer" value="Mention légale pied de page / Правова примітка" />
                    <textarea
                        id="legal_footer"
                        v-model="form.legal_footer"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                    <InputError class="mt-1" :message="form.errors.legal_footer" />
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? '...' : 'Enregistrer / Зберегти' }}
                    </PrimaryButton>
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600">
                        ✓ Sauvegardé
                    </span>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
