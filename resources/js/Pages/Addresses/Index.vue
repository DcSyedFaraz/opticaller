<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between ">
                <h1 class="text-2xl font-bold ">Addresses</h1>
                <Link :href="route('addresses.create')" class="p-button p-component" as="button" type="button">Create New</Link>
            </div>
        </template>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <DataTable :value="addresses" responsiveLayout="scroll">
                <Column field="company_name" header="Company Name"></Column>
                <Column field="city" header="City"></Column>
                <Column field="phone_number" header="Phone Number"></Column>
                <Column field="email_address" header="Email Address"></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <NavLink :href="`/addresses/${slotProps.data.id}`" class="text-blue-600 hover:text-blue-900">
                            View</NavLink>
                    </template>
                </Column>
            </DataTable>
            <div class="flex justify-between mt-4">
                <NavLink :href="route('addresses.next')" class="btn btn-primary">Next Address</NavLink>



            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
export default {
    components: { Link },
    props: {
        addresses: {
            type: Array,
            required: true, // If the prop is required
            default: () => [], // If you want to provide a default value
        },
    },
    mounted() {
        console.log(this.$page.props.flash, 'asd');
        if (this.$page.props.flash.message) {
            this.$toast.add({ severity: 'success', summary: this.$page.props.flash.message,  life: 3000 });
            this.$page.props.flash.message = null;
        }
    },
}
</script>
