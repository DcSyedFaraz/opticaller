<template>

    <Head title="Users" />
    <AuthenticatedLayout>
        <div class=" mx-auto ">
            <h1 class="text-2xl font-bold mb-4">User Management</h1>
            <div class="flex mb-4">
                <InputText v-model="filters.search" @input="getUsers" placeholder="Search..." class="mr-2" />
                <Button @click="createUser" label="Create User" icon="pi pi-plus" />
            </div>
            <DataTable :value="users.data" responsiveLayout="scroll"
                @sort="onSort" :sortField="filters.sortField" :sortOrder="filters.sortOrder">
                <Column field="name" header="Name" sortable></Column>
                <Column field="email" header="Email" sortable></Column>
                <Column field="roles" header="Roles">
                    <template #body="slotProps">
                        {{ slotProps.data.roles.map(role => role.name).join(', ') }}
                    </template>
                </Column>
                <Column header="Actions" class="flex">
                    <template #body="slotProps">
                        <Button @click="editUser(slotProps.data.id)" icon="pi pi-pencil" class="p-link mx-2" />
                        <Button @click="deleteUser(slotProps.data.id)" icon="pi pi-trash" severity="danger"
                            class="p-link p-ml-2" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No users found. Please try adjusting your search criteria or add a new user.
                    </div>
                </template>

            </DataTable>
            <ConfirmDialog></ConfirmDialog>
            <Paginator :rows="users.per_page" :totalRecords="users.total" @page="onPage($event)" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {

    data() {
        return {
            users: this.$page.props.users,
            filters: {
                search: '',
                sortField: '',
                sortOrder: null,
                page: 1
            }
        }
    },
    watch: {
        filters: {
            handler: 'getUsers',
            deep: true
        }
    },
    updated() {
        this.users = this.$page.props.users;
    },
    methods: {
        getUsers() {
            this.$inertia.get(route('users.index'), this.filters, {
                preserveState: true,
                replace: true
            })
        },
        onPage(event) {
            this.filters.page = event.page + 1
            this.getUsers()
        },
        onSort(event) {
            this.filters.sortField = event.sortField || null
            this.filters.sortOrder = event.sortOrder !== undefined ? event.sortOrder : null
            this.getUsers()
        },
        createUser() {
            this.$inertia.get(route('users.create'))
        },
        editUser(id) {
            this.$inertia.get(route('users.edit', id))
        },
        deleteUser(id) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this user?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                rejectProps: {
                    label: 'Cancel',
                    severity: 'success',

                },
                acceptProps: {
                    label: 'Yes',
                    severity: 'danger',
                    outlined: true
                },
                accept: () => {
                    Inertia.delete(route('users.destroy', id), {
                        onSuccess: () => {
                            this.$toast.add({ severity: 'success', summary: 'Success', detail: 'User deleted successfully', life: 3000 });
                        }
                    })
                }
            });
        }
    }
}
</script>

<style scoped>
.container {
    max-width: 800px;
    margin: 0 auto;
}
</style>
