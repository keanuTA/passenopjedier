<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Gebruikers Sectie -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Gebruikers Beheer</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Naam
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acties
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    user.is_blocked 
                                                        ? 'bg-red-100 text-red-800' 
                                                        : 'bg-green-100 text-green-800'
                                                ]"
                                            >
                                                {{ user.is_blocked ? 'Geblokkeerd' : 'Actief' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button 
                                                @click="toggleUserBlock(user)"
                                                :class="[
                                                    'px-4 py-2 rounded-md text-sm font-medium',
                                                    user.is_blocked
                                                        ? 'bg-green-600 text-white hover:bg-green-700'
                                                        : 'bg-red-600 text-white hover:bg-red-700'
                                                ]"
                                            >
                                                {{ user.is_blocked ? 'Deblokkeren' : 'Blokkeren' }}
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="users.links" class="mt-6" />
                    </div>
                </div>

                <!-- Oppasaanvragen Sectie -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Oppasaanvragen Beheer</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Eigenaar
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Oppas
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Start Datum
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Eind Datum
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acties
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in sittingRequests.data" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.owner.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.sitter_profile?.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(request.start_datum) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(request.eind_datum) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button 
                                                @click="deleteSittingRequest(request)"
                                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                            >
                                                Verwijderen
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="sittingRequests.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { nl } from 'date-fns/locale';

const props = defineProps({
    users: Object,
    sittingRequests: Object
});

const formatDate = (date) => {
    return format(new Date(date), 'PPP', { locale: nl });
};

const toggleUserBlock = (user) => {
    router.post(route('admin.users.toggle-block', user.id), {}, {
        preserveScroll: true
    });
};

const deleteSittingRequest = (request) => {
    if (confirm('Weet je zeker dat je deze oppasaanvraag wilt verwijderen?')) {
        router.delete(route('admin.sitting-requests.delete', request.id), {
            preserveScroll: true
        });
    }
};
</script>