<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({ client: Object })

const form = useForm({
    name:        props.client.name,
    email:       props.client.email,
    phone:       props.client.phone       ?? '',
    address:     props.client.address,
    postal_code: props.client.postal_code,
    city:        props.client.city,
    country:     props.client.country,
    vat_number:  props.client.vat_number  ?? '',
    notes:       props.client.notes       ?? '',
})

const submit = () => form.put(route('clients.update', props.client.id))
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('clients.index')" class="text-gray-500 hover:text-gray-700">←</Link>
                <h2 class="text-xl font-semibold text-gray-800">Modifier client / Редагувати клієнта</h2>
            </div>
        </template>

        <div class="py-8 max-w-2xl mx-auto px-4">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-8 space-y-5">
                <div>
                    <InputLabel for="name" value="Nom / Ім'я *" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="email" value="Email *" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Téléphone / Телефон" />
                        <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                    </div>
                </div>
                <div>
                    <InputLabel for="address" value="Adresse / Адреса *" />
                    <textarea id="address" v-model="form.address" rows="2" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    <InputError class="mt-1" :message="form.errors.address" />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="postal_code" value="Code postal *" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="city" value="Ville / Місто *" />
                        <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="country" value="Pays / Країна *" />
                        <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" required />
                    </div>
                </div>
                <div>
                    <InputLabel for="vat_number" value="N° TVA / ЄДРПОУ" />
                    <TextInput id="vat_number" v-model="form.vat_number" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="notes" value="Notes internes / Нотатки" />
                    <textarea id="notes" v-model="form.notes" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? '...' : 'Enregistrer / Зберегти' }}
                </PrimaryButton>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
