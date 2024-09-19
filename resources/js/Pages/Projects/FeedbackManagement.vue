<template>

    <Head title="Feedback Management" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-4xl font-extrabold text-gray-900">Manage Feedbacks</h1>
                <!-- Optionally, add a button or link here for additional actions -->
            </div>

            <!-- Success Message -->
            <transition name="fade">
                <div v-if="$page.props.flash.success"
                    class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg shadow-sm">
                    {{ $page.props.flash.success }}
                </div>
            </transition>

            <!-- Sub-Projects Section -->
            <div v-for="subProject in subProjects" :key="subProject.id" class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                        {{ subProject.title }}
                        <span
                            class="ml-3 inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            ID: {{ subProject.id }}
                        </span>
                    </h2>
                </div>


                <!-- Feedback Form Card -->
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <FeedbackForm :submitLabel="'Add Feedback'" :loading="loading[subProject.id]"
                        @submit="createFeedback(subProject.id, $event)" :ref="`feedbackForm-${subProject.id}`" />
                </div>

                <!-- Feedbacks DataTable Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <DataTable :value="subProject.feedbacks" class="w-full" responsiveLayout="scroll"
                        :emptyMessage="'No feedback found.'">
                        <Column field="id" header="ID" class="text-center w-16" />
                        <Column field="label" header="Label" class="text-left" />
                        <Column field="value" header="Value" class="text-left" />
                        <Column header="Actions" class="text-center w-32">
                            <template #body="slotProps">
                                <div class="flex justify-center space-x-2">
                                    <Button icon="pi pi-pencil"
                                        class="p-button-rounded p-button-text p-button-info hover:bg-blue-100 transition-colors"
                                        @click="openEditDialog(slotProps.data, subProject.id)" aria-label="Edit" />
                                    <Button icon="pi pi-trash"
                                        class="p-button-rounded p-button-text p-button-danger hover:bg-red-100 transition-colors"
                                        @click="confirmDelete(slotProps.data)" aria-label="Delete" />
                                </div>
                            </template>
                        </Column>
                        <template #empty>
                            <div class="text-center py-6 text-gray-500">
                                No feedback found.
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>

            <!-- Edit Feedback Dialog -->
            <Dialog v-model:visible="editDialogVisible" :style="{ width: '450px' }" header="Edit Feedback" :modal="true"
                class="p-6">
                <FeedbackForm :initialFeedback="editFeedback" :submitLabel="'Update'" :loading="isUpdating"
                    @submit="updateFeedback" />
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
        subProjects: {
            type: Array, // Ensure subProjects is an array
            required: true,
        },
    },
    data() {
        return {
            editFeedback: {
                id: null,
                label: '',
                value: '',
            },
            editDialogVisible: false,
            isUpdating: false,
            loading: {},
        };
    },

    methods: {
        /**
         * Creates a new feedback for the specified sub-project.
         * @param {Number} subProjectId - The ID of the sub-project.
         * @param {Object} feedback - The feedback data.
         */
        createFeedback(subProjectId, feedback) {
            this.loading[subProjectId] = true;

            const form = {
                label: feedback.label,
                value: feedback.value,
            };
            console.log(form);
            this.$inertia.post(`/subprojects/${subProjectId}/feedbacks`, form, {
                onSuccess: () => {
                    const refName = `feedbackForm-${subProjectId}`;
                    if (this.$refs[refName] && typeof this.$refs[refName][0].resetForm === 'function') {
                        this.$refs[refName][0].resetForm();
                    } else {
                        console.log(this.$refs[refName][0].resetForm, this.$refs[refName]);

                        console.warn(`Ref "${refName}" does not exist or does not have a resetForm method.`);
                    }

                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback added successfully.',
                        life: 3000,
                    });
                    this.loading[subProjectId] = false;
                },
                onError: (errors) => {
                    // Display error messages via Toast
                    Object.values(errors).forEach((error) => {
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Error',
                            detail: error,
                            life: 5000,
                        });
                    });
                    this.loading[subProjectId] = false;
                },
            });
        },

        /**
         * Opens the edit dialog with the selected feedback's data.
         * @param {Object} feedback - The feedback object to edit.
         * @param {Number} subProjectId - The ID of the sub-project.
         */
        openEditDialog(feedback, subProjectId) {
            this.editFeedback = { ...feedback, subProjectId };
            this.editDialogVisible = true;
        },

        /**
         * Updates the selected feedback.
         * @param {Object} updatedFeedback - The updated feedback data.
         */
        updateFeedback(updatedFeedback) {
            this.isUpdating = true;
            this.$inertia.put(route('feedbacks.update', updatedFeedback.id), updatedFeedback, {
                onSuccess: () => {
                    this.editDialogVisible = false;
                    this.isUpdating = false;
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Feedback updated successfully.',
                        life: 3000,
                    });
                    // Optionally, reset the editFeedback object
                    this.editFeedback = {
                        id: null,
                        label: '',
                        value: '',
                    };
                },
                onError: (errors) => {
                    this.isUpdating = false;
                    // Display error messages via Toast
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
                reject: () => {
                    // Optionally handle rejection
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
