<template>
    <Dialog v-bind:visible="dialogVisible" header="Lock/Unlock Fields" :modal="true"
        @update:visible="$emit('update:dialogVisible', $event)" :closable="true">
        <div class="p-fluid">
            <div v-for="(fieldLabel, fieldName) in fields" :key="fieldName"
                class="mb-4 flex justify-between items-center">
                <label :for="fieldName" class="text-gray-700 font-medium">{{ fieldLabel }}</label>
                <ToggleButton size="large" :id="fieldName" v-model="fieldLocks[fieldName]" :onLabel="'Locked'" :offLabel="'Unlocked'"
                    :onIcon="'pi pi-lock'" :offIcon="'pi pi-unlock'" :class="[
        'transition-all duration-300 ease-in-out transform',
        fieldLocks[fieldName] ? '  hover:!border-red-600' : 'p-button-success bg-green-500 hover:bg-green-600'
    ]" />
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end mt-4 space-x-2">
                <Button label="Save" icon="pi pi-check"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow-md"
                    @click="saveLocks" />
                <Button label="Cancel" icon="pi pi-times"
                    class="p-button-secondary bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded shadow-md"
                    @click="$emit('update:dialogVisible', false)" />
            </div>
        </template>
    </Dialog>
</template>

<script>
export default {
    props: {
        dialogVisible: {
            type: Boolean,
            default: false,
        },
        initialLockedFields: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            fields: {
                // contact_id: 'Contact ID',
                company_name: 'Company Name',
                salutation: 'Salutation',
                first_name: 'First Name',
                last_name: 'Last Name',
                street_address: 'Street Address',
                postal_code: 'Postal Code',
                city: 'City',
                country: 'Country',
                website: 'Website',
                phone_number: 'Phone Number',
                email_address_new: 'Email Address (New)',
            },
            fieldLocks: {},
        };
    },
    watch: {
        initialLockedFields: {
            immediate: true,
            handler(newVal) {
                this.fieldLocks = Object.keys(this.fields).reduce((acc, field) => {
                    acc[field] = newVal.includes(field);
                    return acc;
                }, {});
            },
        },
    },
    methods: {
        saveLocks() {
            const lockedFields = Object.keys(this.fieldLocks).filter(field => this.fieldLocks[field]);
            this.$emit('save', lockedFields);
            // this.dialogVisible = false;
        },
    },
};
</script>

<style scoped>
.field-checkbox {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
