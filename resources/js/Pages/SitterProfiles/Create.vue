<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const form = useForm({
    ervaring: '',
    over_mij: '',
    huisdier_voorkeuren: [],
    uurtarief: '',
    profielfoto: null,
    video_intro: null,
    is_beschikbaar: true
});

const huisdierTypes = [
    'Hond',
    'Kat',
    'Vogel',
    'Knaagdier',
    'Vis',
    'Reptiel',
    'Anders'
];

const profielfotoPreview = ref(null);
const videoPreview = ref(null);

const handleFotoUpload = (e) => {
    const file = e.target.files[0];
    form.profielfoto = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            profielfotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleVideoUpload = (e) => {
    const file = e.target.files[0];
    form.video_intro = file;
    if (file) {
        videoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('sitter-profiles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            profielfotoPreview.value = null;
            videoPreview.value = null;
        },
    });
};
</script>

<template>
    <Head title="Oppasprofiel Aanmaken" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Oppasprofiel Aanmaken</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="max-w-xl mx-auto">
                            <!-- Ervaring -->
                            <div class="mb-6">
                                <InputLabel for="ervaring" value="Ervaring met huisdieren" />
                                <textarea
                                    id="ervaring"
                                    v-model="form.ervaring"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="4"
                                    required
                                    placeholder="Beschrijf je ervaring met het verzorgen van huisdieren..."
                                ></textarea>
                                <InputError :message="form.errors.ervaring" class="mt-2" />
                            </div>

                            <!-- Over Mij -->
                            <div class="mb-6">
                                <InputLabel for="over_mij" value="Over mij" />
                                <textarea
                                    id="over_mij"
                                    v-model="form.over_mij"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="4"
                                    required
                                    placeholder="Vertel wat over jezelf..."
                                ></textarea>
                                <InputError :message="form.errors.over_mij" class="mt-2" />
                            </div>

                            <!-- Huisdier Voorkeuren -->
                            <div class="mb-6">
                                <InputLabel value="Huisdier voorkeuren" />
                                <div class="mt-2 grid grid-cols-2 gap-4">
                                    <div v-for="type in huisdierTypes" :key="type" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :id="type"
                                            v-model="form.huisdier_voorkeuren"
                                            :value="type"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <label :for="type" class="ml-2 text-sm text-gray-600">{{ type }}</label>
                                    </div>
                                </div>
                                <InputError :message="form.errors.huisdier_voorkeuren" class="mt-2" />
                            </div>

                            <!-- Uurtarief -->
                            <div class="mb-6">
                                <InputLabel for="uurtarief" value="Uurtarief (€)" />
                                <TextInput
                                    id="uurtarief"
                                    type="number"
                                    v-model="form.uurtarief"
                                    class="mt-1 block w-full"
                                    required
                                    step="0.50"
                                    min="5"
                                    max="100"
                                />
                                <InputError :message="form.errors.uurtarief" class="mt-2" />
                            </div>

                            <!-- Profielfoto -->
                            <div class="mb-6">
                                <InputLabel for="profielfoto" value="Profielfoto" />
                                <input
                                    type="file"
                                    id="profielfoto"
                                    @change="handleFotoUpload"
                                    accept="image/*"
                                    class="mt-1 block w-full"
                                />
                                <div v-if="profielfotoPreview" class="mt-2">
                                    <img :src="profielfotoPreview" alt="Preview" class="max-w-xs rounded-lg shadow" />
                                </div>
                                <InputError :message="form.errors.profielfoto" class="mt-2" />
                            </div>

                            <!-- Video Intro -->
                            <div class="mb-6">
                                <InputLabel for="video_intro" value="Video introductie (optioneel)" />
                                <input
                                    type="file"
                                    id="video_intro"
                                    @change="handleVideoUpload"
                                    accept="video/*"
                                    class="mt-1 block w-full"
                                />
                                <video 
                                    v-if="videoPreview" 
                                    :src="videoPreview" 
                                    controls 
                                    class="mt-2 max-w-xs rounded-lg shadow"
                                ></video>
                                <InputError :message="form.errors.video_intro" class="mt-2" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end mt-6">
                                <PrimaryButton :disabled="form.processing">
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