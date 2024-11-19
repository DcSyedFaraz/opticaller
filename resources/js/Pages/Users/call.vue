<!-- src/components/AircallDialer.vue -->
<template>
    <div>
        <!-- Button to Open Dialer Dialog -->
        <Button label="Open Dialer" icon="pi pi-phone" @click="handleDialogOpen" />

        <!-- PrimeVue Dialog for Aircall Dialer -->
        <Dialog header="Aircall Dialer" :visible="showDialer" modal :style="{ width: '600px' }">
            <!-- Container for Aircall Phone UI -->
            <div id="phone"></div>

            <!-- Optional: Custom Dial Pad or Controls -->
            <div class="p-mt-3">
                <InputText v-model="phoneNumber" placeholder="Enter phone number" @keyup.enter="callUser"
                    style="width: 100%;" />
                <Button label="Call" icon="pi pi-phone-slash" :disabled="!isLoggedIn || !phoneNumber" class="p-mt-2"
                    @click="callUser" />
            </div>

            <!-- Audio Element for Remote Audio -->
            <audio ref="remoteAudio" autoplay></audio>
        </Dialog>
    </div>
</template>

<script>
import { Device } from '@twilio/voice-sdk';

export default {
    name: 'AircallDialer',

    data() {
        return {
            showDialer: false,
            phoneNumber: '',
            aircall: null,
            line: null,
            isLoggedIn: false,
        };
    },
    methods: {
        initializeAircall() {
            const phoneElement = document.getElementById('phone');
            // console.log(phoneElement);

            // const phone = new AircallPhone({
            //     domToLoadPhone: '#phone',
            //     onLogin: settings => {
            //         // ...
            //     },
            //     onLogout: () => {
            //         // ...
            //     }
            // });
            // console.log(phone);
            this.aircall = new AircallPhone({
                domToLoadPhone: '#phone', // Load Aircall UI into the #phone div
                onLogin: (settings) => {
                    console.log('Aircall Logged In:', settings);
                    this.isLoggedIn = true;
                    // Use the first available line
                    if (settings.lines && settings.lines.length > 0) {
                        this.line = settings.lines[0];
                    }
                },
                onLogout: () => {
                    console.log('Aircall Logged Out');
                    this.isLoggedIn = false;
                    this.line = null;
                },
                // Optional: Additional Callbacks
                onCallStart: (call) => {
                    console.log('Call started:', call);
                },
                onCallEnd: (call) => {
                    console.log('Call ended:', call);
                },
                onError: (error) => {
                    console.error('Aircall Error:', error);
                },
            });
            this.aircall.isLoggedIn(response => {
                console.log(response);

            });
        },
        async callUser() {
            const payload = {
                phone_number: '+33123456789'
            };
            this.aircall.send(
                'dial_number',
                payload,
                (success, data) => {
                    // ...
                }
            );
            // if (!this.line) {
            //     console.error('No active line available.');
            //     return;
            // }

            // const destination = this.phoneNumber;
            // try {
            //     const localAudioStream = await this.line.createMicrophoneStream({ audio: true });
            //     const call = this.line.makeCall({ type: 'uri', address: destination });

            //     call.on('remote_media', (track) => {
            //         this.$refs.remoteAudio.srcObject = new MediaStream([track]);
            //     });

            //     await call.dial(localAudioStream);
            //     console.log(`Calling ${destination}...`);
            // } catch (error) {
            //     console.error('Error initiating call:', error);
            // }
        },
        handleDialogClose() {
            // Optional: Handle any cleanup when the dialog is closed
            // For example, hang up ongoing calls or reset states
            this.showDialer = false;
        },
        handleDialogOpen() {
            this.showDialer = true;
            setTimeout(() => {
                this.initializeAircall();
            }, 500);
        },
    },
    mounted() {
    },
    beforeUnmount() {
        if (this.aircall) {
            this.aircall.logout();
        }
    },
};
</script>

<style scoped></style>
