<template>

    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-4">User Management</h1>
            <Button @click="createUser" label="Create User" icon="pi pi-plus" class="mb-4" />

            <DataTable :value="users" responsiveLayout="scroll" :paginator="true" :rows="10">
                <Column field="name" header="Name"></Column>
                <Column field="email" header="Email"></Column>
                <Column field="roles" header="Roles">
                    <template #body="slotProps">
                        {{ slotProps.data.roles.map(role => role.name).join(', ') }}
                    </template>
                </Column>
                <Column header="Actions" class="flex">
                    <template #body="slotProps">
                        <Button @click="editUser(slotProps.data.id)" icon="pi pi-pencil" class="p-link mx-2" />
                        <Button @click="deleteUser(slotProps.data.id)" icon="pi pi-trash" severity="danger" class="p-link p-ml-2" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { inject } from 'vue'

export default {
    data() {
        return {
            users: this.$page.props.users
        }
    },
    methods: {
        createUser() {
            this.$inertia.get(route('users.create'))
        },
        editUser(id) {
            this.$inertia.get(route('users.edit', id))
        },
        deleteUser(id) {
            console.log(this.$confirm);
            this.$confirm.require({
                message: 'Are you sure you want to delete this user?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.delete(route('users.destroy', id), {
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
