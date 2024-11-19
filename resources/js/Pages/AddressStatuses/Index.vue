<template>

    <Head title="Address Statuses" />
    <AuthenticatedLayout>
        <div class="p-m-4">
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-bold">Address Statuses</h1>
                <!-- Optional: Add a button to create new status if needed -->
                <!-- <Link :href="route('address-statuses.create')" class="p-button p-component p-button-contrast">
                    Create New
                </Link> -->
            </div>

            <!-- Optional: Add search input if implementing search functionality -->
            <!--
            <InputText v-model="filters.search" placeholder="Search..." class="mb-4" @input="fetchData" />
            -->

            <DataTable :value="addressStatuses.data" responsiveLayout="scroll" :sortField="filters.sortField"
                :sortOrder="sortOrderMap[filters.sortOrder]" @sort="onSort"
                class="p-datatable-striped p-datatable-bordered">
                <Column field="id" header="ID" sortable></Column>
                <Column field="address_id" header="Address ID" sortable></Column>
                <Column field="status" header="Status" sortable></Column>
                <Column field="created_at" header="Created At" sortable>
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
                <Column field="updated_at" header="Updated At" sortable>
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.updated_at) }}
                    </template>
                </Column>
                <!-- Optional: Add Actions Column if needed -->
                <!--
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex space-x-2">
                            <Button icon="pi pi-pencil" class="p-button-text p-button-warning" @click="editStatus(slotProps.data.id)" tooltip="Edit" tooltipOptions="{ position: 'top' }" />
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="confirmDelete(slotProps.data)" tooltip="Delete" tooltipOptions="{ position: 'top' }" />
                        </div>
                    </template>
                </Column>
                -->
                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No address statuses found. Please try adjusting your search criteria or add a new status.
                    </div>
                </template>
            </DataTable>

            <Paginator :rows="addressStatuses.per_page" :totalRecords="addressStatuses.total"
                :first="(addressStatuses.current_page - 1) * addressStatuses.per_page" @page="onPageChange"
                class="mt-4" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { format } from 'date-fns';

export default {
    name: 'AddressStatusesIndex',

    props: {
        addressStatuses: {
            type: Object,
            required: true,
            default: () => ({
                data: [],
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
            }),
        },
        filters: {
            type: Object,
            required: false,
            default: () => ({
                search: '',
                sortField: 'id',
                sortOrder: 'asc',
                page: 1,
            }),
        },
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
        formatDate(date) {
            return date ? format(new Date(date), 'PPpp') : '';
        },
        onPageChange(event) {
            // Update the current page in filters
            this.filters.page = (event.page) + 1; // PrimeVue paginator is zero-based

            this.fetchData();
        },
        onSort(event) {
            this.filters.sortField = event.sortField || 'id'; // Ensure default sortField
            this.filters.sortOrder = event.sortOrder === 1 ? 'asc' : 'desc'; // Map sort order to "asc" or "desc"
            this.fetchData();
        },
        fetchData() {
            this.$inertia.get(route('addresses.statuses.index'), this.filters, { preserveState: true, replace: true });
        },
        // Optional: Implement actions like edit or delete
        /*
        editStatus(id) {
            this.$inertia.visit(route('address-statuses.edit', id));
        },
        deleteStatus(status) {
            this.$inertia.delete(route('address-statuses.destroy', status.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$toast.add({ severity: 'success', summary: 'Deleted', detail: 'Status deleted successfully', life: 3000 });
                },
                onError: () => {
                    this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete status', life: 3000 });
                }
            });
        },
        confirmDelete(status) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this status?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Yes',
                rejectLabel: 'No',
                accept: () => {
                    this.deleteStatus(status);
                },
            });
        },
        */
    },
    mounted() {
        // Handle flash messages if any
        if (this.$page.props.flash.message) {
            this.$toast.add({ severity: 'success', summary: 'Success', detail: this.$page.props.flash.message, life: 3000 });
        }
    }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
