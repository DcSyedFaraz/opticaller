<template>
    <div class="audio-call-component">
        <h2>Audio Call Component</h2>

        <!-- Access Token Section -->
        <div class="section">
            <h3>Access Token</h3>
            <input v-model="accessToken" placeholder="Enter Access Token" type="text" />
            <button @click="saveAccessToken">Save Token</button>
            <p class="status">{{ tokenStatus }}</p>
        </div>

        <!-- Register Device Section -->
        <div class="section">
            <h3>Device Registration</h3>
            <button @click="registerDevice" :disabled="isDeviceRegistered">Register Device</button>
            <p class="status">{{ registrationStatus }}</p>
        </div>

        <!-- Microphone Access Section -->
        <div class="section">
            <h3>Microphone Access</h3>
            <button @click="requestMicrophoneAccess" :disabled="microphoneAccessGranted || !isDeviceRegistered">Grant
                Microphone Access</button>
            <p class="status">{{ microphoneStatus }}</p>
        </div>

        <!-- Enable BNR Section -->
        <div class="section">
            <h3>Background Noise Reduction (BNR)</h3>
            <button @click="toggleBNR" :disabled="!isDeviceRegistered || !microphoneAccessGranted">
                {{ isBNREnabled ? 'Disable BNR' : 'Enable BNR' }}
            </button>
            <p class="status">{{ bnrStatus }}</p>
        </div>

        <!-- Dial Number Section -->
        <div class="section">
            <h3>Make an Audio Call</h3>
            <input v-model="destinationNumber" placeholder="Enter Destination Number" type="text" />
            <button @click="dialNumber"
                :disabled="!isDeviceRegistered || !microphoneAccessGranted || !destinationNumber.trim()">Dial</button>
            <button @click="endCall" :disabled="!call">End Call</button>
            <p class="status">{{ callStatus }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AudioCallComponent',
    data() {
        return {
            accessToken: '',
            destinationNumber: '',
            isDeviceRegistered: false,
            microphoneAccessGranted: false,
            isBNREnabled: false,
            call: null,
            tokenStatus: 'Not saved.',
            registrationStatus: 'Not registered.',
            microphoneStatus: 'Microphone not accessed.',
            bnrStatus: 'BNR is disabled.',
            callStatus: 'No active call.',
            callingClient: null,
            calling: null,
            line: null,
            localAudioStream: null,
        };
    },
    methods: {
        /**
         * Saves the access token to localStorage and updates the status.
         */
        saveAccessToken() {
            if (this.accessToken.trim()) {
                localStorage.setItem('access-token', this.accessToken.trim());
                this.tokenStatus = 'Access token saved successfully.';
            } else {
                this.tokenStatus = 'Please enter a valid access token.';
            }
        },

        /**
         * Initializes the Calling SDK and registers the device.
         */
        async registerDevice() {
            try {
                this.registrationStatus = 'Initializing Calling SDK...';

                // Webex and Calling SDK configuration
                const webexConfig = {
                    credentials: {
                        access_token: this.accessToken.trim(),
                    },
                    config: {
                        logger: {
                            level: 'debug', // set the desired log level
                        },
                        meetings: {
                            reconnection: {
                                enabled: true,
                            },
                            enableRtx: true,
                        },
                        encryption: {
                            kmsInitialTimeout: 8000,
                            kmsMaxTimeout: 40000,
                            batcherMaxCalls: 30,
                            caroots: null,
                        },
                        dss: {},
                    },
                };

                const callingConfig = {
                    clientConfig: {
                        calling: true,
                        contact: false,
                        callHistory: false,
                        callSettings: false,
                        voicemail: false,
                    },
                    logger: {
                        level: 'info', // Ensure logger is defined to prevent errors
                    },
                    // Additional Calling configurations can be added here
                };

                // Initialize Calling SDK
                this.calling = await Calling.init({ webexConfig, callingConfig });

                // Listen for the 'ready' event to proceed with device registration
                this.calling.on('ready', async () => {
                    console.log('Calling SDK is ready.');
                    this.registrationStatus = 'Calling SDK initialized. Registering device...';
                    console.log(this.calling,this.line);

                    // Register the device
                    await this.calling.register();
                    this.isDeviceRegistered = true;
                    this.registrationStatus = 'Device registered successfully.';

                    // Obtain the Calling Client and Line
                    this.callingClient = this.calling.callingClient;
                    const lines = Object.values(this.callingClient.getLines());

                    if (lines.length > 0) {
                        this.line = lines[0];
                        console.log('Using line:', this.line);
                    } else {
                        throw new Error('No available lines to make calls.');
                    }
                });

                // Handle initialization errors
                this.calling.on('error', (error) => {
                    console.error('Calling SDK error:', error);
                    this.registrationStatus = `SDK Error: ${error.message}`;
                });

            } catch (error) {
                console.error('Error during device registration:', error);
                this.registrationStatus = `Error: ${error.message}`;
            }
        },

        /**
         * Requests access to the user's microphone.
         */
        async requestMicrophoneAccess() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.microphoneAccessGranted = true;
                this.microphoneStatus = 'Microphone access granted.';
                this.localAudioStream = stream;
            } catch (error) {
                console.error('Microphone access denied:', error);
                this.microphoneStatus = 'Microphone access denied.';
            }
        },

        /**
         * Toggles the Background Noise Reduction (BNR) effect.
         */
        async toggleBNR() {
            if (!this.localAudioStream) {
                this.bnrStatus = 'Microphone not accessed.';
                return;
            }

            try {
                const effectKind = 'noise-reduction-effect'; // Ensure this matches the SDK's effect kind
                let effect = this.localAudioStream.getEffectByKind(effectKind);

                if (!effect) {
                    // Create and add the BNR effect if it doesn't exist
                    const options = {}; // Add any required options here
                    effect = await Calling.createNoiseReductionEffect(options);
                    await this.localAudioStream.addEffect(effect);
                    console.log('BNR effect created and added.');
                }

                if (this.isBNREnabled) {
                    await effect.disable();
                    this.isBNREnabled = false;
                    this.bnrStatus = 'BNR is disabled.';
                } else {
                    await effect.enable();
                    this.isBNREnabled = true;
                    this.bnrStatus = 'BNR is enabled.';
                }
            } catch (error) {
                console.error('Error toggling BNR:', error);
                this.bnrStatus = `Error: ${error.message}`;
            }
        },

        /**
         * Initiates an audio call to the specified destination number.
         */
        async dialNumber() {
            if (!this.destinationNumber.trim()) {
                this.callStatus = 'Please enter a destination number.';
                return;
            }

            if (!this.line) {
                this.callStatus = 'No line available to make a call.';
                return;
            }

            try {
                this.callStatus = `Dialing ${this.destinationNumber}...`;

                const callOptions = {
                    type: 'uri', // Use 'uri' for phone numbers; adjust if necessary
                    address: this.destinationNumber.trim(),
                };

                // Make the call using the selected line
                this.call = this.line.makeCall(callOptions);

                // Attach the local audio stream to the call
                if (this.localAudioStream) {
                    await this.call.attachMedia(this.localAudioStream);
                }

                // Handle call events
                this.call.on('established', (callObj) => {
                    console.log('Call established:', callObj);
                    this.callStatus = `Call to ${this.destinationNumber} established.`;
                });

                this.call.on('disconnected', () => {
                    console.log('Call disconnected.');
                    this.callStatus = 'Call disconnected.';
                    this.call = null;
                });

                this.call.on('error', (error) => {
                    console.error('Call error:', error);
                    this.callStatus = `Call error: ${error.message}`;
                });

            } catch (error) {
                console.error('Error making call:', error);
                this.callStatus = `Error: ${error.message}`;
            }
        },

        /**
         * Ends the active call.
         */
        async endCall() {
            if (this.call) {
                try {
                    await this.call.end();
                    this.callStatus = 'Call ended.';
                    this.call = null;
                } catch (error) {
                    console.error('Error ending call:', error);
                    this.callStatus = `Error: ${error.message}`;
                }
            }
        },
    },
    mounted() {
        // Automatically load the access token from localStorage if available
        const savedToken = localStorage.getItem('access-token');
        if (savedToken) {
            this.accessToken = savedToken;
            this.tokenStatus = 'Access token loaded from storage.';
        }
    },
};
</script>

<style scoped>
.audio-call-component {
    max-width: 600px;
    margin: auto;
    padding: 1rem;
    font-family: Arial, sans-serif;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.section {
    margin-bottom: 2rem;
}

.section h3 {
    margin-bottom: 0.5rem;
}

.input-group {
    display: flex;
    flex-direction: column;
}

input[type="text"],
input[type="email"] {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

button:not(:disabled) {
    background-color: #1a73e8;
    color: white;
}

.status {
    margin-top: 0.5rem;
    color: #555;
}
</style>
