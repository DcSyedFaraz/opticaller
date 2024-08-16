<template>
    <AuthenticatedLayout>
        <div>
            <h1>Webex Calling Integration</h1>
            <Button @click="authenticate" label="Authenticate with Webex" icon="pi pi-phone" />
            <div>
                <input v-model="destination" placeholder="Enter phone number" />
                <Button label="Make Call" icon="pi pi-phone" @click="makeCall" />
            </div>
            <video ref="localVideo" autoplay playsinline></video>
            <video ref="remoteVideo" autoplay playsinline></video>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    data() {
        return {
            authenticated: false,
            destination: '+923102769351',
            localStream: null,
            remoteStream: null,
            peerConnection: null,
        };
    },
    methods: {
        authenticate() {
            window.location.href = '/webex/authorize';
        },
        requestMediaAccess() {
            navigator.mediaDevices.enumerateDevices()
                .then(devices => {
                    devices.forEach(device => {
                        console.log(`${device.kind}: ${device.label} id = ${device.deviceId}`);
                    });
                })
                .catch(error => {
                    console.error('Error enumerating devices:', error);
                });
        },
        async makeCall() {
            this.requestMediaAccess();
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const audioDevice = devices.find(device => device.kind === 'audioinput');
                // const videoDevice = devices.find(device => device.kind === 'videoinput');

                const constraints = {
                    // video: videoDevice ? { deviceId: videoDevice.deviceId } : true,
                    audio: audioDevice ? { deviceId: audioDevice.deviceId } : true,
                };

                this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$refs.localVideo.srcObject = this.localStream;

                // Create peer connection
                this.peerConnection = new RTCPeerConnection();

                // Add local stream tracks to the connection
                this.localStream.getTracks().forEach(track => {
                    this.peerConnection.addTrack(track, this.localStream);
                });

                // Handle remote stream
                this.peerConnection.ontrack = event => {
                    this.remoteStream = event.streams[0];
                    this.$refs.remoteVideo.srcObject = this.remoteStream;
                };

                // Create offer
                const offer = await this.peerConnection.createOffer();
                await this.peerConnection.setLocalDescription(offer);

                // Send offer to the server to initiate the call
                const response = await axios.get('/webex/call', { destination: this.destination, offer: offer.sdp });
                console.log(response,'ss');
                

                // Set remote description when the answer is received from the server
                await this.peerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp: response.data.answer }));

                // Handle ICE candidates
                this.peerConnection.onicecandidate = event => {
                    if (event.candidate) {
                        axios.post('/webex/candidate', { candidate: event.candidate });
                    }
                };

                alert('Call initiated successfully!');
            } catch (error) {
                console.error('Error initiating the call:', error);
                alert('Error initiating call.');
            }
        }
    },
    mounted() {
        // Additional setup or WebRTC listeners if necessary
    }
}
</script>
