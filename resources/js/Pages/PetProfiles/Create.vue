<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    type: '',
    when_needed: '',
    duration: '',
    hourly_rate: '',
    important_info: ''
});

const petTypes = [
    'Hond',
    'Kat',
    'Vogel',
    'Knaagdier',
    'Vis',
    'Anders'
];

const submit = () => {
    form.post(route('pet-profiles.store'));
};
</script>

<template>
    <Head title="Nieuw Huisdierprofiel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nieuw Huisdierprofiel
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <div class="mb-4">
                                <InputLabel for="name" value="Naam" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="type" value="Type Huisdier" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Selecteer type</option>
                                    <option v-for="type in petTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="when_needed" value="Wanneer nodig" />
                                <TextInput
                                    id="when_needed"
                                    type="date"
                                    v-model="form.when_needed"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.when_needed" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="duration" value="Duur (in uren)" />
                                <TextInput
                                    id="duration"
                                    type="number"
                                    v-model="form.duration"
                                    class="mt-1 block w-full"
                                    required
                                    min="1"
                                />
                                <InputError :message="form.errors.duration" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="hourly_rate" value="Uurtarief (€)" />
                                <TextInput
                                    id="hourly_rate"
                                    type="number"
                                    v-model="form.hourly_rate"
                                    class="mt-1 block w-full"
                                    required
                                    step="0.01"
                                    min="0"
                                />
                                <InputError :message="form.errors.hourly_rate" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="important_info" value="Belangrijke Informatie" />
                                <textarea
                                    id="important_info"
                                    v-model="form.important_info"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="4"
                                ></textarea>
                                <InputError :message="form.errors.important_info" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ml-4" :disabled="form.processing">
                                    Profiel Aanmaken
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>