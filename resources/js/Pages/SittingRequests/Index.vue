<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewCreate from '@/Pages/Reviews/Create.vue';
import { ref } from 'vue';

const props = defineProps({
    requests: {
        type: Array,
        required: true
    },
    isSitter: {
        type: Boolean,
        required: true
    }
});

const showReviewModal = ref(false);
const selectedRequest = ref(null);

const statusForm = useForm({
    status: ''
});

const handleComplete = (request) => {
    statusForm.status = 'completed';
    statusForm.put(route('sitting-requests.update', request.id), {
        onSuccess: () => {
            selectedRequest.value = request;
            showReviewModal.value = true;
        }
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">
                {{ isSitter ? 'Ontvangen Oppasverzoeken' : 'Mijn Oppasverzoeken' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Verzoeken overzicht -->
                        <div v-if="requests.length > 0" class="space-y-6">
                            <div v-for="request in requests" :key="request.id" 
                                 class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium">
                                            {{ isSitter ? `Verzoek van ${request.user.name}` : `Verzoek voor ${request.sitter_profile?.user?.name}` }}
                                        </h3>
                                        <p class="text-gray-600 mt-1">
                                            Van: {{ formatDate(request.start_datum) }}
                                        </p>
                                        <p class="text-gray-600">
                                            Tot: {{ formatDate(request.eind_datum) }}
                                        </p>
                                        <p class="text-gray-600">
                                            Uurtarief: €{{ request.uurtarief }}
                                        </p>
                                        <p class="mt-2">{{ request.extra_informatie }}</p>
                                    </div>
                                    <div class="text-sm">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                            :class="{
                                                'bg-blue-100 text-blue-800': request.status === 'open',
                                                'bg-green-100 text-green-800': request.status === 'accepted',
                                                'bg-yellow-100 text-yellow-800': request.status === 'completed',
                                                'bg-red-100 text-red-800': request.status === 'rejected'
                                            }">
                                            {{ request.status === 'open' ? 'Open' : 
                                               request.status === 'accepted' ? 'Geaccepteerd' : 
                                               request.status === 'completed' ? 'Afgerond' :
                                               'Afgewezen' }}
                                        </span>

                                        <div v-if="request.status === 'accepted' && !isSitter" class="mt-2">
                                            <button 
                                                @click="handleComplete(request)"
                                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors"
                                            >
                                                Afronden en Review Schrijven
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            Geen oppasverzoeken gevonden.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ReviewCreate
            v-if="showReviewModal && selectedRequest"
            :sitting-request="selectedRequest"
            @close="() => {
                showReviewModal.value = false;
                selectedRequest.value = null;
            }"
        />
    </AuthenticatedLayout>
</template>