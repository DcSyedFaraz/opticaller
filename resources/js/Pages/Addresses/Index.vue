<template>

    <Head title="Address List" />

    <AuthenticatedLayout>
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Address List</h1>
            <Link :href="route('addresses.create')" class="p-button p-component p-button-contrast" as="button">
            Create New
            </Link>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <DataTable :value="addresses.data" v-model:filters="tableFilters" filterDisplay="row"
                :globalFilterFields="globalFilterFields" :sortField="query.sortField"
                :sortOrder="sortOrderMap[query.sortOrder]" lazy responsiveLayout="scroll" dataKey="id" :paginator="true"
                :rows="addresses.per_page" :totalRecords="addresses.total" @page="onPage" @sort="onSort"
                @filter="onFilter">
                <!-- Global filter bar -->
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="tableFilters.global.value" placeholder="Keyword Search" />
                        </IconField>
                    </div>
                </template>

                <!-- Company Name -->
                <Column field="company_name" header="Company Name" sortable style="min-width:12rem">
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Sub-Project title -->
                <Column header="Sub-Project" filterField="subproject.title" style="min-width:12rem">
                    <template #body="{ data }">{{ data.subproject?.title }}</template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="email_address_system" header="Email System" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="feedback" header="Last Feedback" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="follow_up_date" header="Follow Date" sortable style="min-width:18rem">
                    <template #filter="{ filterModel, filterCallback }">
                        <DatePicker v-model="filterModel.value" @date-select="filterCallback()" dateFormat="yy-mm-dd"
                            showIcon />
                    </template>
                </Column>

                <Column field="deal_id" header="Deal ID" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="contact_id" header="Contact ID" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Closure user name (joined) -->
                <Column header="Closure User" sortable sortField="closure_user_name" filterField="closure_user_name">
                    <template #body="{ data }">{{ data.lastuser?.users?.name }}</template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Action buttons -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex space-x-2">
                            <Button icon="pi pi-eye" class="p-button-text p-button-info"
                                @click="viewAddress(data.id)" />
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger"
                                @click="confirmDelete(data)" />
                        </div>
                    </template>
                </Column>

                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No address found. Try different filters or add a new address.
                    </div>
                </template>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';

export default {
    components: { Link },
    props: {
        addresses: Object,
        filters: Object // comes from backend, used to prime initial state
    },
    data() {
        return {
            globalFilterFields: [
                'company_name',
                'subproject.title',
                'email_address_system',
                'feedback',
                'follow_up_date',
                'deal_id',
                'contact_id',
                'closure_user_name'
            ],
            query: {
                // persisted query-string params (page, sort, etc.)
                search: '',
                sortField: this.filters.sortField || 'id',
                sortOrder: this.filters.sortOrder || 'asc',
                page: this.filters.page || 1
            },
            tableFilters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                company_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'subproject.title': { value: null, matchMode: FilterMatchMode.CONTAINS },
                email_address_system: { value: null, matchMode: FilterMatchMode.CONTAINS },
                feedback: { value: null, matchMode: FilterMatchMode.CONTAINS },
                follow_up_date: { value: null, matchMode: FilterMatchMode.DATE_IS },
                deal_id: { value: null, matchMode: FilterMatchMode.CONTAINS },
                contact_id: { value: null, matchMode: FilterMatchMode.CONTAINS },
                closure_user_name: { value: null, matchMode: FilterMatchMode.CONTAINS }
            },
            sortOrderMap: { asc: 1, desc: -1 }
        };
    },
    methods: {
        /** central loader – called for page / sort / filter changes */
        fetchData(extra = {}) {
            const payload = {
                ...this.query,
                ...extra,
                filters: JSON.stringify(this.tableFilters) // send full filter object
            };
            this.$inertia.get(route('addresses.index'), payload, { preserveState: true, replace: true });
        },
        /* events from DataTable */
        onPage({ page }) {
            this.query.page = page + 1;
            this.fetchData();
        },
        onSort({ sortField, sortOrder }) {
            this.query.sortField = sortField || 'id';
            this.query.sortOrder = sortOrder === 1 ? 'asc' : 'desc';
            this.fetchData();
        },
        onFilter() {
            // whenever any column filter or global changes
            this.query.page = 1; // reset to first page after filtering
            this.fetchData();
        },
        /* CRUD helpers */
        viewAddress(id) {
            this.$inertia.visit(route('addresses.show', id));
        },
        confirmDelete(address) {
            this.$confirm.require({
                message: 'Delete this address?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => this.deleteAddress(address)
            });
        },
        deleteAddress(address) {
            this.$inertia.delete(route('addresses.destroy', address.id), { preserveScroll: true });
        }
    },
    mounted() {
        if (this.$page.props.flash.message) {
            this.$toast.add({ severity: 'success', summary: this.$page.props.flash.message, life: 3000 });
        }
    }
};
</script>
