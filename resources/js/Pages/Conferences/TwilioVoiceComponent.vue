<template>
    <div class="twilio-voice">
        <!-- <h3>Joining Conference: {{ conference.friendlyName }}</h3> -->
        <Button @click="connect" :disabled="isConnecting || connected" class="p-button p-component">
            {{ isConnecting ? 'Connecting...' : 'Join Conference' }}
        </Button>
        <Button @click="disconnect" :disabled="!connected" class="p-button p-component">
            Disconnect
        </Button>
        <p v-if="connectionStatus">{{ connectionStatus }}</p>
    </div>
</template>

<script>
import { Device } from '@twilio/voice-sdk';
import axios from 'axios';

export default {
    props: {
        conference: {
            type: Object,
            required: true,
        },
    },
    emits: ['conference-joined','call-connected'],
    data() {
        return {

            isConnecting: false,
            connected: false,
            connectionStatus: '',
            isDeviceReady: false,
            connection: null,
            // identity: "user_" + Date.now() + "_" + Math.floor(Math.random() * 1000),
            // devices: null,
            // activeConnection: null,
        };
    },
    beforeCreate() {
        this.device = null;
        this.connection = null
    },
    methods: {
        generateIdentity() {
            const userName = this.$page.props.auth.user.name || 'user';
            // Replace spaces and non-alphanumeric characters with underscores
            const sanitizedUserName = userName.replace(/[^a-zA-Z0-9]/g, '_');
            const uniqueSuffix = Date.now() + '_' + Math.floor(Math.random() * 1000);
            return `user_${sanitizedUserName}_${uniqueSuffix}`;
        },
        async requestAudioPermissions() {
            try {
                console.log("Requesting audio device permissions...");
                await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                console.log("Audio device permissions granted.");
            } catch (error) {
                console.log(`Audio Permissions Error: ${error.message}`);
                throw new Error("Audio device permissions are required to make calls.");
            }
        },
        async initializeDevice() {
            try {
                await this.requestAudioPermissions();
                const identity = this.generateIdentity();
                const response = await axios.get(route('admin_token', { identity: identity }));
                const token = response.data.token;
                console.log('Twilio Access Token:', token); // Debugging

                this.devices = new Device(token, {
                    debug: true,
                    codecPreferences: ['opus', 'pcmu'],
                    fakeLocalDTMF: false,
                    enableRingingState: true,
                });

                // console.log('Twilio Device:', this.devices); // Debugging

                this.devices.on("ready", () => {
                    this.onDeviceReady
                });
                // this.devices.on('ready', this.onDeviceReady);
                this.devices.on('error', this.onDeviceError);
                this.devices.on('connect', this.onDeviceConnect);
                this.devices.on('disconnect', this.onDeviceDisconnect);

                await this.devices.register();
                console.log('Device registered successfully');
            } catch (error) {
                // console.error('Failed to initialize Twilio Device:', error);
                // this.connectionStatus = `Initialization failed: ${error.message}`;
            }
        },
        onDeviceReady() {
            console.log('Twilio Device is ready');
            this.isDeviceReady = true;
            this.connectionStatus = 'Device is ready';
        },
        onDeviceError(error) {
            console.error('Twilio Device Error:', error);
            this.connectionStatus = `Error: ${error.message}`;
        },
        onDeviceConnect(conn) {
            console.log('Connected to Twilio');
            this.connected = true;
            this.connectionStatus = 'Connected';
        },
        onDeviceDisconnect() {
            console.log('Disconnected from Twilio');
            this.connected = false;
            this.connectionStatus = 'Disconnected';
        },
        hangUp() {
            if (this.connection) {
                // this.log("Hanging up the call.");
                console.log(this.connection);

                this.connection.disconnect();
            } else {
                this.log("No active call to hang up.");
            }
        },
        async connectToConference() {
            if (!this.devices) {
                console.error('Twilio Device not initialized');
                this.connectionStatus = 'Device not initialized';
                return;
            }

            // if (!this.isDeviceReady) {
            //     console.error('Twilio Device not ready');
            //     this.connectionStatus = 'Device not ready';
            //     console.log('Twilio Device:', this.devices);
            //     return;
            // }

            this.isConnecting = true;
            this.connectionStatus = 'Connecting to conference...';

            console.log('Conference:', this.conference);
            const params = {
                To: this.conference.friendlyName,
            };

            this.connection = await this.devices.connect({
                params: {
                    To: this.conference.friendlyName,
                    AgentId: 'agentId',
                },
            });
            console.log('Twilio Connection:', this.connection); // Debugging

            if (this.connection && typeof this.connection.on === 'function') {
                this.connection.on('accept', this.onCallAccept);
                this.connection.on('disconnect', this.onCallDisconnect);
                this.connection.on('error', this.onConnectionError);
            } else {
                console.error('Connection object does not have an on method:', this.connection);
                this.connectionStatus = 'Failed to connect to conference';
                this.isConnecting = false;
            }
        },
        onCallAccept(conn) {
            console.log('Call accepted');
            console.log(conn);

            this.connectionStatus = 'Call accepted';
            this.$emit("call-connected", conn.parameters.To);

        },
        onCallDisconnect() {
            console.log('Call disconnected');
            this.connected = false;
            this.connectionStatus = 'Disconnected';
            this.$emit("call-disconnected");

            this.isConnecting = false;
        },
        onConnectionError(error) {
            console.error('Connection Error:', error);
            this.connectionStatus = `Connection Error: ${error.message}`;
            this.isConnecting = false;
        },
        connect() {
            this.connectToConference();
        },
        disconnect() {
            if (this.devices) {
                this.devices.disconnectAll();
            }
            this.connectionStatus = 'Disconnected';
        },
    },
    mounted() {
        this.initializeDevice();

    },
    beforeUnmount() {
        if (this.devices) {
            this.devices.destroy();
        }
    },
};
</script>



<style scoped>
.twilio-voice {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

button {
    margin-right: 10px;
}
</style>
