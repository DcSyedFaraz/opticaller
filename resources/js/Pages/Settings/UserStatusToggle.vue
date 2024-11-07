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

                <!-- Activity Details Column -->
                <Column header="Activity Details">
                    <template #body="slotProps">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">
                                Last Login: {{ slotProps.data.latest_login_time?.login_time ?? 'N/A' }}
                            </span>
                            <span class="text-sm text-gray-600">
                                Last Logout: {{ slotProps.data.latest_logout_time?.logout_time ?? 'N/A' }}
                            </span>
                        </div>
                    </template>
                </Column>

                <!-- Status Column: Online/Offline -->
                <Column header="Status">
                    <template #body="slotProps">
                        <span :class="slotProps.data.is_active ? 'text-success' : 'text-secondary'">
                            <Badge v-if="slotProps.data.is_active" value="Online" size="large" severity="success">
                            </Badge>
                            <Badge v-else value="Offline" size="large" severity="danger"></Badge>
                        </span>
                    </template>
                </Column>

                <!-- Action Column for Activate/Deactivate -->
                <Column header="Action">
                    <template #body="slotProps">
                        <Button :label="slotProps.data.is_active ? 'Deactivate' : 'Activate'"
                            :class="slotProps.data.is_active ? 'bg-red-500 hover:bg-red-700' : 'bg-green-500 hover:bg-green-700'"
                            @click="openConfirmationDialog(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

            <!-- Confirmation Dialog for User Status -->
            <Dialog v-model:visible="confirmDialogVisible" header="Confirmation" :closable="false"
                :style="{ width: '350px' }">
                <p class="text-secondary">Are you sure you want to change the status of this user?</p>
                <template #footer>
                    <Button label="Cancel" icon="pi pi-times" class="!bg-gray-500"
                        @click="confirmDialogVisible = false" />
                    <Button label="Yes" icon="pi pi-check" class="!bg-primary" @click="toggleUserStatus" />
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
            confirmDialogVisible: false,
            selectedUser: null,
        };
    },
    mounted() {
        this.initializeWebSocket();
    },
    methods: {
        initializeWebSocket() {
            // console.log(window.Echo);
            // // Listen for real-time changes in user status (online/offline)
            // window.Echo.channel('online-users')
            //     .listen('UserStatusChanged', (event) => {
            //         this.updateUserStatus(event.user_id, event.status);
            //     });
        },
        updateUserStatus(userId, status) {
            const user = this.users.find(u => u.id === userId);
            if (user) {
                user.status = (status === 'online');
                console.log(user);

            }
        },
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
