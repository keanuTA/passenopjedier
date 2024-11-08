<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    sitterProfile: {
        type: Object,
        required: true
    },
    huisdier_types: {
        type: Array,
        required: true
    }
});

console.log('Props received:', props);
console.log('SitterProfile:', props.sitterProfile);

const form = useForm({
    ervaring: props.sitterProfile.ervaring,
    over_mij: props.sitterProfile.over_mij,
    huisdier_voorkeuren: Array.isArray(props.sitterProfile.huisdier_voorkeuren) 
        ? props.sitterProfile.huisdier_voorkeuren 
        : JSON.parse(props.sitterProfile.huisdier_voorkeuren || '[]'),
    uurtarief: props.sitterProfile.uurtarief,
    profielfoto: null,
    video_intro: null,
    is_beschikbaar: props.sitterProfile.is_beschikbaar
});

const submitForm = () => {
    form.put(route('sitter-profiles.update', props.sitterProfile.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect gebeurt automatisch via de controller
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Profiel Bewerken</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Over mij -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Over mij</label>
                                <textarea
                                    v-model="form.over_mij"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.over_mij" class="mt-2" />
                            </div>

                            <!-- Ervaring -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ervaring met huisdieren</label>
                                <textarea
                                    v-model="form.ervaring"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.ervaring" class="mt-2" />
                            </div>

                            <!-- Huisdier voorkeuren -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Huisdier voorkeuren</label>
                                <div class="space-y-2">
                                    <label v-for="type in huisdier_types" :key="type" class="inline-flex items-center mr-4">
                                        <input
                                            type="checkbox"
                                            :value="type"
                                            v-model="form.huisdier_voorkeuren"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span class="ml-2">{{ type }}</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.huisdier_voorkeuren" class="mt-2" />
                            </div>

                            <!-- Uurtarief -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Uurtarief (€)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    v-model="form.uurtarief"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <InputError :message="form.errors.uurtarief" class="mt-2" />
                            </div>

                            <!-- Profielfoto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Profielfoto</label>
                                <input
                                    type="file"
                                    @input="form.profielfoto = $event.target.files[0]"
                                    accept="image/*"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.profielfoto" class="mt-2" />
                                
                                <!-- Huidige foto preview -->
                                <img 
                                    v-if="sitterProfile.profielfoto_pad"
                                    :src="`/storage/sitter_uploads/${sitterProfile.profielfoto_pad}`"
                                    class="mt-2 h-32 w-32 object-cover rounded-lg"
                                    alt="Huidige profielfoto"
                                />
                            </div>

                            <!-- Video intro -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Video introductie</label>
                                <input
                                    type="file"
                                    @input="form.video_intro = $event.target.files[0]"
                                    accept="video/*"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.video_intro" class="mt-2" />
                                
                                <!-- Huidige video preview -->
                                <video 
                                    v-if="sitterProfile.video_intro_pad"
                                    :src="`/storage/sitter_uploads/${sitterProfile.video_intro_pad}`"
                                    controls
                                    class="mt-2 max-w-full h-48"
                                >
                                    Je browser ondersteunt geen video weergave.
                                </video>
                            </div>

                            <!-- Beschikbaarheid -->
                            <div>
                                <label class="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_beschikbaar"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2">Ik ben beschikbaar voor oppassen</span>
                                </label>
                            </div>

                            <!-- Submit buttons -->
                            <div class="flex justify-end space-x-4">
                                <Link
                                    :href="route('sitter-profiles.show', sitterProfile.id)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                                >
                                    Annuleren
                                </Link>
                                <PrimaryButton :disabled="form.processing">
                                    Opslaan
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>