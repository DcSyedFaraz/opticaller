<template>

    <Head title="Address List" />
    <AuthenticatedLayout>

        <div class="flex justify-between">
            <h1 class="text-2xl font-bold">Address List</h1>
            <Link :href="route('addresses.create')" class="p-button p-component p-button-contrast " as="button"
                type="button">Create New</Link>
        </div>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 text-nowrap">
            <InputText v-model="filters.search" placeholder="Search..." class="mb-4" @input="fetchData" />
            <DataTable :value="addresses.data" responsiveLayout="scroll" :sortField="filters.sortField"
                :sortOrder="sortOrderMap[filters.sortOrder]" @sort="onSort">
                <Column field="company_name" header="Company Name" sortable></Column>
                <Column header="Sub-Project">
                    <template #body="slotProps">
                        {{ slotProps.data.subproject?.title }}
                    </template>
                </Column>
                <Column field="email_address_system" header="Email System" sortable></Column>
                <Column field="feedback" header="Last Feedback" sortable></Column>
                <Column field="follow_up_date" header="Follow Date" sortable></Column>
                <Column field="deal_id" header="Deal ID" sortable></Column>
                <Column field="contact_id" header="Contact ID" sortable></Column>
                <Column header="Closure User" sortable sortField="closure_user_name">
                    <template #body="slotProps">
                        {{ slotProps.data.lastuser?.users?.name }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex space-x-2">
                            <!-- View Button -->
                            <Button icon="pi pi-eye" class="p-button-text p-button-info"
                                @click="viewAddress(slotProps.data.id)" :tooltip="'View Address'"
                                tooltipOptions="{ position: 'top' }" />
                            <!-- Delete Button -->
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger"
                                @click="confirmDelete(slotProps.data)" :tooltip="'Delete Address'"
                                tooltipOptions="{ position: 'top' }" />
                        </div>
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
        deleteAddress(address) {
            this.$inertia.delete(route('addresses.destroy', address.id), {
                preserveScroll: true,
                onSuccess: () => {
                    // this.$toast.add({ severity: 'success', summary: 'Deleted', detail: 'Address deleted successfully', life: 3000 });
                },
                onError: () => {
                    this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete address', life: 3000 });
                }
            });
        },
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
        },
        confirmDelete(address) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this address?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Yes',
                rejectLabel: 'No',
                accept: () => {
                    this.deleteAddress(address);
                },
            });
        },
        viewAddress(id) {
            // Navigate to the address detail page
            this.$inertia.visit(route('addresses.show', id));
        },
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
