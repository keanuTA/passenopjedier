<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewCreate from '@/Pages/Reviews/Create.vue';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    requests: {
        type: Array,
        required: true
    },
    hasSitterProfile: {
        type: Boolean,
        required: true
    }
});

const showReviewModal = ref(false);
const selectedRequest = ref(null);

const statusForm = useForm({
    status: ''
});

const handleStatus = (request, status) => {
    statusForm.status = status;
    statusForm.patch(route('sitting-requests.update', request.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (status === 'accepted') {
                selectedRequest.value = request;
                showReviewModal.value = true;
            }
        }
    });
};

const handleCloseReview = () => {
    showReviewModal.value = false;
    selectedRequest.value = null;
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
            <h2 class="font-semibold text-xl text-gray-800">Ontvangen Oppasverzoeken</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Voor gebruikers zonder oppasprofiel -->
                        <div v-if="!hasSitterProfile" class="text-center py-8">
                            <div class="mb-4 text-gray-600">
                                Om oppasverzoeken te kunnen ontvangen, moet je eerst een oppasprofiel aanmaken.
                            </div>
                            <Link
                                :href="route('sitter-profiles.create')"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Maak Oppasprofiel Aan
                            </Link>
                        </div>

                        <!-- Voor gebruikers met oppasprofiel en verzoeken -->
                        <div v-else-if="requests.length > 0" class="space-y-6">
                            <div v-for="request in requests" :key="request.id" 
                                 class="border rounded-lg p-6 hover:bg-gray-50">
                                <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                                    <!-- Request Informatie -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ request.pet_profile?.name }}
                                            </h3>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                                  :class="{
                                                    'bg-blue-100 text-blue-800': request.status === 'open',
                                                    'bg-green-100 text-green-800': request.status === 'accepted',
                                                    'bg-red-100 text-red-800': request.status === 'rejected'
                                                  }">
                                                {{ request.status === 'open' ? 'Open' : 
                                                   request.status === 'accepted' ? 'Geaccepteerd' : 
                                                   'Afgewezen' }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600 mb-2">
                                            <p class="mb-1">Eigenaar: {{ request.user?.name }}</p>
                                            <p class="mb-1">Type huisdier: {{ request.pet_profile?.type }}</p>
                                            <p class="mb-1">Van: {{ formatDate(request.start_datum) }}</p>
                                            <p class="mb-1">Tot: {{ formatDate(request.eind_datum) }}</p>
                                            <p class="mb-1">Uurtarief: €{{ request.uurtarief }}/uur</p>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <h4 class="font-medium text-gray-900 mb-1">Extra informatie:</h4>
                                            <p class="text-gray-600">{{ request.extra_informatie }}</p>
                                        </div>
                                    </div>

                                    <!-- Actie Knoppen -->
                                    <div v-if="request.status === 'open'" 
                                         class="flex flex-col gap-2 min-w-[200px]">
                                        <button 
                                            @click="handleStatus(request, 'accepted')"
                                            :disabled="statusForm.processing"
                                            class="w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {{ statusForm.processing ? 'Bezig...' : 'Accepteren' }}
                                        </button>
                                        <button 
                                            @click="handleStatus(request, 'rejected')"
                                            :disabled="statusForm.processing"
                                            class="w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {{ statusForm.processing ? 'Bezig...' : 'Weigeren' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Voor gebruikers met oppasprofiel maar zonder verzoeken -->
                        <div v-else class="text-center py-8">
                            <div class="text-gray-600 mb-4">
                                Je hebt nog geen oppasverzoeken ontvangen.
                            </div>
                            <p class="text-sm text-gray-500">
                                Zodra huisdiereigenaren je oppasprofiel vinden en een verzoek sturen, 
                                zul je deze hier kunnen zien en beheren.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Component -->
        <ReviewCreate
            v-if="showReviewModal && selectedRequest"
            :sitting-request="selectedRequest"
            @close="handleCloseReview"
        />
    </AuthenticatedLayout>
</template>