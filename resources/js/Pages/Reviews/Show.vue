<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
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
                            </dl>
                        </div>

                        <!-- Reviews Sectie -->
                        <div class="border-t pt-6">
                            <!-- Gemiddelde Rating -->
                            <div v-if="profile.reviews?.length" class="mb-4">
                                <div class="flex items-center">
                                    <span class="text-lg font-medium mr-2">Gemiddelde beoordeling:</span>
                                    <div class="flex items-center">
                                        <template v-for="n in 5" :key="n">
                                            <svg
                                                :class="[
                                                    'h-5 w-5',
                                                    n <= Math.round(profile.average_rating) ? 'text-yellow-400' : 'text-gray-300'
                                                ]"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </template>
                                        <span class="ml-2 text-gray-600">({{ profile.average_rating }})</span>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reviews</h3>
                            
                            <!-- Reviews List -->
                            <div v-if="profile.reviews?.length" class="space-y-4">
                                <div v-for="review in profile.reviews" :key="review.id" 
                                     class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-4">
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
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </template>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ review.user.name }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            {{ formatDate(review.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700">{{ review.review_text }}</p>
                                </div>
                            </div>
                            
                            <!-- No Reviews Message -->
                            <div v-else class="text-center py-8 text-gray-500">
                                Nog geen reviews ontvangen.
                            </div>
                        </div>

                        <!-- Acties -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between">
                                <Link
                                    :href="route('sitter-profiles.index')"
                                    class="text-gray-600 hover:text-gray-900"
                                >
                                    ← Terug naar overzicht
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>