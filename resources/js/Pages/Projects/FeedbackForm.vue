<template>
    <form class="mt-8">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Label Field -->
            <div class="sm:col-span-6">
                <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
                <InputText v-model="feedback.label" id="label" type="text" class="mt-1 block w-full" :disabled="loading"
                    required />
            </div>

            <!-- Value Field -->
            <div class="sm:col-span-6">
                <label for="value" class="block text-sm font-medium text-gray-700">Value</label>
                <InputText v-model="feedback.value" id="value" type="text" class="mt-1 block w-full" :disabled="loading"
                    required />
            </div>

            <!-- Needs Validation Checkbox -->
            <div class="sm:col-span-6">
                <label for="no_validation" class="flex items-center">
                    <Checkbox v-model="feedback.no_validation" binary inputId="no_validation" :disabled="loading" />
                    <span class="ml-2 text-sm text-gray-700">No Validation</span>
                </label>
            </div>

            <!-- Sub-Projects MultiSelect Field -->
            <div class="sm:col-span-6">
                <label for="sub_project_ids" class="block text-sm font-medium text-gray-700">Assign to
                    Sub-Projects</label>
                <MultiSelect v-model="feedback.sub_project_ids" :options="subProjectOptions" optionLabel="title"
                    optionValue="id" class="mt-1 block w-full" :disabled="loading" required />
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <Button type="button" :label="submitLabel" class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem]"
                :loading="loading" @click="handleSubmit" />
        </div>
    </form>
</template>

<script>
export default {
    props: {
        initialFeedback: {
            type: Object,
            default: () => ({ label: '', value: '', no_validation: false, sub_project_ids: [] }),
        },
        submitLabel: {
            type: String,
            default: 'Add Feedback',
        },
        loading: {
            type: Boolean,
            default: false,
        },
        subProjects: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            feedback: { ...this.initialFeedback },
            subProjectOptions: this.subProjects,
        };
    },
    methods: {
        handleSubmit() {
            this.$emit('submit', { ...this.feedback });
        },
        resetForm() {
            this.feedback = { label: '', value: '', no_validation: false, sub_project_ids: [] };
        },
    },
    watch: {
        initialFeedback: {
            handler(newVal) {
                this.feedback = { ...newVal };
            },
            immediate: true,
        },
    },
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
