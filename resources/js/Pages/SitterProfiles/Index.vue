<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    profiles: Object,
    filters: Object,
    can: Object
});

const search = ref({
    huisdier_type: props.filters.huisdier_type || '',
    max_uurtarief: props.filters.max_uurtarief || ''
});

// Debounced filter function
const updateFilter = debounce(() => {
    router.get(
        route('sitter-profiles.index'),
        search.value,
        { preserveState: true }
    );
}, 300);

watch(search, updateFilter, { deep: true });
</script>

<template>
    <Head title="Oppasprofielen" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Oppasprofielen</h2>
                <Link
                    v-if="can.create_profile"
                    :href="route('sitter-profiles.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                    Word Oppas
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white shadow-sm rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type Huisdier</label>
                            <select
                                v-model="search.huisdier_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Alle types</option>
                                <option value="Hond">Hond</option>
                                <option value="Kat">Kat</option>
                                <option value="Vogel">Vogel</option>
                                <option value="Knaagdier">Knaagdier</option>
                                <option value="Vis">Vis</option>
                                <option value="Anders">Anders</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maximum uurtarief (€)</label>
                            <input
                                type="number"
                                v-model="search.max_uurtarief"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="0"
                                step="0.50"
                            >
                        </div>
                    </div>
                </div>

                <!-- Profielen Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="profile in profiles.data" :key="profile.id" 
                         class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow">
                        <div class="relative pb-2/3">
                            <img
                                v-if="profile.profielfoto_url"
                                :src="profile.profielfoto_url"
                                :alt="`Profielfoto van ${profile.user.name}`"
                                class="absolute h-full w-full object-cover"
                            >
                            <div v-else class="absolute h-full w-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400">Geen foto</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-lg">{{ profile.user.name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">€{{ profile.uurtarief }}/uur</p>
                            
                            <div class="mt-2">
                                <h4 class="text-sm font-medium text-gray-700">Past op:</h4>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span 
                                        v-for="type in profile.huisdier_voorkeuren" 
                                        :key="type"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                    >
                                        {{ type }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <Link
                                    :href="route('sitter-profiles.show', profile.id)"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                                    @click="(e) => {
                                        console.log('Clicking profile:', profile.id);
                                        if (!profile.id) {
                                            e.preventDefault();
                                            alert('Geen geldig profiel ID gevonden');
                                        }
                                    }"
                                >
                                    Bekijk Profiel
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paginatie -->
                <div v-if="profiles.links && profiles.links.length > 3" class="mt-6">
                    <!-- Implementeer paginatie component hier -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.pb-2\/3 {
    padding-bottom: 66.666667%;
}
</style>