<template>
    <div class="bg-white shadow-sm rounded-lg mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Huisdier Type Filter -->
        <div>
          <InputLabel for="huisdier_type" value="Type Huisdier" />
          <select
            id="huisdier_type"
            v-model="localFilters.huisdier_type"
            @change="updateFilters"
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
  
        <!-- Maximum Uurtarief Filter -->
        <div>
          <InputLabel for="max_uurtarief" value="Maximum uurtarief (€)" />
          <TextInput
            id="max_uurtarief"
            type="number"
            v-model="localFilters.max_uurtarief"
            @input="updateFilters"
            class="mt-1 block w-full"
            min="0"
            step="0.50"
          />
        </div>
      </div>
  
      <!-- Reset Filters -->
      <div v-if="hasActiveFilters" class="mt-4 flex justify-end">
        <SecondaryButton @click="resetFilters" class="text-sm">
          Filters wissen
        </SecondaryButton>
      </div>

      <!-- Debug Info -->
      <div class="mt-4 p-2 bg-gray-100 rounded text-xs">
        <pre>Huidige filters: {{ JSON.stringify(localFilters, null, 2) }}</pre>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { router } from '@inertiajs/vue3';
  import InputLabel from '@/Components/InputLabel.vue';
  import TextInput from '@/Components/TextInput.vue';
  import SecondaryButton from '@/Components/SecondaryButton.vue';
  
  const props = defineProps({
    initialFilters: {
      type: Object,
      default: () => ({
        huisdier_type: '',
        max_uurtarief: ''
      })
    }
  });
  
  // Log initiële props
  onMounted(() => {
    console.log('Component gemount met initiële filters:', props.initialFilters);
  });
  
  const localFilters = ref({...props.initialFilters});
  
  const hasActiveFilters = computed(() => {
    return localFilters.value.huisdier_type || localFilters.value.max_uurtarief;
  });
  
  let timeoutId = null;
  const updateFilters = () => {
    console.log('Filter update gestart met waarden:', localFilters.value);
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      console.log('Filter update wordt uitgevoerd met:', localFilters.value);
      router.get(
        route('sitter-profiles.index'),
        localFilters.value,
        {
          preserveState: true,
          preserveScroll: true,
          onBefore: () => {
            console.log('Voor de request:', localFilters.value);
          },
          onSuccess: (page) => {
            console.log('Request succesvol:', page);
          },
          onError: (errors) => {
            console.error('Filter fouten:', errors);
          }
        }
      );
    }, 300);
  };
  
  const resetFilters = () => {
    console.log('Filters worden gereset');
    localFilters.value = {
      huisdier_type: '',
      max_uurtarief: ''
    };
    updateFilters();
  };
  </script>