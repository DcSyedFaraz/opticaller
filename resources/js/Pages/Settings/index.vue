<template>

    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-6">
            <Card class="bg-white rounded-lg !shadow-md  p-6 !shadow-secondary top">
                <template #header>
                    <p class="text-2xl font-bold text-gray-700 text-center mb-6">Settings</p>
                </template>
                <template #content>
                    <Accordion value="0">
                        <!-- User Management -->
                        <AccordionPanel value="0" class="!border-none">
                            <AccordionHeader>User Management</AccordionHeader>
                            <AccordionContent>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <Card v-for="card in users" :key="card.id"
                                        class="mt-4 m-2 p-4 !shadow-md !shadow-secondary flex flex-col justify-between items-start">
                                        <template #title>
                                            <p class="font-bold">{{ card.title }}</p>
                                        </template>
                                        <template #content>
                                            <div class="my-2 flex-grow">
                                                <p>{{ card.content }}</p>
                                            </div>
                                        </template>
                                        <template #footer>
                                            <div class="mt-auto">
                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem]">
                                                    {{ card.buttonText }}
                                                </Button>
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
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <Card v-for="card in projects_assign" :key="card.id"
                                        class="mt-4 m-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <p class="font-bold">{{ card.title }}</p>
                                        </template>
                                        <template #content class="my-2">
                                            <p>{{ card.content }}</p>
                                            <div class="my-2">
                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-[#383838] !border-[#383838] !rounded  !px-[4rem]">{{
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
                                <div class="grid grid-cols-1 md:grid-cols-2  xl:grid-cols-3 gap-4">
                                    <Card v-for="card in projects" :key="card.id"
                                        class="mt-4 m-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <p class="font-bold">{{ card.title }}</p>
                                        </template>
                                        <template #content class="my-2">
                                            <p>{{ card.content }}</p>
                                        </template>
                                        <template #footer>
                                            <div class="my-2">
                                                <Button v-if="card.id === 5" @click="openFieldLockDialog"
                                                    class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem]">
                                                    {{ card.buttonText }}
                                                </Button>
                                                <Link v-else :href="route(card.routeName)">
                                                <Button class="!bg-[#383838] !border-[#383838] !rounded  !px-[4rem]">{{
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

                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <Card v-for="card in stats" :key="card.id"
                                        class="mt-4 m-2 p-4 !shadow-md !shadow-secondary ">
                                        <template #title>
                                            <p class="font-bold text-wrap">{{ card.title }}</p>
                                        </template>
                                        <template #content class="my-2">
                                            <p>{{ card.content }}</p>
                                            <div class="my-2">

                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-[#383838] !border-[#383838] !rounded  !px-[4rem]">{{
                                                    card.buttonText }}</Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </Card>
                                </div>

                            </AccordionContent>
                        </AccordionPanel>
                        <AccordionPanel value="4" class="!border-none">
                            <AccordionHeader>Security Management</AccordionHeader>
                            <AccordionContent>

                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <Card v-for="card in profile" :key="card.id"
                                        class="mt-4 m-2 p-4 !shadow-md !shadow-secondary">
                                        <template #title>
                                            <p class="font-bold">{{ card.title }}</p>
                                        </template>
                                        <template #content class="my-2">
                                            <p>{{ card.content }}</p>
                                            <div class="my-2">

                                                <Link :href="route(card.routeName)">
                                                <Button class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem]">{{
                                                    card.buttonText }}</Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </Card>
                                </div>

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
                { id: 1, content: 'List of current users with options to edit, deactivate,or delete accounts.', title: 'Manage Existing Users', routeName: 'users.index', buttonText: 'Proceed' },
                { id: 2, content: 'Form to input details like name, email, role, etc.', title: 'Add New User', routeName: 'users.create', buttonText: 'Proceed' },
            ],
            projects_assign: [
                { id: 1, content: 'Dropdown to assign address lists in relation with sub projects to employees.', title: 'Assign Projects', routeName: 'projects.assign', buttonText: 'Proceed' },
            ],
            stats: [
                { id: 1, content: 'Visual charts displaying overall project performance, user activity, and call outcomes.', title: 'Performance Metrics', routeName: 'statistics.index', buttonText: 'Proceed' },
                { id: 2, content: 'Check Addresses webhook status.', title: 'Address Statuses', routeName: 'addresses.statuses.index', buttonText: 'Proceed' },
            ],
            profile: [
                { id: 1, content: 'Option to update security settings, including password requirements.', title: 'Password protection', routeName: 'profile.edit', buttonText: 'Proceed' },
                { id: 2, content: 'Option to update security settings, including account lockout policies.', title: 'Account lockout ', routeName: 'toggleStatus.get', buttonText: 'Proceed' },
                { id: 3, content: 'Option to update security settings, including Field Visibility policies.', title: 'Manage Field Visibility', routeName: 'field-visibility.index', buttonText: 'Proceed' },
                { id: 4, content: 'Option to join active conferences.', title: 'Active Conferences', routeName: 'addresses.conferences', buttonText: 'Proceed' },
                { id: 4, content: 'Option to Transcription.', title: 'Transcription', routeName: 'addresses.transcription', buttonText: 'Proceed' },
            ],
            projects: [
                { id: 1, content: 'Interface to add or modify Projects.', title: 'Project Management', routeName: 'projects.index', buttonText: 'Proceed' },
                { id: 2, content: 'Interface to add or modify Sub Projects.', title: 'Sub Project Management', routeName: 'projects.create', buttonText: 'Proceed' },
                { id: 3, content: 'Add or modify addresses', title: 'Address List', routeName: 'addresses.index', buttonText: 'Proceed' },
                { id: 3, content: 'Add or modify feedback options', title: 'Feedback Customization', routeName: 'feedbacks.index', buttonText: 'Proceed' },
                { id: 4, content: 'Interface to add or modify Addresses.', title: 'Add New Address', routeName: 'addresses.create', buttonText: 'Proceed' },
                { id: 5, content: 'Option to lock specific fields to prevent user edits.', title: 'Fields Locking', routeName: '', buttonText: 'Proceed' },
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

<style>
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

.p-accordionheader {
    --tw-bg-opacity: 1 !important;
    background-color: rgb(235 241 239 / var(--tw-bg-opacity)) !important;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

@media only screen and (max-width: 768px) {

    #app div.p-card-body,
    #app .p-accordioncontent-content {
        padding: 0;
    }
}

.p-card {
    display: flex !important;
    align-items: stretch !important;
    justify-content: flex-start !important;
}

.p-card-body {
    height: 100% !important;
    display: grid !important;
    gap: 0 !important;
}

.p-card-footer {
    align-self: end !important;
}
</style>
