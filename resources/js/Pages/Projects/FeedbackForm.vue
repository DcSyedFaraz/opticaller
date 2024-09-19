<template>
    <form class="mt-8">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-6">
                <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
                <InputText v-model="feedback.label" id="label" type="text" class="mt-1 block w-full" :disabled="loading"
                    required />
            </div>
            <div class="sm:col-span-6">
                <label for="value" class="block text-sm font-medium text-gray-700">Value</label>
                <InputText v-model="feedback.value" id="value" type="text" class="mt-1 block w-full" :disabled="loading"
                    required />
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <!-- Update Button to trigger handleSubmit instead of form submit -->
            <Button type="button" :label="submitLabel" class="!bg-[#383838] !border-[#383838] !rounded !px-[4rem]" :loading="loading" @click="handleSubmit" />
        </div>
    </form>
</template>


<script>
export default {
    props: {
        initialFeedback: {
            type: Object,
            default: () => ({ label: '', value: '' }),
        },
        submitLabel: {
            type: String,
            default: 'Add Feedback',
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            feedback: { ...this.initialFeedback },
        };
    },
    methods: {
        handleSubmit() {
            this.$emit('submit', { ...this.feedback });
        },
        resetForm() {
            this.feedback = { label: '', value: '' };
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
