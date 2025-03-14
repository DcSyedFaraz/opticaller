<template>
    <div class="change-password-container">
        <Toast ref="toast" />
        <div class="card">
            <h2>Change Your Password</h2>

            <!-- Display any messages passed from the backend -->
            <p v-if="message" class="p-message p-message-info">{{ message }}</p>

            <form @submit.prevent="changePassword">
                <div class="p-field">
                    <label for="password">New Password</label>
                    <Password v-model="form.password" id="password" fluid placeholder="Enter new password"
                        :feedback="false" class="w-full " toggleMask />
                    <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
                </div>

                <div class="p-field">
                    <label for="password_confirmation">Confirm Password</label>
                    <Password v-model="form.password_confirmation" id="password_confirmation" fluid :feedback="false"
                        class="w-full " toggleMask placeholder="Confirm your new password" />
                    <small v-if="errors.password_confirmation" class="p-error">{{ errors.password_confirmation
                    }}</small>
                </div>

                <Button label="Change Password" icon="pi pi-check" type="submit"
                    class="p-button-primary submit-button" />
            </form>
        </div>
    </div>
</template>

<script>
import { usePage } from '@inertiajs/vue3';

export default {
    data() {
        return {
            form: {
                password: '',
                password_confirmation: '',
            },
            errors: {},
            message: this.$page.props.message || '', // Get message passed from the controller
        };
    },
    methods: {
        async changePassword() {
            try {
                const response = await axios.post(route('change-password.post'), this.form);
                await this.$toast.add({ severity: 'success', summary: 'Success', detail: response.data.message, life: 3000 });
                this.$inertia.replace(route('login'));
            } catch (error) {
                console.log('error', error);

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    Object.keys(errors).forEach((field) => {
                        this.errors[field] = errors[field][0];
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[field][0], life: 4000 });
                    });
                }
                if (error.response?.status === 404) {
                    this.$toast.add({ severity: 'error', summary: 'Error', detail: 'User not found.', life: 4000 });
                    this.$inertia.replace(route('login'));

                }
            }
        },
    },
    onUpdated() {
        console.log(usePage().props);

        if (usePage().props.flash.message) { // Use status.value to access reactive property
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: usePage().props.flash.message,
                life: 3000,
            });
            usePage().props.flash.message = null;
        }
    },
};
</script>

<style scoped>
.change-password-container {
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f4f7fa;
    padding: 2rem;
}

.card {
    width: 100%;
    max-width: 400px;
    padding: 2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h2 {
    margin-bottom: 1.5rem;
    font-size: 24px;
    color: #333;
}

.p-field {
    margin-bottom: 1rem;
    text-align: left;
}

.p-field label {
    font-weight: bold;
    color: #555;
    margin-bottom: 0.5rem;
    font-size: 14px;
}

.p-field .p-inputtext {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid #ccc;
    background-color: #fafafa;
    transition: border-color 0.3s ease;
}

.p-field .p-inputtext:focus {
    border-color: #007bff;
}

.p-error {
    font-size: 12px;
    color: #f44336;
    margin-top: 0.5rem;
}

.submit-button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    background-color: #007bff;
    border: none;
    border-radius: 4px;
    color: white;
    transition: background-color 0.3s ease;
}

.submit-button:hover {
    background-color: #0056b3;
}

.p-message-info {
    color: #007bff;
    font-size: 14px;
    margin-bottom: 1rem;
}
</style>
