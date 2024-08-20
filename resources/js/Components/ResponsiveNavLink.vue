<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const linkClasses = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out'
);

const iconContainerClasses = computed(() =>
    props.active
        ? 'w-10 h-10 flex justify-center items-center bg-white text-pink-600 rounded-full transition'
        : 'w-10 h-10 flex justify-center items-center bg-gray-200 rounded-full hover:bg-white hover:text-pink-600 transition'
);
</script>

<template>
    <Link :href="href" :class="linkClasses">
    <div class="flex items-center">
        <div :class="iconContainerClasses">
            <slot name="icon" />
        </div>
        <div class="ml-3">
            <slot name="label" />
        </div>
    </div>
    </Link>
</template>
