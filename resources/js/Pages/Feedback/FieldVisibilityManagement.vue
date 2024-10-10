<template>

    <Head title="Manage Field Visibility" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-4xl font-extrabold text-gray-900">Manage Field Visibility</h1>
            </div>

            <!-- PrimeVue Toast -->

            <!-- Field Visibility Form Card -->
            <!-- <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <FieldVisibilityForm :fields="fields" :subProjects="subProjects" @submit="handleSubmit" />
            </div> -->

            <!-- Field Visibility DataTable Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <DataTable :value="fields" class="w-full" responsiveLayout="scroll" :emptyMessage="'No fields found.'">
                    <!-- <Column field="name" header="Field Name" class="text-center w-32" /> -->
                    <Column field="label" header="Field Label" class="text-left" />
                    <Column header="Hidden in Sub-Projects" class="text-left">
                        <template #body="slotProps">
                            <ul class="list-disc list-inside">
                                <li v-for="subProject in getHiddenSubProjects(slotProps.data.name)"
                                    :key="subProject.id">
                                    {{ subProject.title }}
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column header="Actions" class="text-center w-32">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-info hover:bg-blue-100 transition-colors"
                                @click="openEditDialog(slotProps.data)" aria-label="Edit" />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Edit Field Visibility Dialog -->
            <Dialog v-model:visible="editDialogVisible" :style="{ width: '600px' }" header="Edit Field Visibility"
                :modal="true" class="p-6">
                <FieldVisibilityForm :initialData="editData" :fields="[{
                    name: editData.field_name,
                    label: fields.find(f => f.name === editData.field_name)?.label || editData.field_name
                }]" :subProjects="subProjects" @submit="handleUpdate" :isEdit="true" />
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import FieldVisibilityForm from '../Feedback/FieldVisibilityForm.vue';

export default {
    components: {
        FieldVisibilityForm,
    },
    props: {
        subProjects: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            // Define fields as an array of objects with 'name' and 'label'
            fields: [
                { name: 'company_name', label: 'Company Name' },
                { name: 'salutation', label: 'Salutation' },
                { name: 'first_name', label: 'First Name' },
                { name: 'last_name', label: 'Last Name' },
                { name: 'street_address', label: 'Street Address' },
                { name: 'postal_code', label: 'Postal Code' },
                { name: 'city', label: 'City' },
                { name: 'country', label: 'Country' },
                { name: 'website', label: 'Website' },
                { name: 'phone_number', label: 'Phone Number' },
                { name: 'email_address_new', label: 'Email Address (New)' },
                // Add other fields as needed
            ],
            editDialogVisible: false,
            editData: {
                field_name: '',
                hidden_sub_project_ids: [],
            },
        };
    },
    methods: {
        /**
         * Handles form submission to create a new field visibility.
         * @param {Object} formData - The form data.
         */
        handleSubmit(formData) {
            console.log('Create Form Data:', formData);

            this.$inertia.post(route('field-visibility.store'), formData, {
                onSuccess: () => {
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Field visibility settings updated.',
                        life: 3000,
                    });
                    this.fetchFieldVisibilities();
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
                },
            });
        },

        /**
         * Opens the edit dialog with the selected field's data.
         * @param {Object} field - The field data.
         */
        openEditDialog(field) {
            // Get sub-projects where this field is hidden
            const hiddenSubProjectIds = this.subProjects
                .filter(sp => sp.field_visibilities.some(fv => fv.field_name === field.name && fv.is_hidden))
                .map(sp => sp.id);

            this.editData = {
                field_name: field.name,
                hidden_sub_project_ids: hiddenSubProjectIds,
            };

            this.editDialogVisible = true;
        },

        /**
         * Handles updating field visibility settings.
         * @param {Object} formData - The form data.
         */
        async handleUpdate(formData) {
            try {
                console.log('Update Form Data:', formData);

                // Create a Set for faster lookup of hidden sub_project_ids
                const hiddenSubProjectIds = new Set(formData.hidden_sub_project_ids || []);

                // Array to hold all update/create operations data
                const fieldVisibilityUpdates = this.subProjects.map(subProject => {
                    const isHidden = hiddenSubProjectIds.has(subProject.id);
                    const existingVisibility = subProject.field_visibilities?.find(fv => fv.field_name === formData.field_name);

                    // Prepare the data for updating or creating field visibility records
                    return {
                        sub_project_id: subProject.id,
                        field_name: formData.field_name,
                        is_hidden: isHidden,
                        update_existing: !!existingVisibility
                    };
                });

                console.log('Field Visibility Updates:', fieldVisibilityUpdates);
                console.log('Bulk Update Route:', route('field-visibility.bulkUpdate'));

                await this.$inertia.post(route('field-visibility.bulkUpdate'), { updates: fieldVisibilityUpdates }, {
                    onError: (errors) => {
                        this.errors = errors;
                        Object.keys(errors).forEach(key => {
                            this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key], life: 3000 });
                        });
                        console.log('Errors:', this.errors);
                    },
                    onSuccess: () => {
                        console.log('Update successful');
                        // this.$toast.add({
                        //     severity: 'success',
                        //     summary: 'Success',
                        //     detail: 'Field visibility settings updated.',
                        //     life: 3000,
                        // });
                        this.editDialogVisible = false;
                        this.fetchFieldVisibilities();
                    },
                });

            } catch (error) {
                console.error('Update failed:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Failed to update field visibility settings.',
                    life: 5000,
                });
            }
        },


        /**
         * Retrieves the list of sub-projects where a given field is hidden.
         * @param {String} fieldName - The field name.
         * @returns {Array} - List of sub-projects.
         */
        getHiddenSubProjects(fieldName) {
            return this.subProjects.filter(sp =>
                sp.field_visibilities.some(fv => fv.field_name === fieldName && fv.is_hidden)
            );
        },

        /**
         * Fetches the latest field visibility settings from the backend.
         */
        fetchFieldVisibilities() {
            this.$inertia.get(route('field-visibility.index'), {}, {
                preserveState: true, // Preserves the current state
                onError: () => {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: 'Failed to fetch updated field visibility settings.',
                        life: 5000,
                    });
                },
            });
        },
    },
};
</script>

<style scoped>
/* Add any necessary styles */
</style>
