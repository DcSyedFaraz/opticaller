<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>
        <div class="user-page grid grid-cols-1 lg:grid-cols-2 gap-4 p-4">
            <div class="user-actions mb-4 lg:mb-0">
                <span>Login Time: {{ loginTime }}</span>
                <Button :label="isPaused ? 'Resume Countdown' : 'Pause Countdown'"
                    :severity="isPaused ? 'success' : 'info'" :icon="isPaused ? 'pi pi-play' : 'pi pi-pause'"
                    @click="togglePause" class="mr-2" />
                <Button label="Callback" icon="pi pi-phone" @click="showCallbackForm = true" />
            </div>
            <div class="countdown-timer lg:col-span-2">
                <h3 class="text-lg font-bold">Time spent on current Address: {{ formattedCountdown }}</h3>
            </div>
            <div class="localAddress-container lg:col-span-2">
                <Card class="shadow-md">
                    <template #title>
                        <h2 class="text-lg font-bold">{{ localAddress.company_name }}</h2>
                    </template>
                    <template #content>
                        <p><strong>Address:</strong> {{ localAddress.street_address }}, {{ localAddress.city }}, {{
                    localAddress.postal_code }}</p>
                        <p><strong>Phone:</strong> <a :href="'tel:' + localAddress.phone_number"
                                class="text-blue-500 hover:underline">{{ localAddress.phone_number }}</a></p>
                        <p><strong>Email:</strong> {{ localAddress.email_address }}</p>
                        <p><strong>Website:</strong> {{ localAddress.website }}</p>
                        <InputText v-model="localAddress.company_name" placeholder="Company Name" class="w-full mt-2" />
                        <InputText v-model="localAddress.street_address" placeholder="Street Address"
                            class="w-full mt-2" />
                        <InputText v-model="localAddress.city" placeholder="City" class="w-full mt-2" />
                        <InputText v-model="localAddress.postal_code" placeholder="Postal Code" class="w-full mt-2" />
                        <InputText v-model="localAddress.phone_number" placeholder="Phone Number" class="w-full mt-2" />
                        <InputText v-model="localAddress.email_address" placeholder="Email Address"
                            class="w-full mt-2" />
                        <InputText v-model="localAddress.website" placeholder="Website" class="w-full mt-2" />
                    </template>
                </Card>
            </div>
            <div class="feedback-form lg:col-span-1">
                <h3 class="text-lg font-bold">Feedback</h3>
                <Select v-model="localAddress.feedback" :options="feedbackOptions" optionValue="label"
                    optionLabel="label" placeholder="Select Feedback" class="w-full my-2" />
                <Textarea v-model="localAddress.personal_notes" rows="5" placeholder="Enter notes here" autoResize
                    class="w-full" />
                <Button label="Submit Feedback" icon="pi pi-check" @click="submitFeedback" class="w-full" />
            </div>
            <div class="call-history lg:col-span-1">
                <h3 class="text-lg font-bold">Call History</h3>
                <DataTable :value="callHistory" class="w-full" scrollable scrollHeight="250px">
                    <Column field="starting_time" header="Date" >
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data) }}
                        </template>
                </Column>
                <template #empty> No call logs available. </template>
                <!-- <Column field="id" header="Note" /> -->
            </DataTable>
            </div>
            <Dialog header="Callback Request" v-model:visible="showCallbackForm" modal class="rounded-lg shadow-lg">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Select v-model="callbackForm.project" :options="projectOptions" placeholder="Select Project"
                            optionValue="label" optionLabel="label"
                            class="w-full p-2 border border-gray-300 rounded-md" />
                        <InputText v-model="localAddress.salutation" placeholder="Salutation"
                            class="w-full p-2 border border-gray-300 rounded-md" />
                        <InputText v-model="localAddress.first_name" placeholder="First Name"
                            class="w-full p-2 border border-gray-300 rounded-md" />
                        <InputText v-model="localAddress.last_name" placeholder="Last Name"
                            class="w-full p-2 border border-gray-300 rounded-md" />
                        <InputText v-model="localAddress.phone_number" placeholder="Phone Number"
                            class="w-full p-2 border border-gray-300 rounded-md" />
                        <Textarea v-model="localAddress.personal_notes" rows="5" placeholder="Notes" autoResize
                            class="w-full p-2 border border-gray-300 rounded-md" />
                    </div>
                    <div class="flex justify-end">
                        <Button label="Submit" icon="pi pi-check" @click="submitCallbackRequest"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out" />
                    </div>
                </div>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>


