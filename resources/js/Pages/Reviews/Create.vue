<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    sittingRequest: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    sitting_request_id: props.sittingRequest.id,
    rating: 5,
    review_text: ''
});

const submitReview = () => {
    form.post(route('reviews.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        }
    });
};

// Reset form when sitting request changes
watch(() => props.sittingRequest, (newVal) => {
    if (newVal) {
        form.sitting_request_id = newVal.id;
        form.rating = 5;
        form.review_text = '';
    }
});
</script>

<template>
    <Modal :show="true" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                Schrijf een Review
            </h3>

            <form @submit.prevent="submitReview">
                <div v-if="form.errors.error" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    {{ form.errors.error }}
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Rating</label>
                    <div class="flex space-x-2 mt-1">
                        <template v-for="n in 5" :key="n">
                            <button
                                type="button"
                                @click="form.rating = n"
                                class="focus:outline-none"
                            >
                                <svg
                                    :class="[
                                        'h-6 w-6',
                                        n <= form.rating ? 'text-yellow-400' : 'text-gray-300'
                                    ]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        </template>
                    </div>
                    <div v-if="form.errors.rating" class="text-red-500 text-sm mt-1">
                        {{ form.errors.rating }}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Review</label>
                    <textarea
                        v-model="form.review_text"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    ></textarea>
                    <div v-if="form.errors.review_text" class="text-red-500 text-sm mt-1">
                        {{ form.errors.review_text }}
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                        @click="$emit('close')"
                    >
                        Annuleren
                    </button>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Bezig...' : 'Review Plaatsen' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>