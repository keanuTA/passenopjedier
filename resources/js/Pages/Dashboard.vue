<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <!-- Welkomstpagina Content -->
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12">
                        <!-- Welkomsttekst -->
                        <h1 class="text-4xl font-bold text-blue-800 mb-4 text-center">
                            Welkom bij PassenOpJeDier.nl!
                        </h1>
                        
                        <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto text-center">
                            Of je nu op zoek bent naar de perfecte oppas voor je huisdier of je wilt graag op huisdieren passen - 
                            wij brengen dierenliefhebbers bij elkaar. Maak vandaag nog je keuze en word deel van onze community!
                        </p>

                        <!-- Button Container -->
                        <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                            <!-- Zoek een oppas button -->
                            <Link
                                :href="route('sitting-requests.index')"
                                class="h-32 text-xl bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 flex flex-col items-center justify-center gap-3"
                            >
                                <i class="fas text-3xl">&#xf6d3;</i>
                                <span>Zoek een oppas!</span>
                            </Link>

                            <!-- Word oppas button -->
                            <Link
                                :href="route('sitter-profiles.index')"
                                class="h-32 text-xl bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-all duration-200 flex flex-col items-center justify-center gap-3"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <span>Word oppas!</span>
                            </Link>
                        </div>

                        <!-- Extra informatie -->
                        <div class="mt-12 grid md:grid-cols-3 gap-6 text-gray-600">
                            <div>
                                <h3 class="font-semibold mb-2">Veilig & Betrouwbaar</h3>
                                <p class="text-sm">Geverifieerde oppassen en eigenaren</p>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Persoonlijke Match</h3>
                                <p class="text-sm">Vind de perfecte oppas voor jouw huisdier</p>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Community</h3>
                                <p class="text-sm">Een netwerk van dierenliefhebbers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        Link
    }
}
</script>