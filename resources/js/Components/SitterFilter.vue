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
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
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
  
  const localFilters = ref({...props.initialFilters});
  
  const hasActiveFilters = computed(() => {
    return localFilters.value.huisdier_type || localFilters.value.max_uurtarief;
  });
  
  let timeoutId = null;
  const updateFilters = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      router.get(
        route('sitter-profiles.index'),
        localFilters.value,
        {
          preserveState: true,
          preserveScroll: true
        }
      );
    }, 300);
  };
  
  const resetFilters = () => {
    localFilters.value = {
      huisdier_type: '',
      max_uurtarief: ''
    };
    updateFilters();
  };
  </script>