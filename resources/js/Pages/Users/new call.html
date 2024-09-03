<template>
    <AuthenticatedLayout>

        <div>
            <h2>Webex Call Application</h2>

            <!-- Login Section -->
            <div v-if="!accessToken">
                <button @click="login">Login with Webex</button>
            </div>

            <!-- Call Section -->
            <div v-if="accessToken">
                <div>
                    <input v-model="phoneNumber" placeholder="Enter phone number" />
                    <button @click="makeCall">Call</button>
                </div>

                <div v-if="callStatus">
                    <p>Status: {{ callStatus }}</p>
                    <button @click="hangUp" v-if="inCall">Hang Up</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import Webex from 'webex';

export default {
    data() {
        return {
            webex: null,
            accessToken: null,
            phoneNumber: '+923102769351',
            callStatus: '',
            inCall: false,
            call: null,
        };
    },
    methods: {
        login() {
            const clientId = 'C8ddeae7e2aebd102a2ca30543c36f6275c4c704ab9c7a024ac100e7639720ad1';
            const redirectUri = 'http://127.0.0.1:8000/webex/callback';
            const authUrl = `https://webexapis.com/v1/authorize?client_id=${clientId}&response_type=code&redirect_uri=${redirectUri}&scope=spark:all&state=12345`;

            window.location.href = authUrl;
        },
        async handleCallback() {
            const urlParams = new URLSearchParams(window.location.search);
            const code = urlParams.get('code');
            console.log(code, 'code');

            if (code) {
                const clientId = 'C8ddeae7e2aebd102a2ca30543c36f6275c4c704ab9c7a024ac100e7639720ad1';
                const clientSecret = '120e1c5aa849e3b3fe0cf6054fe654fa80d15d335d31479fad8d6d53cc227f99';
                const redirectUri = 'http://127.0.0.1:8000/webex/callback';

                const params = new URLSearchParams();
                params.append('grant_type', 'authorization_code');
                params.append('client_id', clientId);
                params.append('client_secret', clientSecret);
                params.append('code', code);
                params.append('redirect_uri', redirectUri);
                params.append('scope', 'spark:all');

                await axios.post('https://webexapis.com/v1/access_token', params, {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                })
                    .then(response => {
                        const data = response.data;
                        console.log(data, 'data');

                        this.accessToken = data.access_token;
                        // do something with the data
                    })
                    .catch(error => {
                        console.error(error);
                    });

                if (this.accessToken) {
                    this.initializeWebex();
                } else {
                    console.error('Failed to get access token.');
                }
            }
        },
        initializeWebex() {
            try {
                this.webex = Webex.init({
                    credentials: {
                        access_token: this.accessToken,
                    },
                    config: {
                        meetings: {
                            reconnection: {
                                enabled: true,
                                delay: 2000,
                                maxAttempts: 10,
                            },
                        },
                        timeout: 60000,
                        logger: {
                            level: 'debug', // or whatever level you need
                        },
                    },
                });

                console.log(this.webes, 'webex', this.accessToken, Webex);

                if (!this.webex || !this.webex.meetings) {
                    console.error('Webex SDK failed to initialize.');
                }
            } catch (error) {
                console.error('Error initializing Webex SDK:', error);
            }
        },
        async makeCall() {
            if (!this.phoneNumber) {
                alert('Please enter a phone number.');
                return;
            }

            if (!this.webex || !this.webex.meetings) {
                console.error('Webex SDK is not initialized properly.');
                return;
            }

            try {
                this.callStatus = 'Calling...';
                this.inCall = true;

                // Place the call
                this.call = await this.webex.meetings.create(this.phoneNumber);
                await this.call.join();
                this.callStatus = 'In call';

                // Listen for call events
                this.call.on('disconnected', () => {
                    this.callStatus = 'Call ended';
                    this.inCall = false;
                });
            } catch (error) {
                console.error('Error making call:', error);
                this.callStatus = 'Error occurred';
                this.inCall = false;
            }
        },
    },
    mounted() {
        this.handleCallback();
    },
};
</script>

<style scoped>
h2 {
    margin-bottom: 1rem;
}

input {
    margin-right: 0.5rem;
}

button {
    margin-top: 1rem;
    margin-right: 0.5rem;
}
</style>
