<template>

    <Head title="Account lockout" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <h2 class="text-2xl font-semibold mb-4 text-primary">Account lockout</h2>
            <DataTable :value="users" class="!p-datatable-striped" v-model:filters="filters"
                :globalFilterFields="['name', 'email']" :paginator="true" :rows="10" responsiveLayout="scroll">
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters.global.value" placeholder="Keyword Search" />
                        </IconField>
                    </div>
                </template>
                <template #empty>
                    <span class="text-center">
                        <i class="pi pi-info-circle" />
                        <span>No users found.</span>
                    </span>
                </template>

                <Column field="name" header="Name" />
                <Column field="email" header="Email" />

                <!-- New Activity Details Column -->
                <Column header="Activity Details">
                    <template #body="slotProps">
                        <div class="flex flex-col">
                            <!-- Active Status -->
                            <!-- <span :class="slotProps.data.is_active ? 'text-success' : 'text-secondary'">
                                <Badge v-if="slotProps.data.is_active" value="Active" size="large" severity="success">
                                </Badge>
                                <Badge v-else value="Inactive" size="large" severity="danger"></Badge>
                            </span> -->
                            <!-- Last Login -->
                            <span class="text-sm text-gray-600">
                                Last Login: {{ slotProps.data.latest_login_time?.login_time ?? 'N/A' }}
                            </span>
                            <!-- Last Logout -->
                            <span class="text-sm text-gray-600">
                                Last Logout: {{ slotProps.data.latest_logout_time?.logout_time ?? 'N/A' }}
                            </span>
                        </div>
                    </template>
                </Column>

                <Column header="Status">
                    <template #body="slotProps">
                        <span :class="slotProps.data.is_active ? 'text-success' : 'text-secondary'">
                            <Badge v-if="slotProps.data.is_active" value="Active" size="large" severity="success">
                            </Badge>
                            <Badge v-else value="Inactive" size="large" severity="danger"></Badge>
                        </span>
                    </template>
                </Column>
                <Column header="Action">
                    <template #body="slotProps">
                        <Button :label="slotProps.data.is_active ? 'Deactivate' : 'Activate'" :class="`
                                ${slotProps.data.is_active
                                ? '!bg-red-500 hover:!bg-red-700 !border-red-500 hover:!border-red-700 text-white'
                                : '!bg-green-500 hover:!bg-green-700 !border-green-500 hover:!border-green-700 text-white'
                            }
                            `" @click="openConfirmationDialog(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

            <!-- Confirmation Dialog for Toggling User Status -->
            <Dialog v-model:visible="confirmDialogVisible" header="Confirmation" :closable="false"
                :style="{ width: '350px' }">
                <p class="text-secondary">Are you sure you want to change the status of this user?</p>
                <template #footer>
                    <Button label="Cancel" icon="pi pi-times"
                        class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem] text-secondary"
                        @click="confirmDialogVisible = false" />
                    <Button label="Yes" icon="pi pi-check"
                        class="!bg-primary !border-primary !rounded !px-[4rem] text-primary"
                        @click="toggleUserStatus" />
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { FilterMatchMode } from '@primevue/core/api';

export default {
    props: {
        users: Array,
    },
    data() {
        return {
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
            selectedUser: null,
            confirmDialogVisible: false,
        };
    },
    mounted() {
        console.log(this.users);
    },
    methods: {
        openConfirmationDialog(user) {
            this.selectedUser = user;
            this.confirmDialogVisible = true;
        },
        toggleUserStatus() {
            if (this.selectedUser) {
                this.$inertia.post(route('toggleStatus'), { id: this.selectedUser.id });
                this.confirmDialogVisible = false;
            }
        },
        formatDate(date) {
            if (!date) return 'N/A';
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            };
            return new Date(date).toLocaleString(undefined, options);
        },
    },
};
</script>

<style scoped>
.p-datatable .p-datatable-thead>tr>th {
    text-align: left;
}

.flex-col {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
</style>
