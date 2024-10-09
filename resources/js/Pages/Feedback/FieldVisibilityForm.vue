<template>
    <form >
        <!-- Field Selection (Only for Create) -->
        <div v-if="!isEdit" class="mb-4">
            <label for="field_name" class="block text-sm font-medium text-gray-700">Field</label>
            <Select v-model="form.field_name" :options="fieldsOptions" optionLabel="label" optionValue="value"
                placeholder="Select a Field" class="mt-1 block w-full" required />
        </div>

        <!-- Sub-Projects Multi-Select for Hiding -->
        <div class="mb-4">
            <label for="sub_projects" class="block text-sm font-medium text-gray-700">
                Sub-Projects to Hide This Field
            </label>
            <MultiSelect v-model="form.hidden_sub_project_ids" :options="subProjectsOptions" optionLabel="label"
                optionValue="value" placeholder="Select Sub-Projects" class="mt-1 block w-full" display="chip" />
        </div>

        <!-- Submit Button -->
        <Button type="button" @click="handleSubmit" :loading="loading" label="Save" icon="pi pi-save" class="p-button-primary" />
    </form>
</template>

<script>
export default {
    props: {
        initialData: {
            type: Object,
            default: null,
        },
        fields: {
            type: Array,
            required: true,
        },
        subProjects: {
            type: Array,
            required: true,
        },
        submitLabel: {
            type: String,
            default: 'Save',
        },
        isEdit: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            form: {
                field_name: '',
                hidden_sub_project_ids: [],
            },
            loading: false,
        };
    },
    computed: {
        fieldsOptions() {
            return this.fields.map(field => ({
                label: field.label,
                value: field.name,
            }));
        },
        subProjectsOptions() {
            return this.subProjects.map(sp => ({
                label: sp.title, // Corrected from sp.title to sp.name
                value: sp.id,
            }));
        },
    },
    mounted() {
        if (this.isEdit && this.initialData) {
            this.form.field_name = this.initialData.field_name;
            this.form.hidden_sub_project_ids = this.initialData.hidden_sub_project_ids;
        }
    },
    methods: {
        handleSubmit() {
            console.log('Form Submission Payload:', this.form);
            this.$emit('submit', {
                field_name: this.form.field_name,
                hidden_sub_project_ids: this.form.hidden_sub_project_ids,
            });
        },
    },
};
</script>

<style scoped>
/* Add any necessary styles */
</style>
