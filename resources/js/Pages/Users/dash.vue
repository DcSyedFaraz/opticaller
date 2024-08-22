<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>

        <div class="user-page grid grid-cols-4 gap-4 p-4">
            <!-- Main Panel -->
            <div class="col-span-3 border rounded-xl shadow-xl">
                <div class=" pb-2 p-4">
                    <div
                        class="inline-flex items-center rounded-md bg-black px-2 py-1 text-3xl font-medium text-white ring-1 ring-inset ring-black mb-1">
                        Sub
                        Project Title</div>
                    <div class="flex justify-between items-center">

                        <div
                            class="inline-flex items-center rounded-md bg-primary px-2 py-1 text-xl font-medium text-white ring-1 ring-inset ring-primary mb-1">
                            Project Title: Name of the active project.</div>
                        <div
                            class="inline-flex items-center rounded-md  px-2 py-1  font-medium text-black border border-black mb-1">
                            <span>
                                Contact ID: &nbsp;
                            </span>
                            <span class="font-extrabold">
                                X37590
                            </span>
                        </div>
                    </div>
                </div>
                <div class="border m-7 rounded-lg shadow-xl shadow-secondary">
                    <div class="border-b-2 p-3">
                        <span class="mx-4 font-extrabold text-lg">
                            Details
                        </span>
                    </div>

                    <div class="grid">
                        <Card class="shadow-md">
                            <template #content>
                                <div class="grid grid-cols-1 mx-3 gap-4">
                                    <div class="field">
                                        <label for="company_name" class="font-extrabold text-lg">Company Name: <span
                                                class="text-red-600">*</span></label>
                                        <InputText id="company_name" v-model="localAddress.company_name" class="w-full"
                                            :disabled="isFieldLocked('company_name')" />
                                    </div>

                                    <div class="grid grid-cols-5 gap-4">
                                        <div class="field">
                                            <label for="salutation" class="font-extrabold text-lg">Salutation: <span
                                                    class="text-red-600">*</span></label>
                                            <InputText id="salutation" v-model="localAddress.salutation" class="w-full"
                                                :disabled="isFieldLocked('salutation')" />
                                        </div>

                                        <div class="field col-span-2">
                                            <label for="first_name" class="font-extrabold text-lg">First Name: <span
                                                    class="text-red-600">*</span></label>
                                            <InputText id="first_name" v-model="localAddress.first_name" class="w-full"
                                                :disabled="isFieldLocked('first_name')" />
                                        </div>
                                        <div class="field col-span-2">
                                            <label for="last_name" class="font-extrabold text-lg">Last Name: <span
                                                    class="text-red-600">*</span></label>
                                            <InputText id="last_name" v-model="localAddress.last_name" class="w-full"
                                                :disabled="isFieldLocked('last_name')" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-5 gap-4">

                                        <div class="field col-span-2">
                                            <label class="font-extrabold text-lg" for="street_address">Street Address:
                                                <span class="text-red-600">*</span></label>
                                            <InputText id="street_address" v-model="localAddress.street_address"
                                                class="w-full" :disabled="isFieldLocked('street_address')" />
                                        </div>
                                        <div class="field ">
                                            <label class="font-extrabold text-lg" for="postal_code">Postal Code: <span
                                                    class="text-red-600">*</span></label>
                                            <InputText id="postal_code" v-model="localAddress.postal_code"
                                                class="w-full" :disabled="isFieldLocked('postal_code')" />
                                        </div>
                                        <div class="field col-span-2">
                                            <label class="font-extrabold text-lg" for="city">City: <span
                                                    class="text-red-600">*</span></label>
                                            <InputText id="city" v-model="localAddress.city" class="w-full"
                                                :disabled="isFieldLocked('city')" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">

                                    </div>
                                    <div class="field">
                                        <label for="phone_number">Phone Number:</label>
                                        <InputText id="phone_number" v-model="localAddress.phone_number" class="w-full"
                                            :disabled="isFieldLocked('phone_number')" />
                                    </div>
                                    <div class="field">
                                        <label for="email_address_system" class="font-extrabold text-lg">Email
                                            Address: <span class="text-red-600">*</span></label>
                                        <InputText id="email_address_system" v-model="localAddress.email_address_system"
                                            class="w-full" :disabled="isFieldLocked('email_address_system')" />
                                    </div>
                                    <div class="field">
                                        <label for="email_address_new">Email New:</label>
                                        <InputText id="email_address_new" v-model="localAddress.email_address_new"
                                            class="w-full" :disabled="isFieldLocked('email_address_new')" />
                                    </div>
                                    <div class="field">
                                        <label for="website">Website:</label>
                                        <InputText id="website" v-model="localAddress.website" class="w-full"
                                            :disabled="isFieldLocked('website')" />
                                    </div>
                                    <div class="field">
                                        <label for="attempts">No of call attempts:</label>
                                        <InputText type="number" id="attempts" v-model="logdata.call_attempts"
                                            class="w-full" />
                                        <div v-if="!logdata.call_attempts || logdata.call_attempts <= 0"
                                            class="text-red-500 text-sm">Please enter a positive number</div>

                                    </div>
                                    <div class="field">
                                        <label for="interest_notes">Interest Notes:</label>
                                        <Textarea id="interest_notes" v-model="localAddress.interest_notes" rows="5"
                                            class="w-full" />
                                    </div>
                                    <div class="field">
                                        <label for="personal_notes">Personal Notes:</label>
                                        <Textarea id="personal_notes" v-model="logdata.personal_notes" rows="5"
                                            class="w-full" />
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

                <div class="feedback-form ">
                    <Card class="shadow-md">
                        <template #title>
                            <h2 class="text-lg font-bold">Feedback</h2>
                        </template>
                        <template #content>
                            <div class="field">
                                <label for="feedback">Feedback:</label>
                                <Select id="feedback" v-model="localAddress.feedback" :options="feedbackOptions"
                                    optionValue="label" optionLabel="label" class="w-full" />
                            </div>
                            <div class="field" v-if="localAddress.feedback === 'Follow-up'">
                                <label for="follow_up_date">Follow-up Date:</label>
                                <DatePicker id="follow_up_date" showTime hourFormat="24" fluid
                                    v-model="localAddress.follow_up_date" class="w-full" />
                            </div>
                            <Button label="Submit Feedback" icon="pi pi-check" @click="submitFeedback" class="w-full" />
                        </template>
                    </Card>
                </div>
                <div class="call-history lg:col-span-1">
                    <Card class="shadow-md">
                        <template #title>
                            <h2 class="text-lg font-bold">Call History</h2>
                        </template>
                        <template #content>
                            <DataTable :value="callHistory" class="w-full" scrollable scrollHeight="250px">
                                <Column field="created_at" header="Date">
                                    <template #body="slotProps">
                                        {{ formatDate(slotProps.data) }}
                                    </template>
                                </Column>
                                <Column header="Call Attempts">
                                    <template #body="slotProps">
                                        {{ slotProps.data.notes?.call_attempts ?? 'N/A' }}
                                    </template>
                                </Column>
                                <Column header="Personal Notes">
                                    <template #body="slotProps">
                                        <Button @click="showNotes(slotProps.data.notes?.personal_notes)"
                                            v-if="slotProps.data.notes?.personal_notes">
                                            View Notes
                                        </Button>
                                        <span v-else>
                                            N/A
                                        </span>
                                    </template>
                                </Column>
                                <template #empty> No call logs available. </template>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
            <!-- Side Panel -->
            <div class="col-span-1 w-2/3">
                <Button label="Callback" icon="pi pi-phone" class=" w-full my-2" size="large"
                    @click="showCallbackForm = true" />
                <Button @click="togglePause" :label="isPaused ? 'End Break' : 'Take Break'"
                    :icon="isPaused ? 'pi pi-play' : 'pi pi-hourglass'"
                    class=" w-full my-2 !bg-secondary !border-secondary" size="large" />

                <div class="grid grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-sm">Login Time</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-2xl">{{ loginTime }}</h1>
                    </div>
                    <div class="col-span-1">
                        <span class=" pi pi-clock !text-[3rem] text-primary ml-2" data-pc-section="icon"></span>
                    </div>

                </div>

                <div class="grid grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-[0.85rem]">Count down Time</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-2xl">00:05:25</h1>
                    </div>
                    <div class="col-span-1">
                        <span class=" pi pi-clock !text-[3rem] ml-2" data-pc-section="icon"></span>
                    </div>

                </div>

                <div class="grid grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-sm">Time on Page</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-2xl">{{ formattedCountdown }}</h1>
                    </div>
                    <div class="col-span-1">
                        <span class=" pi pi-clock !text-[3rem] text-secondary ml-2" data-pc-section="icon"></span>
                    </div>

                </div>

                <div class="bg-white border border-gray-300 rounded-lg shadow-xl  ">
                    <div class="flex justify-between items-center pb-2  border-b-2 p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Call History</h3>
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="pi pi-expand !text-xl text-secondary"></i>
                        </button>
                    </div>
                    <div class="my-3">

                        <div class="mb-2 p-4">
                            <span
                                class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-secondary mb-1">Last
                                call on: Sep 20 Friday</span>
                            <span
                                class="inline-flex items-center rounded-md bg-primary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-primary">Office:
                                Newyork, USA</span>
                            <span
                                class="inline-flex items-center rounded-md bg-[#3E3E3E] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#3E3E3E]">Agent:
                                Smith Parker</span>
                        </div>
                        <div class="border-b border-gray-300 pt-2 p-4">
                            <div class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="h-2 w-2 bg-primary rounded-full mr-2"></span>
                                    <span class="text-md font-[1000] text-green-600">Personal Notes:</span>
                                </div>
                                <p class="text-sm text-gray-700 font-bold">New Location Visited Austin Martin Shop...
                                </p>
                            </div>
                            <div class="">
                                <div class="flex items-center mb-1">
                                    <span class="text-md font-[1000] text-red-500">Interest Notes:</span>
                                </div>
                                <p class="text-sm  font-bold">Interested in X product or ...</p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">Call
                                Duration: &nbsp;<span class="font-bold"> 5 minutes</span></span>
                        </div>
                    </div>
                    <div class="my-3">

                        <div class="mb-2 p-4">
                            <span
                                class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-secondary mb-1">Last
                                call on: Sep 20 Friday</span>
                            <span
                                class="inline-flex items-center rounded-md bg-primary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-primary">Office:
                                Newyork, USA</span>
                            <span
                                class="inline-flex items-center rounded-md bg-[#3E3E3E] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#3E3E3E]">Agent:
                                Smith Parker</span>
                        </div>
                        <div class="border-b border-gray-300 pt-2 p-4">
                            <div class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="h-2 w-2 bg-primary rounded-full mr-2"></span>
                                    <span class="text-sm font-bold text-green-600">Personal Notes:</span>
                                </div>
                                <p class="text-sm text-gray-700 font-bold">New Location Visited Austin Martin Shop...
                                </p>
                            </div>
                            <div class="">
                                <div class="flex items-center mb-1">
                                    <span class="text-sm font-bold text-red-500">Interest Notes:</span>
                                </div>
                                <p class="text-sm text-red-500 font-bold">Interested in X product or ...</p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">Call
                                Duration: &nbsp;<span class="font-bold"> 5 minutes</span></span>
                        </div>
                    </div>
                </div>

            </div>

            <Dialog header="Personal Notes" v-model:visible="showNotesModal" modal class="rounded-lg shadow-lg"
                :style="{ width: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        {{ selectedNotes }}

                    </div>
                </div>
            </Dialog>
            <Dialog header="Callback Request" v-model:visible="showCallbackForm" modal class="rounded-lg shadow-lg"
                :style="{ width: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <div class="field">
                            <label for="project">Project:</label>
                            <Select id="project" v-model="callbackForm.project" :options="projectOptions"
                                optionValue="label" optionLabel="label" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="salutation">Salutation:</label>
                            <InputText id="salutation" v-model="callbackForm.salutation" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="first_name">First Name:</label>
                            <InputText id="first_name" v-model="callbackForm.firstName" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="last_name">Last Name:</label>
                            <InputText id="last_name" v-model="callbackForm.lastName" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="phoneNumber">Phone Number:</label>
                            <InputText id="phoneNumber" v-model="callbackForm.phoneNumber" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="personal_notes">Notes:</label>
                            <Textarea id="personal_notes" v-model="callbackForm.notes" rows="5" class="w-full" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button label="Submit" icon="pi pi-check" @click="submitCallbackRequest"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out" />
                    </div>
                </div>
            </Dialog>
        </div>
        <div v-if="isLoading" class="loading-overlay">
            <ProgressSpinner style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" />
        </div>
    </AuthenticatedLayout>
