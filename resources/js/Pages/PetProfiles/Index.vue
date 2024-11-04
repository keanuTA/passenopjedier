<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    petProfiles: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Oppas Aanvragen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Oppas Aanvragen
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between mb-6">
                            <h3 class="text-lg font-medium">Beschikbare Oppas Aanvragen</h3>
                            <a href="/pet-profiles/create" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Nieuwe Aanvraag
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Placeholder voor profielen -->
                            <div v-for="profile in petProfiles" :key="profile.id"
                                 class="border rounded-lg p-4 hover:shadow-lg transition">
                                <h4 class="font-bold">{{ profile.description }}</h4>
                                <p class="text-gray-600">Wanneer: {{ profile.when_needed }}</p>
                                <p class="text-gray-600">Duur: {{ profile.duration }} uur</p>
                                <p class="text-green-600 font-bold">€{{ profile.hourly_rate }}/uur</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>