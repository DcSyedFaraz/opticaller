<template>

    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-6">
            <Card
                class="bg-white rounded-lg !shadow-md  p-6 !shadow-secondary top">
                <template #header>
                    <h2 class="text-2xl font-bold text-gray-700 text-center mb-6">Settings</h2>
                </template>
                <template #content>
                    <Accordion value="0">
                        <!-- User Management -->
                        <AccordionPanel value="0" class="!border-none">
                            <AccordionHeader>User Management</AccordionHeader>
                            <AccordionContent>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <Card v-for="card in users" :key="card.id"
                                        class="my-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <h2 class="font-bold">{{ card.title }}</h2>
                                        </template>
                                        <template #content class="my-2">
                                            <div class="my-2">
                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-secondary !border-secondary" size="small">{{
                                        card.buttonText }}</Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </Card>
                                </div>
                            </AccordionContent>
                        </AccordionPanel>

                        <!-- Assign Projects -->
                        <AccordionPanel value="1" class="!border-none">
                            <AccordionHeader>Assign Projects</AccordionHeader>
                            <AccordionContent>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <Card v-for="card in projects_assign" :key="card.id"
                                        class="my-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <h2 class="font-bold">{{ card.title }}</h2>
                                        </template>
                                        <template #content class="my-2">
                                            <div class="my-2">
                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-secondary !border-secondary" size="small">{{
                                        card.buttonText }}</Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </Card>
                                </div>
                            </AccordionContent>
                        </AccordionPanel>

                        <!-- Project Settings -->
                        <AccordionPanel value="2" class="!border-none">
                            <AccordionHeader>Project Settings</AccordionHeader>
                            <AccordionContent>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <Card v-for="card in projects" :key="card.id"
                                        class="my-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <h2 class="font-bold">{{ card.title }}</h2>
                                        </template>
                                        <template #content class="my-2">
                                            <div class="my-2">
                                                <Button v-if="card.id === 5" @click="openFieldLockDialog"
                                                    class="!bg-secondary !border-secondary" size="small">
                                                    {{ card.buttonText }}
                                                </Button>
                                                <Link v-else :href="route(card.routeName)">
                                                <Button class="!bg-secondary !border-secondary" size="small">{{
                                        card.buttonText }}</Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </Card>
                                </div>
                            </AccordionContent>
                        </AccordionPanel>

                        <!-- Other Panels -->
                        <AccordionPanel value="3" class="!border-none">
                            <AccordionHeader>Statistics & Reporting</AccordionHeader>
                            <AccordionContent>
                                <p class="text-gray-600">Content for Statistics & Reporting</p>
                            </AccordionContent>
                        </AccordionPanel>
                        <AccordionPanel value="4" class="!border-none">
                            <AccordionHeader>Security Management</AccordionHeader>
                            <AccordionContent>
                                <p class="text-gray-600">Content for Security Management</p>
                            </AccordionContent>
                        </AccordionPanel>
                        <AccordionPanel value="5" class="!border-none">
                            <AccordionHeader>Successful Synced On</AccordionHeader>
                            <AccordionContent>
                                <p class="text-gray-600">Content for Successful Synced On</p>
                            </AccordionContent>
                        </AccordionPanel>
                    </Accordion>
                </template>
            </Card>

            <!-- Field Lock Dialog -->
            <FieldLockDialog :dialogVisible="fieldLockDialogVisible" :initialLockedFields="lockfields"
                @save="updateLockedFields" @update:dialogVisible="fieldLockDialogVisible = $event" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
import FieldLockDialog from './FieldLockDialog.vue';

export default {
    props: {
        lockfields: Array,
    },
    components: {
        FieldLockDialog,
    },
    data() {
        return {
            users: [
                { id: 1, title: 'Users Index', routeName: 'users.index', buttonText: 'View Details' },
                { id: 2, title: 'User Create', routeName: 'users.create', buttonText: 'View Details' },
            ],
            projects_assign: [
                { id: 1, title: 'Assign Users', routeName: 'projects.assign', buttonText: 'View Details' },
            ],
            projects: [
                { id: 1, title: 'Projects Index/Create', routeName: 'projects.index', buttonText: 'View Details' },
                { id: 2, title: 'Sub Project Index/Create', routeName: 'projects.create', buttonText: 'View Details' },
                { id: 3, title: 'Addresses Index', routeName: 'addresses.index', buttonText: 'View Details' },
                { id: 4, title: 'Addresses Create', routeName: 'addresses.create', buttonText: 'View Details' },
                { id: 5, title: 'Fields Lock', routeName: '', buttonText: 'View Details' },
            ],
            fieldLockDialogVisible: false,
            lockedFields: [], // Track locked fields
        };
    },
    methods: {
        openFieldLockDialog() {
            this.fieldLockDialogVisible = true;
        },
        updateLockedFields(lockedFields) {
            this.lockedFields = lockedFields;
            console.log('Locked Fields:', this.lockedFields);
            try {
                this.$inertia.post(route('global-locked-fields.update'), {
                    locked_fields: this.lockedFields,
                });

                this.$toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Locked fields updated successfully.',
                    life: 3000,
                });
            } catch (error) {
                // Handle errors (e.g., show an error message)
                console.error('Error saving locked fields:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Failed to update locked fields.',
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.p-card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.p-card h3 {
    margin-bottom: 0;
}

.p-accordionheader,
.p-accordioncontent-content {
    --tw-bg-opacity: 1 !important;
    background-color: rgb(235 241 239 / var(--tw-bg-opacity)) !important;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
</style>
