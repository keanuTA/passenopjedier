<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    profile: {
        type: Object,
        required: true
    }
});

const showContactModal = ref(false);

const contactForm = useForm({
    bericht: '',
    sitting_request_id: null
});

const submitContact = () => {
    contactForm.post(route('sitting-responses.store', { profile: profile.id }), {
        onSuccess: () => {
            showContactModal.value = false;
            contactForm.reset();
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Oppasprofiel</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <!-- Profielfoto -->
                        <div v-if="profile.profielfoto_pad" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Profielfoto</h3>
                            <img 
                                :src="`/storage/sitter_uploads/${profile.profielfoto_pad}`"
                                :alt="`Profielfoto van ${profile.user?.name ?? 'Oppas'}`"
                                class="rounded-lg max-w-sm"
                            >
                        </div>

                        <!-- Video Introductie -->
                        <div v-if="profile.video_intro_pad" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Video Introductie</h3>
                            <video 
                                :src="`/storage/sitter_uploads/${profile.video_intro_pad}`"
                                controls
                                class="rounded-lg max-w-lg"
                            >
                                Je browser ondersteunt geen video weergave.
                            </video>
                        </div>

                        <!-- Profiel Informatie -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    Over {{ profile.user?.name ?? 'Oppas' }}
                                </h3>
                                <p class="text-gray-600 whitespace-pre-line">{{ profile.over_mij }}</p>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="border-t pt-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Uurtarief</dt>
                                    <dd class="mt-1 text-lg text-gray-900">€{{ profile.uurtarief }}/uur</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Verzorgt</dt>
                                    <dd class="mt-1 text-lg text-gray-900">
                                        <span 
                                            v-for="(dier, index) in profile.huisdier_voorkeuren" 
                                            :key="dier"
                                            class="capitalize"
                                        >
                                            {{ dier }}{{ index < profile.huisdier_voorkeuren.length - 1 ? ', ' : '' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Reviews Sectie -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reviews</h3>
                            
                            <div v-if="profile.reviews?.length" class="space-y-4">
                                <div v-for="review in profile.reviews" :key="review.id" 
                                     class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <!-- Star Rating -->
                                        <div class="flex">
                                            <template v-for="n in 5" :key="n">
                                                <svg
                                                    :class="[
                                                        'h-5 w-5',
                                                        n <= review.rating ? 'text-yellow-400' : 'text-gray-300'
                                                    ]"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">
                                            door {{ review.user.name }}
                                        </span>
                                    </div>
                                    <p class="text-gray-600">{{ review.review_text }}</p>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                Nog geen reviews ontvangen.
                            </div>
                        </div>

                        <!-- Acties -->
                        <div class="border-t pt-6 flex justify-between items-center">
                            <Link
                                :href="route('sitter-profiles.index')"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                ← Terug naar overzicht
                            </Link>

                            <div class="space-x-4">
                                <div v-if="profile.user_id === $page.props.auth.user.id">
                                    <Link
                                        :href="route('sitter-profiles.edit', profile.id)"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                    >
                                        Bewerken
                                    </Link>
                                </div>
                                <div v-else>
                                    <PrimaryButton @click="showContactModal = true">
                                        Contact Opnemen
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Modal -->
        <Modal :show="showContactModal" @close="showContactModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Contact opnemen met {{ profile.user?.name ?? 'Oppas' }}
                </h3>
                <form @submit.prevent="submitContact">
                    <div class="mb-4">
                        <textarea
                            v-model="contactForm.bericht"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Schrijf hier je bericht..."
                            required
                        ></textarea>
                        <InputError :message="contactForm.errors.bericht" class="mt-2" />
                    </div>
                    
                    <div class="flex justify-end gap-4">
                        <button
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                            @click="showContactModal = false"
                        >
                            Annuleren
                        </button>
                        <PrimaryButton :disabled="contactForm.processing">
                            Versturen
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>