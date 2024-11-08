// resources/js/Pages/Admin/Dashboard.vue
<script setup>
import { Link, router } from '@inertiajs/vue3';  // Voeg router import toe
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    sittingRequests: {
        type: Array,
        required: true
    }
});

const loading = ref(false);

const handleUserStatus = async (userId, action) => {
    loading.value = true;
    try {
        // Gebruik Inertia router in plaats van axios
        await router.patch(`/admin/users/${userId}/status`, { 
            action: action 
        }, {
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
            },
            onError: () => {
                loading.value = false;
                alert('Er ging iets mis bij het bijwerken van de gebruiker status');
            }
        });
    } catch (error) {
        loading.value = false;
        console.error('Error updating user status:', error);
    }
};

const handleRequestStatus = async (requestId, status) => {
    loading.value = true;
    try {
        // Gebruik Inertia router in plaats van axios
        await router.patch(`/admin/sitting-requests/${requestId}/status`, {
            status: status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
            },
            onError: () => {
                loading.value = false;
                alert('Er ging iets mis bij het bijwerken van het oppasverzoek');
            }
        });
    } catch (error) {
        loading.value = false;
        console.error('Error updating request status:', error);
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('nl-NL', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Gebruikers Beheer -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Gebruikers Beheer</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Naam
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acties
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-green-100 text-green-800': user.status === 'active',
                                                'bg-red-100 text-red-800': user.status === 'blocked'
                                            }">
                                                {{ user.status === 'active' ? 'Actief' : 'Geblokkeerd' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button
                                                @click="handleUserStatus(user.id, user.status === 'active' ? 'block' : 'unblock')"
                                                :disabled="loading"
                                                :class="{
                                                    'text-sm font-medium rounded-md px-3 py-1': true,
                                                    'text-red-600 hover:text-red-900': user.status === 'active',
                                                    'text-green-600 hover:text-green-900': user.status === 'blocked',
                                                    'opacity-50 cursor-not-allowed': loading
                                                }"
                                            >
                                                {{ user.status === 'active' ? 'Blokkeer' : 'Deblokkeer' }}
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Oppasaanvragen Beheer -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Oppasaanvragen Beheer</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Huisdier
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Eigenaar
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Oppas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acties
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in sittingRequests" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.pet_profile?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.user?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ request.sitter_profile?.user?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-blue-100 text-blue-800': request.status === 'open',
                                                'bg-green-100 text-green-800': request.status === 'accepted',
                                                'bg-red-100 text-red-800': request.status === 'rejected',
                                                'bg-gray-100 text-gray-800': request.status === 'completed'
                                            }">
                                                {{ request.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                Start: {{ formatDate(request.start_datum) }}<br>
                                                Eind: {{ formatDate(request.eind_datum) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button
                                                v-if="request.status !== 'completed'"
                                                @click="handleRequestStatus(request.id, 'complete')"
                                                :disabled="loading"
                                                class="text-green-600 hover:text-green-900 text-sm font-medium mr-2"
                                                :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                            >
                                                Voltooid
                                            </button>
                                            <button
                                                @click="handleRequestStatus(request.id, 'delete')"
                                                :disabled="loading"
                                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                                                :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                            >
                                                Verwijder
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>