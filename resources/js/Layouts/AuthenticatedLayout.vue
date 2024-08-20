<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <header class="flex justify-between items-center bg-[#eae9e7] p-4 md:p-6">
        <div class="flex items-center">
            <!-- Optionally, you can uncomment and use the following lines -->
            <!-- <h1 class="text-pink-600 text-2xl md:text-3xl font-bold">OptiCaller</h1> -->
            <!-- <input type="text" placeholder="Search here..." class="hidden md:block ml-4 md:ml-8 p-2 w-full md:w-64 bg-gray-200 rounded"> -->
        </div>
        <div class="flex flex-col mr-4 md:mr-12 text-center md:text-right">
            <span class="text-gray-700 font-bold">Emma Watson</span>
            <span class="text-gray-500 text-sm">admin</span>
        </div>
    </header>

    <!-- Sidebar and Main Content Wrapper -->
    <div class="flex flex-col md:flex-row min-h-[75vh] bg-white">
        <!-- Sidebar -->
        <div class="w-full md:w-20 md:ml-8 my-2 md:my-5 md:border md:h-screen bg-[#eae9e7] rounded-none md:rounded-[3rem]">
            <aside class="flex justify-center md:block md:py-4">
                <!-- Sidebar Menu -->
                <nav class="flex md:flex-col justify-around md:justify-between md:h-screen gap-4 md:gap-8">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        <i class="pi pi-home !text-2xl"></i>
                    </NavLink>

                    <NavLink :href="route('addresses.index')" :active="route().current('addresses.*')">
                        <i class="pi pi-map-marker !text-2xl"></i>
                    </NavLink>

                    <NavLink href="#" :active="false">
                        <i class="pi pi-cog !text-2xl"></i>
                    </NavLink>

                    <NavLink href="#" :active="false">
                        <i class="pi pi-users !text-2xl"></i>
                    </NavLink>

                    <NavLink :href="route('logout')" method="post" as="button" :active="false">
                        <i class="pi pi-sign-out !text-2xl transform rotate-180"></i>
                    </NavLink>
                </nav>
            </aside>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-6">
            <slot />
        </main>
    </div>
</template>
