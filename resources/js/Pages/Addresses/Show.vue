<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto  sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-8 text-center">Address Details</h1>
            <form @submit.prevent="updateAddress" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-1 sm:col-span-2 lg:col-span-3 mb-4">
                    <InputLabel for="company_name">Company Name</InputLabel>
                    <InputText v-model="address.company_name" type="text" required class="w-full" />
                    <Message v-if="errors.company_name" severity="error" class="mt-2">{{ errors.company_name }}
                    </Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="salutation">Salutation</InputLabel>
                    <Select id="salutation" v-model="address.salutation" placeholder="select salutation"
                        :options="salutationOptions" optionLabel="label" optionValue="value" class="w-full" />
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
                    <Message v-if="errors.street_address" severity="error" class="mt-2">{{ errors.street_address }}
                    </Message>
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
                    <InputLabel for="country">Country</InputLabel>
                    <Select id="country" v-model="address.country" filter :options="country_names"
                        placeholder="Select a country" class="w-full " />
                    <Message v-if="errors.country" severity="error" class="mt-2">{{ errors.country }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="website">Website</InputLabel>
                    <InputText v-model="address.website" type="text" class="w-full" />
                    <Message v-if="errors.website" severity="error" class="mt-2">{{ errors.website }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="phone_number">Phone Number</InputLabel>
                    <InputText v-model="address.phone_number" type="text" class="w-full" />
                    <Message v-if="errors.phone_number" severity="error" class="mt-2">{{ errors.phone_number }}
                    </Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="email_address_system">System Email Address</InputLabel>
                    <InputText v-model="address.email_address_system" type="email" class="w-full" />
                    <Message v-if="errors.email_address_system" severity="error" class="mt-2">{{
                        errors.email_address_system }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="email_address_new">New Email Address</InputLabel>
                    <InputText v-model="address.email_address_new" type="email" class="w-full" />
                    <Message v-if="errors.email_address_new" severity="error" class="mt-2">{{ errors.email_address_new
                        }}</Message>
                </div>

                <div class="mb-4">
                    <InputLabel for="linkedin">LinkedIn</InputLabel>
                    <InputText v-model="address.linkedin" type="text" class="w-full" />
                    <Message v-if="errors.linkedin" severity="error" class="mt-2">{{ errors.linkedin }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="logo">Logo</InputLabel>
                    <InputText v-model="address.logo" type="text" class="w-full" />
                    <Message v-if="errors.logo" severity="error" class="mt-2">{{ errors.logo }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="notes">Notes</InputLabel>
                    <Textarea v-model="address.notes" class="w-full" />
                    <Message v-if="errors.notes" severity="error" class="mt-2">{{ errors.notes }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="sub_project_id">Sub Project</InputLabel>
                    <Select v-model="address.sub_project_id" :options="subprojects" optionLabel="title" optionValue="id"
                        placeholder="Select a Project" class="w-full" filter />
                    <Message v-if="errors.sub_project_id" severity="error" class="mt-2">{{ errors.sub_project_id }}
                    </Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="contact_id">Contact ID</InputLabel>
                    <InputText v-model="address.contact_id" type="number" class="w-full" />
                    <Message v-if="errors.contact_id" severity="error" class="mt-2">{{ errors.contact_id }}</Message>
                </div>
                <div class="mb-4">
                    <InputLabel for="hubspot_tag">HubSpot Tag</InputLabel>
                    <InputText v-model="address.hubspot_tag" type="text" class="w-full" />
                    <Message v-if="errors.hubspot_tag" severity="error" class="mt-2">{{ errors.hubspot_tag }}</Message>
                </div>

                <div class="mb-4">
                    <InputLabel for="deal_id">Deal ID</InputLabel>
                    <InputText v-model="address.deal_id" type="text" class="w-full" />
                    <Message v-if="errors.deal_id" severity="error" class="mt-2">{{ errors.deal_id }}</Message>
                </div>

                <div class="mb-4">
                    <InputLabel for="company_id">Company ID</InputLabel>
                    <InputText v-model="address.company_id" type="text" class="w-full" />
                    <Message v-if="errors.company_id" severity="error" class="mt-2">{{ errors.company_id }}</Message>
                </div>

                <div class="mb-4">
                    <InputLabel for="titel">Title</InputLabel>
                    <Select id="titel" v-model="address.titel" placeholder="select titel" :options="titelOptions"
                    optionLabel="label" optionValue="value" class="w-full " />
                    <Message v-if="errors.titel" severity="error" class="mt-2">{{ errors.titel }}</Message>
                </div>

                <div class="col-span-1 sm:col-span-2 lg:col-span-3 flex justify-end">
                    <Button type="submit" severity='contrast'>Update Address</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>

</template>

<script>

export default {
    props: {
        address: Object,
        subprojects: Array,
        users: Array,
    },
    data() {
        return {
            errors: {},
            country_names: ['Germany', 'Austria', 'Switzerland', 'France', 'Italy'],
            salutationOptions: [
                { label: 'Herr', value: 'Herr' },
                { label: 'Frau', value: 'Frau' },
                { label: 'Divers', value: 'Divers' },
                { label: 'Sehr geehrte Damen und Herren', value: 'Sehr geehrte Damen und Herren' },
            ],
            titelOptions: [
                { label: 'Dr.', value: 'Dr.' },
                { label: 'Prof.', value: 'Prof.' },
                { label: 'Prof. Dr.', value: 'Prof. Dr.' },

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
