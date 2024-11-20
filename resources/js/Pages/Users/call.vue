<template>
    <div>
        <h2>Twilio Voice Call</h2>

        <!-- Initialize Twilio Device -->
        <div v-if="!deviceInitialized">
            <Button @click="initializeDevice" :disabled="initializing">
                {{ initializing ? 'Initializing...' : 'Initialize Device' }}
            </Button>
        </div>

        <!-- Call Controls -->
        <div v-else>
            <input v-model="phoneNumber" placeholder="Enter phone number" />
            <Button @click="makeCall" :disabled="calling">Call</Button>
            <Button @click="hangUp" :disabled="!calling">Hang Up</Button>
        </div>

        <!-- Logs -->
        <div>
            <h3>Logs</h3>
            <ul>
                <li v-for="(log, index) in logs" :key="index">{{ log }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
import { Device } from '@twilio/voice-sdk';

export default {
    name: 'TwilioCallComponent',
    data() {
        return {
            phoneNumber: '+923472783689',
            calling: false,
            logs: [],
            deviceReady: false,
            deviceInitialized: false,
            initializing: false,
            identity: '',
        };
    },
    methods: {
        generateIdentity() {
            return 'user_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        },
        async initializeDevice() {
            this.initializing = true;
            this.identity = this.generateIdentity();
            this.logs.push('Generating identity: ' + this.identity);
            console.log('Generating identity:', this.identity);

            try {
                await this.requestAudioPermissions();
                const identity = encodeURIComponent(this.identity);
                // const response = await fetch(`/api/token?identity=${encodeURIComponent(this.identity)}`);
                const response = await fetch(route('refresh_token', { identity }));
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                const token = data.token;
                console.log('Received Token:', token);
                this.logs.push('Received Twilio Access Token.');

                this.setupDevice(token);
                this.deviceInitialized = true;
                this.logs.push('Twilio Device initialized successfully.');
            } catch (error) {
                this.logs.push('Failed to initialize Twilio Device: ' + error.message);
                console.error('Initialization Error:', error);
            } finally {
                this.initializing = false;
            }
        },
        async requestAudioPermissions() {
            try {
                this.logs.push('Requesting audio device permissions...');
                console.log('Requesting audio device permissions...');
                await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                this.logs.push('Audio device permissions granted.');
                console.log('Audio device permissions granted.');
            } catch (error) {
                this.logs.push('Audio device permissions denied: ' + error.message);
                console.error('Audio Permissions Error:', error);
                throw new Error('Audio device permissions are required to make calls.');
            }
        },
        setupDevice(token) {
            this.device = new Device(token, {
                enableImprovedSignalingErrorPrecision: true,
            });
            console.log('Twilio Device Initialized:', this.device);

            this.device.on('registered', () => {
                this.logs.push('Device is ready.');
                this.deviceReady = true;
                console.log('Twilio Device is ready.');
            });

            this.device.on('error', (error) => {
                this.logs.push('Device error: ' + error.message);
                console.error('Twilio Device Error:', error);
            });

            this.device.on('connect', (connection) => {
                this.logs.push('Connected to call.');
                this.calling = true;
                console.log('Call Connected:', connection);
            });

            this.device.on('disconnect', (connection) => {
                this.logs.push('Call disconnected.');
                this.calling = false;
                console.log('Call Disconnected:', connection);
            });

            this.device.on('cancel', (connection) => {
                this.logs.push('Incoming call canceled.');
                console.log('Incoming Call Canceled:', connection);
            });

            this.device.on('incoming', (connection) => {
                this.logs.push(`Incoming call from ${connection.parameters.From}`);
                console.log('Incoming Call:', connection);
                connection.accept();
            });
        },
        makeCall() {
            // if (!this.deviceReady || !this.phoneNumber) {
            //     this.logs.push('Device not ready or phone number not entered.');
            //     return;
            // }

            const params = { To: this.phoneNumber };
            this.logs.push('Calling ' + this.phoneNumber + '...');
            console.log('Making Call to:', this.phoneNumber);

            const connection = this.device.connect({
                params: {
                    To: this.phoneNumber,
                },
            });
            console.log(connection, 'con');

            connection.on('accept', () => {
                this.logs.push('Call accepted.');
                this.calling = true;
                console.log('Call Accepted:', connection);
            });

            connection.on('disconnect', () => {
                this.logs.push('Call ended.');
                this.calling = false;
                console.log('Call Ended:', connection);
            });

            connection.on('error', (error) => {
                this.logs.push('Connection error: ' + error.message);
                this.calling = false;
                console.error('Connection Error:', error);
            });
        },
        hangUp() {
            if (this.device) {
                this.device.disconnectAll();
                this.logs.push('Call hung up.');
                this.calling = false;
                console.log('Call Hung Up.');
            }
        },
    },
    beforeDestroy() {
        if (this.device) {
            this.device.destroy();
            this.device = null;
            this.logs.push('Twilio Device destroyed.');
            console.log('Twilio Device Destroyed.');
        }
    },
    // Add a non-reactive property to store the Twilio Device instance
    beforeCreate() {
        this.device = null;
    },
};
</script>

<style scoped>
/* Component-specific styles */
Button {
    padding: 8px 16px;
    margin-right: 8px;
    margin-top: 8px;
}

input {
    padding: 8px;
    margin-right: 8px;
    width: 250px;
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    background: #f0f0f0;
    margin-bottom: 4px;
    padding: 4px 8px;
    border-radius: 4px;
}
</style>
