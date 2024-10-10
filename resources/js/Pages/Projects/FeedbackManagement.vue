<template>

    <Head title="Feedback Management" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-4xl font-extrabold text-gray-900">Manage Feedbacks</h1>
            </div>


            <!-- Feedback Form Card -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <FeedbackForm :submitLabel="'Add Feedback'" :loading="loading" @submit="createFeedback"
                    :ref="'feedbackForm'" :subProjects="subProjects" />
            </div>

            <!-- Feedbacks DataTable Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <DataTable :value="localFeedbacks" class="w-full" responsiveLayout="scroll" rowReorder
                    @rowReorder="onRowReorder" :emptyMessage="'No feedback found.'">
                    <Column rowReorder headerStyle="width: 3rem" :reorderableColumn="false" class="text-center" />

                    <Column field="id" header="ID" class="text-center w-16" />
                    <Column field="label" header="Label" class="text-left" />
                    <Column field="value" header="Value" class="text-left" />
                    <Column field="no_validation" header="No Validation" class="text-center w-24">
                        <template #body="slotProps">
                            <Checkbox v-model="slotProps.data.no_validation" @change="toggleValidation(slotProps.data)"
                                binary />
                        </template>
                    </Column>
                    <Column header="Sub-Projects" class="text-left">
                        <template #body="slotProps">
                            <ul>
                                <li v-for="subProject in slotProps.data.sub_projects" :key="subProject.id">
                                    {{ subProject.title }}
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column header="Actions" class="text-center w-32">
                        <template #body="slotProps">
                            <div class="flex justify-center space-x-2">
                                <Button icon="pi pi-pencil"
                                    class="p-button-rounded p-button-text p-button-info hover:bg-blue-100 transition-colors"
                                    @click="openEditDialog(slotProps.data)" aria-label="Edit" />
                                <Button icon="pi pi-trash"
                                    class="p-button-rounded p-button-text p-button-danger hover:bg-red-100 transition-colors"
                                    @click="confirmDelete(slotProps.data)" aria-label="Delete" />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-6 text-gray-500">No feedback found.</div>
                    </template>
                </DataTable>
            </div>

            <!-- Edit Feedback Dialog -->
            <Dialog v-model:visible="editDialogVisible" :style="{ width: '450px' }" header="Edit Feedback" :modal="true"
                class="p-6">
                <FeedbackForm :initialFeedback="editFeedback" :submitLabel="'Update'" :loading="isUpdating"
                    @submit="updateFeedback" :subProjects="subProjects" />
            </Dialog>

            <!-- PrimeVue Toast -->
            <Toast position="top-right" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
import FeedbackForm from './FeedbackForm.vue';

export default {
    components: {
        FeedbackForm,
    },
    props: {
        feedbacks: {
            type: Array, // List of all feedbacks with their associated subprojects
            required: true,
        },
        subProjects: {
            type: Array, // List of all subprojects
            required: true,
        },
    },
    data() {
        return {
            localFeedbacks: [...this.feedbacks],
            editFeedback: {
                id: null,
                label: '',
                value: '',
                no_validation: false,
                sub_project_ids: [],
            },
            editDialogVisible: false,
            isUpdating: false,
            loading: false,
        };
    },
    watch: {
        feedbacks(newFeedbacks) {
            this.localFeedbacks = [...newFeedbacks];
        }
    },

    methods: {
        /**
         * Creates a new feedback and assigns it to selected subprojects.
         * @param {Object} feedback - The feedback data.
         */
        createFeedback(feedback) {
            this.loading = true;

            const form = {
                label: feedback.label,
                value: feedback.value,
                no_validation: feedback.no_validation,
                no_statistics: feedback.no_statistics,
                sub_project_ids: feedback.sub_project_ids,
            };

            this.$inertia.post(route('feedbacks.store'), form, {
                onSuccess: () => {
                    if (this.$refs.feedbackForm && typeof this.$refs.feedbackForm.resetForm === 'function') {
                        this.$refs.feedbackForm.resetForm();
                    }

                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback added successfully.',
                        life: 3000,
                    });
                    this.loading = false;
                },
                onError: (errors) => {
                    Object.values(errors).forEach((error) => {
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Error',
                            detail: error,
                            life: 5000,
                        });
                    });
                    this.loading = false;
                },
            });
        },

        /**
         * Opens the edit dialog with the selected feedback's data.
         * @param {Object} feedback - The feedback object to edit.
         */
        openEditDialog(feedback) {
            this.editFeedback = {
                id: feedback.id,
                label: feedback.label,
                value: feedback.value,
                no_statistics: feedback.no_statistics,
                no_validation: feedback.no_validation,
                sub_project_ids: feedback.sub_projects.map((sp) => sp.id),
            };
            this.editDialogVisible = true;
        },
        onRowReorder(event) {
            this.localFeedbacks = event.value;

            // Extract the reordered IDs
            const reorderedIds = this.localFeedbacks.map(feedback => feedback.id);

            this.$inertia.post(route('feedbacks.reorder'), {
                orderedIds: reorderedIds
            }, {
                preserveScroll: true,
                onSuccess: (page) => {
                    console.log(page);

                },
                onError: () => {
                    toast.value.add({ severity: 'error', summary: 'Error', detail: 'Failed to reorder feedbacks.', life: 3000 });
                }
            });

        },

        /**
         * Updates the selected feedback and its associated subprojects.
         * @param {Object} updatedFeedback - The updated feedback data.
         */
        updateFeedback(updatedFeedback) {
            this.isUpdating = true;

            const form = {
                label: updatedFeedback.label,
                value: updatedFeedback.value,
                no_validation: updatedFeedback.no_validation,
                no_statistics: updatedFeedback.no_statistics,
                sub_project_ids: updatedFeedback.sub_project_ids,
            };

            this.$inertia.put(route('feedbacks.update', this.editFeedback.id), form, {
                onSuccess: () => {
                    this.editDialogVisible = false;
                    this.isUpdating = false;
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback updated successfully.',
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    this.isUpdating = false;
                    Object.values(errors).forEach((error) => {
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Error',
                            detail: error,
                            life: 5000,
                        });
                    });
                },
            });
        },

        /**
         * Confirms and deletes the selected feedback.
         * @param {Object} feedback - The feedback object to delete.
         */
        confirmDelete(feedback) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this feedback?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Yes',
                rejectLabel: 'No',
                accept: () => {
                    this.deleteFeedback(feedback);
                },
            });
        },

        /**
         * Deletes the selected feedback after confirmation.
         * @param {Object} feedback - The feedback object to delete.
         */
        deleteFeedback(feedback) {
            this.$inertia.delete(route('feedbacks.destroy', feedback.id), {
                onSuccess: () => {
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback deleted successfully.',
                        life: 3000,
                    });
                },
                onError: () => {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: 'Failed to delete feedback.',
                        life: 3000,
                    });
                },
            });
        },
        toggleValidation(feedback) {
            const updatedFeedback = {
                ...feedback,
                no_validation: feedback.no_validation,
            };

            this.$inertia.put(route('feedbacks.validation', feedback.id), {
                no_validation: updatedFeedback.no_validation,
            }, {
                onSuccess: () => {
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback validation status updated.',
                        life: 3000,
                    });
                },
                onError: () => {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: 'Failed to update validation status.',
                        life: 3000,
                    });
                    // Revert the checkbox if the update fails
                    feedback.no_validation = !feedback.no_validation;
                },
            });
        },
    },
};
</script>

<style scoped>
/* Fade transition for success message */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Optional: Add custom styles if needed */
</style>