export default {
    props: {
        address: Object,
        logs: Array,
    },
    data() {
        return {
            localAddress: { ...this.address },
            callHistory: [...this.logs],
            countdown: 0,
            pauseTime: 0,
            projectTitle: '',
            feedback: '',
            notes: '',
            isPaused: false,
            timer: null,
            showCallbackForm: false,
            callbackForm: {
                project: null,
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',
                notes: '',
            },
            startTime: 0,
            loginTime: new Date().toLocaleTimeString(),
            timeLogId: null,
            pauseLogId: null,
        };
    },
    computed: {
        projectOptions() {
            return [
                { label: 'vimtronix', value: 'vimtronix' },
                { label: 'XSimpress', value: 'XSimpress' },
                { label: 'box4pflege.de', value: 'box4pflege.de' },
            ];
        },
        feedbackOptions() {
            return [
                { label: 'Not Interested', value: 'Not Interested' },
                { label: 'Interested', value: 'Interested' },
                { label: 'Request', value: 'Request' },
                { label: 'Follow-up', value: 'Follow-up' },
                { label: 'Delete Address', value: 'Delete Address' },
            ];
        },
        formattedCountdown() {
            // console.log(this.countdown);
            const minutes = Math.floor(this.countdown / 60);
            const seconds = this.countdown % 60;
            return `${minutes}m ${seconds}s`;
        },
        progressValue() {
            return (this.countdown / (5 * 60)) * 100; // Assume 5 minutes as full progress for demo
        }
    },
    mounted() {
        this.startTracking();
        console.log(this.address);
    },
    methods: {
        formatDate(data) {
            const date = new Date(data.starting_time);
            return date.toLocaleDateString();
        },
        async startTracking() {
            try {
                this.isPaused = false;
                const response = await axios.post('/start-tracking', {
                    address_id: this.address.id,
                });
                this.timeLogId = response.data.id;
                this.countdown = 0;
                await this.startCountdown();
            } catch (error) {
                console.error('Error starting tracking:', error);
            }
        },
        startCountdown() {
            if (this.timer) clearInterval(this.timer);
            this.startTime = new Date().getTime();
            this.timer = setInterval(() => {
                if (!this.isPaused) {
                    this.countdown = Math.floor((new Date().getTime() - this.startTime) / 1000);
                }
            }, 1000);
        },
        async togglePause() {
            this.isPaused = !this.isPaused;
            try {
                if (this.isPaused) {
                    this.pauseTime = new Date().getTime(); // Store the pause time
                    const res = await axios.post(`/pause-tracking/${this.address.id}`);
                    this.pauseLogId = res.data.id;
                    console.log(this.pauseLogId);
                } else {
                    const resumeTime = new Date().getTime();
                    this.startTime += resumeTime - this.pauseTime; // Adjust the start time
                    await axios.post(`/resume-tracking/${this.pauseLogId}`);
                }
            } catch (error) {
                console.error('Error toggling pause:', error);
            }
        },
        async submitFeedback() {
            // Implement feedback submission logic here
            // alert(`Feedback: ${this.address.feedback}\nNotes: ${this.address.personal_notes}`);
            if (this.isPaused) {
                await this.togglePause(); // Resume the tracking if it's paused
            }
            this.isPaused = true;
            clearInterval(this.timer);
            const res = await axios.post(`/stop-tracking/${this.timeLogId}`);
            console.log(res.data.address);
            this.localAddress = res.data.address;
            this.callHistory = res.data.calLogs;
            this.timer = null;
            await this.startTracking();
            // if (res.data) {
            //     this.address = res.data;
            //     this.countdown = 0;
            //     this.startTracking();
            // }
            // Save the edits
            //   await axios.post('/api/save-address', this.address);
            // console.log(res.data, 'new');
            // Fetch the next address (for demo, we'll just reset the current address)
            // this.$inertia.reload();
        },
        async submitCallbackRequest() {
            const emailMap = {
                vimtronix: 'info@vimtronix.com',
                XSimpress: 'info@xsimpress.com',
                box4pflege: 'info@box4pflege.de',
            };

            const emailAddress = emailMap[this.callbackForm.project];
            const subject = 'Rückruf bitte';
            const body = `
        Project: ${this.callbackForm.project}
        Salutation: ${this.callbackForm.salutation}
        First Name: ${this.callbackForm.firstName}
        Last Name: ${this.callbackForm.lastName}
        Phone Number: ${this.callbackForm.phoneNumber}
        Notes: ${this.callbackForm.notes}
      `;

            // Simulate sending an email
            alert(`Email sent to: ${emailAddress}\nSubject: ${subject}\nBody: ${body}`);

            // Reset form and close dialog
            this.callbackForm = {
                project: null,
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',
                notes: '',
            };
            this.showCallbackForm = false;
        },
    },
};
</script>

<style>
.user-page {
    padding: 20px;
}

.user-actions {
    margin-bottom: 20px;
    text-align: right;
}

.address-container {
    margin-bottom: 20px;
}

.feedback-form {
    margin-bottom: 20px;
}

.call-history {
    margin-bottom: 20px;
}

.countdown-timer {
    margin-bottom: 20px;
}
</style>
