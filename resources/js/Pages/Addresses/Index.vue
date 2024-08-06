<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold">Addresses</h1>
                <Link :href="route('addresses.create')" class="p-button p-component" as="button" type="button">Create
                New</Link>
            </div>
        </template>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <InputText v-model="filters.search" placeholder="Search..." class="mb-4" @input="fetchData" />
            <DataTable :value="addresses.data" responsiveLayout="scroll" :sortField="filters.sortField"
                :sortOrder="sortOrderMap[filters.sortOrder]" @sort="onSort">
                <Column field="company_name" header="Company Name" sortable></Column>
                <Column field="city" header="City" sortable></Column>
                <Column field="phone_number" header="Phone Number" sortable></Column>
                <Column field="email_address_system" header="Email Address System" sortable></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <NavLink :href="`/addresses/${slotProps.data.id}`" class="!text-blue-600 hover:!text-blue-900">
                            View</NavLink>
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No address found. Please try adjusting your search criteria or add a new address.
                    </div>
                </template>
            </DataTable>
            <Paginator :rows="addresses.per_page" :totalRecords="addresses.total" @page="onPageChange($event)" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: { Link },
    props: {
        addresses: {
            type: Object,
            required: true,
            default: () => ({ data: [], total: 0, per_page: 10 })
        },
        filters: {
            type: Object,
            required: true,
            default: () => ({ search: '', sortField: 'id', sortOrder: 'asc', page: 1 })
        }
    },
    data() {
        return {
            sortOrderMap: {
                asc: 1,
                desc: -1
            }
        };
    },
    methods: {
        fetchData() {
            // Ensure default sortField and sortOrder are included
            if (!this.filters.sortField) this.filters.sortField = 'id';
            if (!this.filters.sortOrder) this.filters.sortOrder = 'asc';

            this.$inertia.get(route('addresses.index'), this.filters, { preserveState: true });
        },
        onSort(event) {
            this.filters.sortField = event.sortField || 'id'; // Ensure default sortField
            this.filters.sortOrder = event.sortOrder === 1 ? 'asc' : 'desc'; // Map sort order to "asc" or "desc"
            this.fetchData();
        },
        onPageChange(event) {
            this.filters.page = event.page + 1;
            this.fetchData();
        }
    },
    mounted() {
        console.log(this.$page.props);
        if (this.$page.props.flash.message) {
            this.$toast.add({ severity: 'success', summary: this.$page.props.flash.message, life: 3000 });
            this.$page.props.flash.message = null;
        }
    }
}
</script>
