<template>
    <Head title="Addresses" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto  sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-8 text-center">Address Details</h1>
            <form @submit.prevent="updateAddress" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-1 sm:col-span-2 lg:col-span-3 mb-4">
                    <InputLabel for="company_name">Company Name</InputLabel>
                    <InputText v-model="address.company_name" type="text" required class="w-full" />
                    <Message v-if="errors.company_name" severity="error" class="mt-2">{{ errors.company_name }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="salutation">Salutation</InputLabel>
                    <InputText v-model="address.salutation" type="text" class="w-full" />
                    <Message v-if="errors.salutation" severity="error" class="mt-2">{{ errors.salutation }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="first_name">First Name</InputLabel>
                    <InputText v-model="address.first_name" type="text" class="w-full" />
                    <Message v-if="errors.first_name" severity="error" class="mt-2">{{ errors.first_name }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="last_name">Last Name</InputLabel>
                    <InputText v-model="address.last_name" type="text" class="w-full" />
                    <Message v-if="errors.last_name" severity="error" class="mt-2">{{ errors.last_name }}</Message>
                </div>
                <div class="col-span-1 sm:col-span-2 lg:col-span-3 mb-4">
                    <InputLabel for="street_address">Street Address</InputLabel>
                    <InputText v-model="address.street_address" type="text" class="w-full" />
                    <Message v-if="errors.street_address" severity="error" class="mt-2">{{ errors.street_address }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="postal_code">Postal Code</InputLabel>
                    <InputText v-model="address.postal_code" type="text" class="w-full" />
                    <Message v-if="errors.postal_code" severity="error" class="mt-2">{{ errors.postal_code }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="city">City</InputLabel>
                    <InputText v-model="address.city" type="text" class="w-full" />
                    <Message v-if="errors.city" severity="error" class="mt-2">{{ errors.city }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="website">Website</InputLabel>
                    <InputText v-model="address.website" type="url" class="w-full" />
                    <Message v-if="errors.website" severity="error" class="mt-2">{{ errors.website }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="phone_number">Phone Number</InputLabel>
                    <InputText v-model="address.phone_number" type="text" class="w-full" />
                    <Message v-if="errors.phone_number" severity="error" class="mt-2">{{ errors.phone_number }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="email_address_system">System Email Address</InputLabel>
                    <InputText v-model="address.email_address_system" type="email" class="w-full" />
                    <Message v-if="errors.email_address_system" severity="error" class="mt-2">{{ errors.email_address_system }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="email_address_new">New Email Address</InputLabel>
                    <InputText v-model="address.email_address_new" type="email" class="w-full" />
                    <Message v-if="errors.email_address_new" severity="error" class="mt-2">{{ errors.email_address_new }}</Message>
                </div>

                <div class="mb-4">
                    <InputLabel for="priority">Project Priority</InputLabel>
                    <Select v-model="address.priority" :options="priorityOptions" optionValue="value" optionLabel="label"
                        placeholder="Select Priority" class="w-full" />
                    <Message v-if="errors.priority" severity="error" class="mt-2">{{ errors.priority }}</Message>
                </div>
                <!-- <div class="mb-4 col-span-1 sm:col-span-2 lg:col-span-3">
                    <InputLabel for="personal_notes">Personal Notes</InputLabel>
                    <Textarea v-model="address.personal_notes" class="w-full" />
                    <Message v-if="errors.personal_notes" severity="error" class="mt-2">{{ errors.personal_notes }}</Message>
                </div> -->
                <!-- <div class="mb-4 col-span-1 sm:col-span-2 lg:col-span-3">
                    <InputLabel for="interest_notes">Interest Notes</InputLabel>
                    <Textarea v-model="address.interest_notes" class="w-full" />
                    <Message v-if="errors.interest_notes" severity="error" class="mt-2">{{ errors.interest_notes }}</Message>
                </div> -->
                <div class="mb-4 col-span-1 sm:col-span-2 lg:col-span-3">
                    <InputLabel for="feedback">Feedback</InputLabel>
                    <Select v-model="address.feedback" :options="feedbackOptions" optionValue="label"
                        optionLabel="label" placeholder="Select Feedback" class="w-full" />
                    <Message v-if="errors.feedback" severity="error" class="mt-2">{{ errors.feedback }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="follow_up_date">Follow Up Date</InputLabel>
                    <DatePicker v-model="address.follow_up_date" dateFormat="dd/mm/yy" class="w-full" />
                    <Message v-if="errors.follow_up_date" severity="error" class="mt-2">{{ errors.follow_up_date }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="sub_project_id">Sub Project</InputLabel>
                    <Select v-model="address.sub_project_id" :options="subprojects" optionLabel="title" optionValue="id"
                        placeholder="Select a Project" class="w-full" filter />
                    <Message v-if="errors.sub_project_id" severity="error" class="mt-2">{{ errors.sub_project_id }}</Message>
                </div>

                <div class="col-span-1 sm:col-span-2 lg:col-span-3 flex justify-end">
                    <Button type="submit" severity='contrast'>Update Address</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
    components: { Link },
    props: {
        address: Object,
        subprojects: Array,
        users: Array,
    },
    data() {
        return {
            errors: {},
            feedbackOptions: [
                { label: 'Not Interested', value: 'Not Interested' },
                { label: 'Interested', value: 'Interested' },
                { label: 'Request', value: 'Request' },
                { label: 'Follow-up', value: 'Follow-up' },
                { label: 'Delete Address', value: 'Delete Address' },
            ],
            priorityOptions: [
                { label: 'Low', value: 1 },
                { label: 'Medium', value: 2 },
                { label: 'High', value: 3 },
                { label: 'Critical', value: 4 },
            ],
        }
    },
    methods: {
        updateAddress() {
            this.$inertia.put(`/addresses/${this.address.id}`, this.address, {
                onError: (errors) => {
                    this.errors = errors;
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key], life: 3000 });
                    });
                    console.log(this.errors);
                },
            });
        },
    },
};
</script>
