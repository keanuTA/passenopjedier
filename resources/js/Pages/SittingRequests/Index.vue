<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
                                            Van: {{ new Date(request.start_datum).toLocaleString() }}
                                        </p>
                                        <p class="text-gray-600">
                                            Tot: {{ new Date(request.eind_datum).toLocaleString() }}
                                        </p>
                                        <p class="text-gray-600">
                                            Uurtarief: €{{ request.uurtarief }}
                                        </p>
                                        <p class="mt-2">{{ request.extra_informatie }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Status: {{ request.status }}
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
    </AuthenticatedLayout>
</template>