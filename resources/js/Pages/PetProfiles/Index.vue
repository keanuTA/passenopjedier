<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    petProfiles: Array
});
</script>

<template>
    <Head title="Huisdierprofielen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Huisdierprofielen
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Mijn Huisdieren</h3>
                            <Link
                                :href="route('pet-profiles.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Nieuw Huisdierprofiel
                            </Link>
                        </div>

                        <div v-if="petProfiles.length === 0" class="text-center py-8">
                            <p>Je hebt nog geen huisdierprofielen aangemaakt.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="profile in petProfiles" :key="profile.id" 
                                 class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <h4 class="font-bold text-lg">{{ profile.name }}</h4>
                                <p class="text-gray-600">{{ profile.type }}</p>
                                <div class="mt-2">
                                    <p><span class="font-medium">Wanneer nodig:</span> {{ profile.when_needed }}</p>
                                    <p><span class="font-medium">Duur:</span> {{ profile.duration }} uur</p>
                                    <p><span class="font-medium">Uurtarief:</span> €{{ profile.hourly_rate }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>