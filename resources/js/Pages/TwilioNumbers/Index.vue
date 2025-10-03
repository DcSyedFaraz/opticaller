<template>
    <Head title="Twilio Numbers" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Twilio Numbers</h1>

            <Card class="mb-6">
                <template #title>
                    <span class="font-semibold">Add New Number</span>
                </template>
                <template #content>
                    <form @submit.prevent="createNumber" class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Label</label>
                            <InputText v-model="form.label" class="w-full" placeholder="Sales, Support..." />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <InputText v-model="form.phone_number" class="w-full" placeholder="+15551234567" />
                            <small class="text-gray-500">Must be E.164, e.g. +15551234567</small>
                        </div>
                        <div class="sm:col-span-6">
                            <Button type="submit" label="Add" class="!bg-indigo-600 !border-indigo-600" />
                        </div>
                    </form>
                </template>
            </Card>

            <Card>
                <template #title>
                    <span class="font-semibold">Saved Numbers</span>
                </template>
                <template #content>
                    <DataTable :value="numbers" responsiveLayout="scroll">
                        <Column field="label" header="Label"></Column>
                        <Column field="phone_number" header="Phone"></Column>
                        <Column header="Actions">
                            <template #body="{ data }">
                                <div class="space-x-2">
                                    <Button size="small" label="Edit" @click="openEdit(data)" />
                                    <Button size="small" severity="danger" label="Delete" @click="remove(data)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <Dialog v-model:visible="editDialog" header="Edit Number" :modal="true" :style="{ width: '32rem' }">
                <form @submit.prevent="updateNumber">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Label</label>
                        <InputText v-model="editForm.label" class="w-full" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <InputText v-model="editForm.phone_number" class="w-full" placeholder="+15551234567" />
                        <small class="text-gray-500">Must be E.164, e.g. +15551234567</small>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <Button type="button" label="Cancel" severity="secondary" @click="editDialog=false" />
                        <Button type="submit" label="Save" />
                    </div>
                </form>
            </Dialog>
        </div>
    </AuthenticatedLayout>
    <Toast />
    <ConfirmDialog />
    
</template>

<script>
export default {
    props: {
        numbers: Array,
    },
    data() {
        return {
            form: { label: '', phone_number: '' },
            editDialog: false,
            editForm: { id: null, label: '', phone_number: '' },
        }
    },
    methods: {
        createNumber() {
            this.$inertia.post(this.route('twilio-numbers.index'), this.form, {
                onSuccess: () => {
                    this.form = { label: '', phone_number: '' }
                    this.$toast.add({ severity: 'success', summary: 'Saved', life: 2000 })
                },
                onError: (errs) => {
                    Object.values(errs || {}).forEach((m) => this.$toast.add({ severity: 'error', summary: 'Error', detail: m }))
                }
            })
        },
        openEdit(row) {
            this.editForm = { ...row }
            this.editDialog = true
        },
        updateNumber() {
            this.$inertia.post(this.route('twilio-numbers.update', this.editForm.id), {
                _method: 'PUT',
                label: this.editForm.label,
                phone_number: this.editForm.phone_number,
            }, {
                onSuccess: () => {
                    this.editDialog = false
                    this.$toast.add({ severity: 'success', summary: 'Updated', life: 2000 })
                },
                onError: (errs) => {
                    Object.values(errs || {}).forEach((m) => this.$toast.add({ severity: 'error', summary: 'Error', detail: m }))
                }
            })
        },
        remove(row) {
            this.$confirm.require({
                message: 'Delete this number?',
                header: 'Confirm',
                accept: () => {
                    this.$inertia.delete(this.route('twilio-numbers.destroy', row.id), {
                        onSuccess: () => this.$toast.add({ severity: 'success', summary: 'Deleted', life: 2000 })
                    })
                }
            })
        }
    }
}
</script>
