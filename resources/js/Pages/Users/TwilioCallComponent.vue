<template>
    <!-- No visible UI elements as this component handles call logic internally -->
</template>

<script>
import { Device } from "@twilio/voice-sdk";
import axios from "axios";

export default {
    name: "TwilioCallComponent",
    props: {
        phoneNumber: {
            type: String,
            default: "",
        },
        addressID: {
            type: Number,
            default: 0,
        },
        isPaused: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            deviceInitialized: false,
            initializing: false,
            calling: false,
            identity: null,
            logs: [],
        };
    },
    beforeCreate() {
        this.device = null;
        this.activeConnection = null
    },
    watch: {
        // phoneNumber(newNumber) {
        //     if (newNumber && this.deviceInitialized && !this.isPaused) {
        //         this.makeCall(newNumber);
        //     }
        // },
        isPaused(newVal) {
            if (newVal && this.calling) {
                this.hangUp();
            }
        },
    },
    mounted() {
        this.initializeDevice();
        this.generateIdentity();
    },
    beforeDestroy() {
        if (this.device) {
            this.device.destroy();
            this.device = null;
            this.log("Twilio Device destroyed.");
        }
    },
    methods: {
        triggerCall() {
            if (this.phoneNumber) {
                // console.log(this.phoneNumber, 'number2');

                setTimeout(() => {
                    // console.log(this.phoneNumber, 'number3');
                    this.makeCall(this.phoneNumber);
                }, 500);
            } else {
                this.log("No valid phone number available to make a call.");
            }
        },
        log(message) {
            this.logs.push(message);
            // console.log(`[TwilioCallComponent]: ${message}`);
        },
        async initializeDevice() {
            this.initializing = true;
            try {
                await this.requestAudioPermissions();
                // const identity = this.generateIdentity();
                const token = await this.fetchTwilioToken(this.identity);
                this.setupDevice(token, this.identity);
                this.deviceInitialized = true;
                this.log("Twilio Device initialized successfully.");
                // Automatically make a call if phoneNumber is provided and not paused
                // if (this.phoneNumber && !this.isPaused) {
                //     this.makeCall(this.phoneNumber);
                // }
            } catch (error) {
                this.log(`Initialization Error: ${error.message}`);
                this.$emit("twilio-error", error.message);
            } finally {
                this.initializing = false;
            }
        },
        async requestAudioPermissions() {
            try {
                this.log("Requesting audio device permissions...");
                await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                this.log("Audio device permissions granted.");
            } catch (error) {
                this.log(`Audio Permissions Error: ${error.message}`);
                throw new Error("Audio device permissions are required to make calls.");
            }
        },
        generateIdentity() {
            const userName = this.$page.props.auth.user.name || 'user';
            // Replace spaces and non-alphanumeric characters with underscores
            const sanitizedUserName = userName.replace(/[^a-zA-Z0-9]/g, '_');
            const uniqueSuffix = Date.now() + '_' + Math.floor(Math.random() * 1000);
            this.identity = `${sanitizedUserName}_${uniqueSuffix}`;
        },
        async fetchTwilioToken(identity) {
            try {
                this.log("Fetching Twilio Access Token...");
                const response = await axios.get(route("refresh_token", { identity }));
                if (response.status === 200 && response.data.token) {
                    this.log("Received Twilio Access Token.");
                    return response.data.token;
                } else {
                    throw new Error("Invalid token response from server.");
                }
            } catch (error) {
                this.log(`Token Fetching Error: ${error.message}`);
                throw error;
            }
        },
        setupDevice(token, identity) {
            this.device = new Device(token, {
                codecPreferences: ["opus", "pcmu"],
                fakeLocalDTMF: false,
                enableRingingState: true,
            });
            console.log('Twilio Device:', this.device);
            // Event: Device Ready
            this.device.on("ready", () => {
                this.log("Twilio Device is ready.");
            });

            // Event: Device Registered
            this.device.on("registered", () => {
                this.log("Twilio Device registered.");
            });

            // Event: Device Unregistered
            this.device.on("unregistered", () => {
                this.log("Twilio Device unregistered.");
            });

            // Event: Device Error
            this.device.on("error", (error) => {
                this.log(`Device Error: ${error.message}`);
                this.$emit("twilio-error", error.message);
            });

            // Event: Incoming Call
            this.device.on("incoming", (connection) => {
                this.log(`Incoming call from: ${connection.parameters.From}`);
                if (!this.isPaused) {
                    this.emitIncomingCall(connection.parameters.From, connection);
                } else {
                    this.log("Agent is on break. Rejecting incoming call.");
                    connection.reject();
                }
            });

            // Event: Connect
            this.device.on("connect", (connection) => {
                this.log("Call connected.");
                this.calling = true;
                this.activeConnection = connection;
                this.$emit("call-connected", connection.parameters.To);
            });

            // Event: Disconnect
            this.device.on("disconnect", (connection) => {
                this.log("Call disconnected.");
                this.calling = false;
                this.activeConnection = null;
                this.$emit("call-disconnected");
            });

            // Initialize the device
            this.device.register();
        },
        emitIncomingCall(from, connection) {
            this.$emit("incoming-call", { from, connection });
        },
        async makeCall(toNumber) {
            if (this.calling) {
                this.log("Already on a call. Cannot make another call.");
                return;
            }

            try {
                this.log(`Initiating call to: ${toNumber}`);
                console.log(this.addressID, 'addressID');
                this.activeConnection = await this.device.connect({
                    params: {
                        To: toNumber,
                        addressID: this.addressID,
                    },
                });

                this.$emit("call-connected", toNumber);

                console.log(this.activeConnection);
                this.activeConnection.on("accept", () => {
                    this.log("Call accepted.");
                    this.$emit("call-accepted");

                    if (this.activeConnection.status() == 'accept') {
                        this.log("Call picked up.");
                    }
                });

                this.activeConnection.on("disconnect", () => {
                    this.log("Call ended.");
                    this.calling = false;
                    // this.activeConnection = null;
                    this.$emit("call-disconnected");
                });

                this.activeConnection.on("error", (error) => {
                    this.log(`Connection Error: ${error.message}`);
                    this.$emit("twilio-error", error.message);
                    this.calling = false;
                    // this.activeConnection = null;
                });
            } catch (error) {
                this.log(`Make Call Error: ${error.message}`);
                this.$emit("twilio-error", error.message);
            }
        },
        hangUp() {
            if (this.activeConnection) {
                this.log("Hanging up the call.");
                console.log(this.activeConnection);
                // this.updateConferenceStatus('completed');
                this.activeConnection.disconnect();
            } else {
                this.log("No active call to hang up.");
            }
        },
        async updateConferenceStatus(status) {
            try {
                // Ensure you have the conference identifier (SID or Name)
                const conferenceIdentifier = this.identity
                    ? {
                        // conferenceSid: this.selectedConference.sid,
                        // or
                        conferenceName: `${this.identity}`,
                    }
                    : {};

                const response = await axios.post(route('conference.updateStatus'), {
                    status: status,
                    ...conferenceIdentifier,
                });

                if (response.status === 200) {
                    this.log("Conference status updated successfully.");
                } else {
                    this.log("Failed to update conference status.");
                }
            } catch (error) {
                console.error("Error updating conference status:", error);
                this.log("Error updating conference status.");
            }
        },

    },
};
</script>

<style scoped>
/* No specific styles as this component has no visible UI elements */
</style>
