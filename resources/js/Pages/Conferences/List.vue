<template>
    <AuthenticatedLayout>
        <div class="p-card">
            <h2>Active Conferences</h2>

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
            <TwilioVoiceComponent :conference="selectedConference" @conference-joined="handleConferenceJoined" />
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
        conferences: Array,
    },
    components: {
        TwilioVoiceComponent,
    },
    setup() {
        // const conferences = ref([]);
        const loading = ref(false);
        const selectedConference = ref(null);

        // const fetchActiveConferences = async () => {
        //     loading.value = true;
        //     try {
        //         const response = await axios.get('/api/conferences/active');
        //         conferences.value = response.data.conferences;
        //     } catch (error) {
        //         console.error('Error fetching active conferences:', error);
        //         // Optionally, display an error message to the user
        //     } finally {
        //         loading.value = false;
        //     }
        // };

        const joinConference = (conference) => {
            selectedConference.value = conference;
            console.log(conference);

        };

        const handleConferenceJoined = () => {
            // Reset selectedConference after joining
            selectedConference.value = null;
            // Optionally, refresh the conference list
            // fetchActiveConferences();
        };

        onMounted(() => {
            // fetchActiveConferences();
        });

        return {
            // conferences,
            loading,
            selectedConference,
            joinConference,
            handleConferenceJoined,
        };
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
