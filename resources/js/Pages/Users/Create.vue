<template>

    <Head title="Add New User" />
    <AuthenticatedLayout>

        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-4">Add New User</h1>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Name</label>
                    <InputText v-model="form.name" id="name"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <InputText v-model="form.email" id="email"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                </div>
                <div class="mb-4 flex items-center">
                    <Checkbox binary v-model="form.auto_calling" inputId="auto_calling" class="mr-2" />
                    <label for="auto_calling" class="text-gray-700 my-auto">Enable Auto Calling</label>
                </div>
                <div class="mb-4 flex items-center">

                    <Checkbox binary v-model="form.invite_user" inputId="invite_user" class="mr-2" />
                    <label for="invite_user" class="text-gray-700 my-auto">
                        Send invitation email instead of setting a password
                    </label>

                </div>

                <div class="mb-4" v-if="!form.invite_user">
                    <label for="password" class="block text-gray-700">Password</label>
                    <InputText v-model="form.password" id="password"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                </div>
                <div class="mb-4" v-if="!form.invite_user">
                    <label for="password_confirmation" class="block text-gray-700">Confirm Password</label>
                    <InputText v-model="form.password_confirmation" id="password_confirmation"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                </div>
                <div class="mb-4">
                    <label for="roles" class="block text-gray-700">Roles</label>
                    <Select v-model="form.roles" :options="roles" optionLabel="name" optionValue="name"
                        class="mt-1 block w-full" />
                </div>
                <Button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create</Button>
            </form>
        </div>
    </AuthenticatedLayout>

</template>

<script>

export default {
    data() {
        return {
            roles: this.$page.props.roles,
            form: this.$inertia.form({
                name: '',
                email: '',
                password: '',
                auto_calling: '',
                invite_user: false,
                password_confirmation: '',
                roles: []
            })
        }
    },
    methods: {
        submit() {
            if (this.form.invite_user) {
                this.form.password = ''
                this.form.password_confirmation = ''
            }
            this.form.post(route('users.store'), {
                onSuccess: () => {
                    const detail = this.form.invite_user
                        ? 'User invited successfully'
                        : 'User created successfully'
                    this.$toast.add({ severity: 'success', summary: 'Success', detail, life: 3000 })
                },
                onError: (errors) => {
                    console.log(errors);

                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key] });
                    });
                },
            });
        }
    }
}

</script>

<style scoped>
.container {
    max-width: 600px;
    margin: 0 auto;
}
</style>
