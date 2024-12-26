<template>
    <Head title="Active Conferences" />
    <AuthenticatedLayout>
        <div class="p-card">
            <h2>Active Conferences {{ this.$page.props.auth.user.name }}</h2>

            <DataTable :value="conferences" :paginator="true" :rows="5" :loading="loading">
                <Column field="friendlyName" header="Conference Name"></Column>
                <Column field="sid" header="Conference SID"></Column>
                <Column header="Action" body="actionTemplate">
                    <template #body="{ data }">
                        <Button severity="success" label="Join" icon="pi pi-phone" class="p-button-primary"
                            @click="joinConference(data)" />
                    </template>
                </Column>
                <template #empty>
                    <tr>
                        <td colspan="3">No active conferences found.</td>
                    </tr>
                </template>
            </DataTable>

            <!-- Twilio Voice Component -->
            <TwilioVoiceComponent v-if="selectedConference" :conference="selectedConference" ref="twilioVoiceComponent"
                @call-connected="handleCallConnected" @call-disconnected="handleCallDisconnected" />

            <Dialog header="Call in Progress" v-model:visible="showActiveCallDialog" :closable="false"
                class="w-11/12 md:w-1/3 p-6">
                <template #header>
                    <div class="flex items-center justify-between bg-gray-100 cursor-move" style="user-select: none;">
                        <span class="font-semibold text-gray-800">Call in Progress &nbsp; </span>
                        <i class="pi pi-bars text-gray-600"></i>
                    </div>
                </template>
                <div class="flex flex-col items-center space-y-4">
                    <!-- Call Information -->
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-phone text-3xl text-blue-500"></i>
                        <p class="text-lg font-semibold">Calling: {{ activeCallNumber }}</p>
                    </div>

                    <!-- Call Duration (Visible After Call is Accepted) -->
                    <!-- <div class="flex items-center space-x-2">
                        <i class="pi pi-clock text-2xl text-gray-600"></i>
                        <p class="text-md font-medium">Duration: {{ formattedCallDuration }}</p>
                    </div> -->

                    <!-- Hang Up Button -->
                    <div class="flex space-x-4 mt-6">
                        <Button label="Hang Up" icon="pi pi-phone-slash" class="p-button-danger p-button-rounded"
                            @click="hangUp" />
                    </div>
                </div>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TwilioVoiceComponent from './TwilioVoiceComponent.vue'; // Ensure correct path
// import TwilioVoiceComponent from './../Users/TwilioCallComponent.vue'; // Ensure correct path

export default {
    props: {
        conferences: {
            type: Array,
            default: () => [],
        },
    },
    components: {
        TwilioVoiceComponent,
    },
    data() {
        return {
            loading: false,
            selectedConference: null,
            activeCallNumber: null,
            showActiveCallDialog: false,
            callDuration: 0,
            // Add any other reactive properties here
        };
    },
    methods: {
        /**
         * Joins a selected conference.
         * @param {Object} conference - The conference to join.
         */
        joinConference(conference) {
            this.selectedConference = conference;
            console.log(conference);
            // You can add additional logic here if needed
        },
        hangUp() {
            this.$refs.twilioVoiceComponent.hangUp();
            this.showActiveCallDialog = false;
            this.activeCallNumber = "";
        },
        /**
         * Handles the event when a call is connected.
         * @param {String} toNumber - The number that was called.
         */
        handleCallConnected(toNumber) {
            this.activeCallNumber = toNumber;
            this.showActiveCallDialog = true;
            this.callDuration = 0;
            // Uncomment and implement the timer if needed
            // this.startCallDurationTimer();
        },
        handleCallDisconnected() {
            this.showActiveCallDialog = false;
            this.activeCallNumber = "";
        },
        /**
         * Handles the event when a conference is successfully joined.
         */
        handleConferenceJoined() {
            // Reset the selected conference
            this.selectedConference = null;
            // Optionally, refresh the conference list
            // this.fetchActiveConferences();
        },

        /**
         * (Optional) Fetches the list of active conferences.
         */
        // fetchActiveConferences() {
        //     // Implement your API call or logic to fetch conferences
        // },

        /**
         * (Optional) Starts a timer to track call duration.
         */
        // startCallDurationTimer() {
        //     // Implement your timer logic here
        // },
    },
    mounted() {
        // Fetch the active conferences when the component is mounted
        // this.fetchActiveConferences();
    },
};

</script>

<style scoped>
.p-card {
    margin: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
