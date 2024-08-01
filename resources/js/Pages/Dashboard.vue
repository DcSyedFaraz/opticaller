<script setup>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { usePage } from '@inertiajs/vue3';

const store = useStore();
const { props } = usePage();

const roles = computed(() => store.state.roles);

onMounted(() => {
    // console.log(props.auth.roles);
    store.dispatch('initializeStore', {
        user: props.auth.user,
        roles: props.auth.roles,
    });
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard <p>Roles: {{ roles.join(', ') }}</p></h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
