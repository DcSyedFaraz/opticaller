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

const classes = computed(() =>
    props.active
        ? 'flex-1 flex flex-col items-center group'
        : 'flex-1 flex flex-col items-center group'
);

const iconClasses = computed(() =>
    props.active
        ? 'w-10 h-10 flex justify-center items-center bg-gray-200 rounded-full !bg-white !text-primary transition'
        : 'w-10 h-10 flex justify-center items-center bg-gray-200 rounded-full hover:bg-white hover:text-primary transition'
);
</script>

<template>
    <Link :href="href" :class="classes">
    <div :class="iconClasses">
        <slot />
    </div>
    </Link>
</template>
