<template>
    <div>
        <Button @click="login">Login to Webex</Button>
        <Button @click="callUser">Call</Button>
        <audio ref="remoteAudio" autoplay></audio>
    </div>
</template>

<script >
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-underscore-dangle */
/* eslint-disable jsdoc/require-jsdoc */
/* eslint-env browser */
/* global Calling */

/* eslint-disable require-jsdoc */
/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
/* eslint-disable no-global-assign */
/* eslint-disable no-multi-assign */
/* eslint-disable max-len */

import Calling from "@webex/calling";

export default {
    data() {
        return {
            webex: null,
            callings: null,
            line: null,
        };
    },
    methods: {
        async login() {
            const webexConfig = {
                credentials: { access_token: 'NWJhMTNkZGUtNGExOS00MTQ4LTkwOTUtZDJkYjI4OGZlNDhmMmI5NmM2MDQtMzUw_PE93_5aa90450-7bd4-496a-b679-2da9dbca94a5' },
                config: {
                    logger: {
                        level: 'debug' // Set the desired log level
                    },
                    meetings: {
                        reconnection: {
                            enabled: true
                        },
                        enableRtx: true
                    },
                    encryption: {
                        kmsInitialTimeout: 8000,
                        kmsMaxTimeout: 40000,
                        batcherMaxCalls: 30,
                        caroots: null
                    },
                    dss: {}
                },
            };

            // this.webex = Calling.init({ webexConfig });
            const callingConfig = {
                clientConfig: {
                    calling: true,
                    contact: true,
                    callHistory: true,
                    callSettings: true,
                    voicemail: true
                },
                callingClientConfig: {
                    discovery: { region: '', country: '' },
                    jwe: "",
                    logger: { level: 'info' },
                    serviceData: { indicator: 'calling', domain: '' }
                },
                logger: {
                    level: 'info'
                }
            };

            this.callings = await Calling.init({ webexConfig, callingConfig });
            // await new Promise(resolve => setTimeout(resolve, 1000));
            console.log('ddd', this.callings,webexConfig);
            this.callings.on("ready", async () => {

                await this.callings.register();
                this.line = Object.values(this.callings.callingClient.getLines())[0];
                this.line.on("registered", () => console.log("Line registered"));
            });
        },
        async callUser() {
            const destination = "+923102769351";
            const localAudioStream = await Calling.createMicrophoneStream({ audio: true });
            console.log(this.line);

            const call = this.line.makeCall({ type: "uri", address: destination });
            call.on("remote_media", (track) => {
                this.$refs.remoteAudio.srcObject = new MediaStream([track]);
            });
            await call.dial(localAudioStream);
        },
    },
};
</script>
