<template>
    <!-- No visible UI elements as this component handles call logic internally -->
</template>

<script>
import axios from "axios";

export default {
    name: "CloudTalkCallComponent",
    props: {
        phoneNumber: {
            type: String,
            default: "",
        },
        isPaused: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            calling: false,
            logs: [],
            callId: null, // To track the current call
        };
    },
    watch: {
        isPaused(newVal) {
            if (newVal && this.calling) {
                this.hangUp();
            }
        },
    },
    mounted() {
        // Automatically make a call if phoneNumber is provided and not paused
        if (this.phoneNumber && !this.isPaused) {
            this.makeCall(this.phoneNumber);
        }

        // Listen for broadcasted events
        window.Echo.channel('calls')
            .listen('.call.initiated', (e) => {
                this.log(`Call initiated to: ${e.phoneNumber}`);
            })
            .listen('.call.ended', (e) => {
                this.log(`Call ended with ID: ${e.callId}`);
                this.calling = false;
            })
            .listen('.call.incoming', (e) => {
                this.log(`Incoming call from: ${e.fromNumber}`);
                this.$emit("incoming-call", { from: e.fromNumber });
            });
    },
    methods: {
        log(message) {
            this.logs.push(message);
            console.log(`[CloudTalkCallComponent]: ${message}`);
        },
        async makeCall(toNumber) {
            if (this.calling) {
                this.log("Already on a call. Cannot make another call.");
                return;
            }

            try {
                this.log(`Initiating call to: ${toNumber}`);

                const response = await axios.post('/api/calls/initiate', {
                    phoneNumber: toNumber,
                });

                if (response.status === 200) {
                    this.calling = true;
                    this.log(`Call initiated to ${toNumber}.`);
                    this.$emit("call-connected", toNumber);
                    // Optionally, store callId if returned from backend
                } else {
                    this.log(`Failed to initiate call to ${toNumber}.`);
                    this.$emit("call-error", 'Failed to initiate call.');
                }
            } catch (error) {
                this.log(`Make Call Error: ${error.response.data.error || error.message}`);
                this.$emit("call-error", error.response.data.error || error.message);
            }
        },
        async hangUp() {
            if (this.calling && this.callId) {
                this.log("Ending the call.");

                try {
                    const response = await axios.post('/api/calls/hangup', {
                        callId: this.callId,
                    });

                    if (response.status === 200) {
                        this.calling = false;
                        this.log("Call ended.");
                        this.$emit("call-disconnected");
                    } else {
                        this.log("Failed to end the call.");
                        this.$emit("call-error", 'Failed to end the call.');
                    }
                } catch (error) {
                    this.log(`Hang Up Error: ${error.response.data.error || error.message}`);
                    this.$emit("call-error", error.response.data.error || error.message);
                }
            } else {
                this.log("No active call to hang up.");
            }
        },
    },
};
</script>

<style scoped>
/* No specific styles as this component has no visible UI elements */
</style>
