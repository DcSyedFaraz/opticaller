<template>

    <Head title="Users" />
    <AuthenticatedLayout>

        <div class="">
            <h1 class="text-2xl font-bold mb-4">Edit User</h1>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Name</label>
                    <InputText type="text" v-model="form.name" id="name"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                    <div v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</div>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <InputText type="email" v-model="form.email" id="email"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                    <div v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</div>
                </div>
                <div class="mb-4 flex items-center">
                    <Checkbox binary v-model="form.auto_calling" inputId="auto_calling" class="mr-2" />
                    <label for="auto_calling" class="text-gray-700">Enable Auto Calling</label>
                </div>
                <div v-if="errors.auto_calling" class="text-red-500 text-sm mb-4">{{ errors.auto_calling }}</div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <InputText type="password" v-model="form.password" id="password"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                    <div v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</div>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700">Confirm Password</label>
                    <InputText type="password" v-model="form.password_confirmation" id="password_confirmation"
                        class="mt-1 block w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                    <div v-if="errors.password_confirmation" class="text-red-500 text-sm">{{
                        errors.password_confirmation }}</div>
                </div>
                <div class="mb-4">
                    <label for="roles" class="block text-gray-700">Roles</label>
                    <Select v-model="form.roles" :options="roles" option-label="name" option-value="name"
                        class="mt-1 block w-full"></Select>
                    <div v-if="errors.roles" class="text-red-500 text-sm">{{ errors.roles }}</div>
                </div>
                <Button type="submit"
                    class="!bg-[#383838] text-white !py-3 !px-[3rem] !rounded !border-[#383838]">Update</Button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    data() {
        return {
            form: {
                name: this.$page.props.user.name,
                email: this.$page.props.user.email,
                auto_calling: this.$page.props.user.auto_calling,
                password: '',
                password_confirmation: '',
                roles: this.$page.props.userRoles
            },
            roles: this.$page.props.roles,
            errors: {}
        }
    },
    methods: {
        submit() {
            this.$inertia.put(route('users.update', this.$page.props.user.id), this.form, {
                onSuccess: () => {
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'User updated successfully', life: 3000 });
                },
                onError: (errors) => {
                    console.log(errors);
                    this.errors = errors;

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