</template>


<script>


export default {

    props: {
        address: Object,
        lockfields: Array,
    },
    data() {
        return {
            localAddress: { ...this.address },
            locallockfields: [...this.lockfields],
            callHistory: [...(this.address?.cal_logs ?? [])],
            logdata: {},
            errors: {},
            form: {},
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
            loginTime: this.$page.props.auth.user.logintime,
            timeLogId: null,
            pauseLogId: null,
            isLoading: false,
            showNotesModal: false,
            selectedNotes: '',

        };
    },
    computed: {
        isFieldLocked() {
            return (fieldName) => this.locallockfields.includes(fieldName);
        },

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
        console.log(this.address, this.$page.props);
    },
    methods: {
        showNotes(notes) {
            this.selectedNotes = notes
            this.showNotesModal = true
        },
        formatDate(data) {
            const date = new Date(data.starting_time);
            return date.toLocaleDateString();
        },
        async startTracking() {
            try {
                this.isPaused = false;
                const response = await axios.post('/start-tracking', {
                    address_id: this.localAddress.id,
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

            if (!this.logdata.call_attempts || this.logdata.call_attempts <= 0) {
                this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Please enter a positive number for call attempts', life: 4000 });
                return;
            }


            this.isLoading = true;
            try {

                if (this.localAddress.feedback != 'Follow-up') {
                    this.localAddress.follow_up_date = null
                }

                if (this.isPaused) {
                    await this.togglePause(); // Resume the tracking if it's paused
                }
                this.isPaused = true;
                clearInterval(this.timer);
                const res = await axios.post(`/stop-tracking/${this.timeLogId}`, {
                    ...this.logdata,
                    address: this.localAddress,
                });
                this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Data saved successfully.', life: 4000 });
                console.log(res.data);
                this.localAddress = res.data.address;
                this.locallockfields = res.data.lockfields;
                this.callHistory = res.data.address.cal_logs;
                this.timer = null;
                this.logdata.call_attempts = null;
                this.logdata.personal_notes = '';
                await this.startTracking();
                this.isLoading = false;

            } catch (error) {
                this.isLoading = false;
                if (error.response.status === 422) {
                    const errors = error.response.data.errors;
                    console.log(errors);
                    for (const field in errors) {
                        this.errors[field] = errors[field][0];
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[field][0], life: 4000 });
                    }
                } else {
                    console.error('Error submitting feedback:', error);
                }
            }
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
::-webkit-scrollbar {
    display: none;
}

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

.field {
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 5px;
}

.collapsed {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    /* adjust the width to your liking */
}

.p-inputtext,
.p-select,
.p-textarea {
    width: 100%;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}
</style>
