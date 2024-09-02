<template>

    <Head title="Statistics" />
    <AuthenticatedLayout>

        <div class=" mx-auto p-6 bg-white rounded-lg shadow-md shadow-secondary">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Callback</h2>
            <div class="p-4 border rounded-md shadow-sm shadow-secondary bg-gray-50">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Select Project Select -->
                    <div class="flex flex-col">
                        <label for="project" class="text-sm font-bold text-gray-700 mb-1">Select Project <span
                                class="text-red-600 font-bold">*</span></label>
                        <Select id="project" v-model="formData.project" :options="projects" optionLabel="name"
                            optionValue="email" placeholder="--Select--" required />
                    </div>

                    <!-- Salutation Input -->
                    <div class="flex flex-col">
                        <label for="salutation" class="text-sm font-bold text-gray-700 mb-1">Salutation <span
                                class="text-red-600 font-bold">*</span></label>
                        <InputText id="salutation" v-model="formData.salutation" placeholder="Salutation" required
                            class="p-inputtext-sm" />
                    </div>

                    <!-- First Name Input -->
                    <div class="flex flex-col">
                        <label for="firstName" class="text-sm font-bold text-gray-700 mb-1">First Name <span
                                class="text-red-600 font-bold">*</span></label>
                        <InputText id="firstName" v-model="formData.firstName" placeholder="First Name" required
                            class="p-inputtext-sm" />
                    </div>

                    <!-- Last Name Input -->
                    <div class="flex flex-col">
                        <label for="lastName" class="text-sm font-bold text-gray-700 mb-1">Last Name <span
                                class="text-red-600 font-bold">*</span></label>
                        <InputText id="lastName" v-model="formData.lastName" placeholder="Last Name" required
                            class="p-inputtext-sm" />
                    </div>

                    <!-- Phone Number Input -->
                    <div class="flex flex-col">
                        <label for="phoneNumber" class="text-sm font-bold text-gray-700 mb-1">Phone Number <span
                                class="text-red-600 font-bold">*</span></label>
                        <InputText id="phoneNumber" v-model="formData.phoneNumber" placeholder="+42" required
                            class="p-inputtext-sm" />
                    </div>

                    <!-- Notes Textarea -->
                    <div class="flex flex-col">
                        <label for="notes" class="text-sm font-bold text-gray-700 mb-1">Notes: <span
                                class="text-red-600 font-bold">*</span></label>
                        <Textarea id="notes" v-model="formData.notes" placeholder="Text here" rows="3" required
                            class="p-inputtext-sm" />
                    </div>

                    <!-- Save and Cancel Buttons -->
                </div>
            </div>
            <div class="flex justify-center mt-4">
                <Button label="Save" icon="pi pi-save"
                    class="!bg-[#77A697] !border-[#77A697] text-white !px-[4rem] !rounded mr-2" @click="saveForm" />
                <Button label="Cancel" severity="contrast" class="!px-[4rem] !rounded" @click="cancelForm" />
            </div>
        </div>
        <div v-if="isLoading" class="loading-overlay">
            <ProgressSpinner style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" />
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    data() {
        return {
            isLoading: false,
            // Form data
            formData: {
                project: '',
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',

                notes: '',
            },

            // Options for the Select Project
            projects: [
                { name: 'vimtronix', email: 'info@vimtronix.com' },
                { name: 'XSimpress', email: 'info@xsimpress.com' },
                { name: 'box4pflege.de', email: 'info@box4pflege.de' },
                { name: 'Management', email: 'dcsyedfaraz@gmail.com' },
            ],
        };
    },
    methods: {
        saveForm() {
            const missingFields = [];

            if (!this.formData.project) missingFields.push('Project');
            if (!this.formData.salutation) missingFields.push('Salutation');
            if (!this.formData.firstName) missingFields.push('First Name');
            if (!this.formData.lastName) missingFields.push('Last Name');
            if (!this.formData.phoneNumber) missingFields.push('Phone Number');
            // if (!this.formData.email) missingFields.push('Email');
            // if (!this.formData.dob) missingFields.push('Date of Birth');
            if (!this.formData.notes) missingFields.push('Notes');

            // If there are missing fields, show a warning toast
            if (missingFields.length > 0) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Incomplete Form',
                    detail: `Please fill the following fields: ${missingFields.join(', ')}`,
                    life: 4000,
                });
                return;
            }

            this.isLoading = true,
                // If validation passes, send data with Inertia
                this.$inertia.post(route('callback.post'), this.formData, {
                    onSuccess: () => {
                        this.isLoading = false,
                        this.$inertia.visit(route('dash'));
                            this.$toast.add({
                                severity: 'success',
                                summary: 'Success',
                                detail: 'Form submitted successfully!',
                                life: 3000,
                            });
                    },
                    onError: (errors) => {
                        this.isLoading = false,
                            this.$toast.add({
                                severity: 'error',
                                summary: 'Error',
                                detail: 'Form submission failed. Please check your input.',
                                life: 3000,
                            });
                    },
                });
            // this.isLoading = false;
        },

        cancelForm() {
            this.formData = {
                project: '',
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',
                email: '',
                dob: '',
                notes: '',
            };
        },

    },
};
</script>


<style scoped></style>
