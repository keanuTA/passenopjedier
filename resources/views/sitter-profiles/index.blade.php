// resources/views/sitter-profiles/index.blade.php
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Beschikbare Oppassen') }}
            </h2>
            @if(auth()->user()->role === 'oppas' && !auth()->user()->sitterProfile)
                <a href="{{ route('sitter-profiles.create') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Maak Oppasprofiel') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($profiles as $profile)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            @if($profile->profielfoto_pad)
                                <img src="{{ Storage::url($profile->profielfoto_pad) }}" 
                                     alt="Profielfoto van {{ $profile->user->name }}"
                                     class="w-full h-48 object-cover rounded-lg mb-4">
                            @endif
                            <h3 class="text-lg font-semibold mb-2">{{ $profile->user->name }}</h3>
                            <p class="text-gray-600 mb-2">€{{ number_format($profile->uurtarief, 2) }} per uur</p>
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ $profile->over_mij }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($profile->huisdier_voorkeuren as $dier)
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                        {{ $dier }}
                                    </span>
                                @endforeach
                            </div>
                            <a href="{{ route('sitter-profiles.show', $profile) }}" 
                               class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Bekijk Profiel
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $profiles->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

// resources/views/sitter-profiles/create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Oppasprofiel Aanmaken') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('sitter-profiles.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label for="over_mij" class="block text-gray-700 text-sm font-bold mb-2">
                                Over mij
                            </label>
                            <textarea id="over_mij" name="over_mij" rows="4"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('over_mij') border-red-500 @enderror">{{ old('over_mij') }}</textarea>
                            @error('over_mij')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="ervaring" class="block text-gray-700 text-sm font-bold mb-2">
                                Ervaring met huisdieren
                            </label>
                            <textarea id="ervaring" name="ervaring" rows="4"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('ervaring') border-red-500 @enderror">{{ old('ervaring') }}</textarea>
                            @error('ervaring')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Huisdier voorkeuren
                            </label>
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                                @foreach(['Hond', 'Kat', 'Konijn', 'Vogel', 'Vis', 'Knaagdier'] as $dier)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="huisdier_voorkeuren[]" value="{{ $dier }}"
                                            class="form-checkbox h-5 w-5 text-blue-600"
                                            {{ in_array($dier, old('huisdier_voorkeuren', [])) ? 'checked' : '' }}>
                                        <span class="ml-2">{{ $dier }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('huisdier_voorkeuren')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="uurtarief" class="block text-gray-700 text-sm font-bold mb-2">
                                Uurtarief (€)
                            </label>
                            <input type="number" step="0.50" min="5" max="100" id="uurtarief" name="uurtarief"
                                value="{{ old('uurtarief') }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('uurtarief') border-red-500 @enderror">
                            @error('uurtarief')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="profielfoto" class="block text-gray-700 text-sm font-bold mb-2">
                                Profielfoto
                            </label>
                            <input type="file" id="profielfoto" name="profielfoto" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('profielfoto')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="video_intro" class="block text-gray-700 text-sm font-bold mb-2">
                                Video introductie
                            </label>
                            <input type="file" id="video_intro" name="video_intro" accept="video/mp4"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('video_intro')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Profiel Aanmaken
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

// resources/views/sitter-profiles/show.blade.php
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Oppasprofiel van ') }} {{ $profile->user->name }}
            </h2>
            @if(auth()->id() === $profile->user_id)
                <a href="{{ route('sitter-profiles.edit', $profile) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Profiel Bewerken
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            @if($profile->profielfoto_pad)
                                <img src="{{ Storage::url($profile->profielfoto_pad) }}" 
                                     alt="Profielfoto van {{ $profile->user->name }}"
                                     class="w-full h-64 object-cover rounded-lg mb-4">
                            @endif
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h3 class="font-semibold text-lg mb-2">Uurtarief</h3>
                                <p class="text-2xl font-bold text-blue-600">€{{ number_format($profile->uurtarief, 2) }}</p>
                            </div>

                            @if($profile->video_intro_pad)
                                <div class="mb-4">
                                    <h3 class="font-semibold text-lg mb-2">Video Introductie</h3>
                                    <video controls class="w-full rounded-lg">
                                        <source src="{{ Storage::url($profile->video_intro_pad) }}" type="video/mp4">
                                        Je browser ondersteunt geen video weergave.
                                    </video>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="mb-6">
                                <h3 class="font-semibold text-lg mb-2">Over mij</h3>
                                <p class="text-gray-700 whitespace-pre-line">{{ $profile->over_mij }}</p>
                            </div>

                            <div class="mb-6">
                                <h3 class="font-semibold text-lg mb-2">Ervaring</h3>
                                <p class="text-gray-700 whitespace-pre-line">{{ $profile->ervaring }}</p>
                            </div>

                            <div class="mb-6">
                                <h3 class="font-semibold text-lg mb-2">Huisdier voorkeuren</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($profile->huisdier_voorkeuren as $dier)
                                        <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-2.5 py-0.5 rounded">
                                            {{ $dier }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            @if(auth()->user()->role === 'eigenaar')
                                <div class="mt-6">
                                    <a href="{{ route('contact.sitter', $profile) }}" 
                                       class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Contact Opnemen
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>