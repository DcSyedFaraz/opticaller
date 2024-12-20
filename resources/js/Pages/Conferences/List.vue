<template>

    <Head title="Active Conferences" />
    <AuthenticatedLayout>
        <div>

            <div class="p-card">
                <h2>Active Conferences</h2>

                <DataTable :value="conferences" :paginator="true" :rows="5">
                    <Column field="friendlyName" header="Conference Name"></Column>
                    <Column header="Action" body="actionTemplate">
                        <template #body="{ data }">
                            <Button severity="success" label="Join in Mute" icon="pi pi-microphone"
                                class="p-button-secondary" @click="joinConference(data.friendlyName)" />
                        </template>
                    </Column>
                    <template #empty> No active confrences found. </template>
                </DataTable>
                <TwilioCallComponent />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import TwilioCallComponent from './../Users/TwilioCallComponent.vue';
export default {
    components: { TwilioCallComponent },

    props: {
        conferences: Array,
    },
    methods: {
        joinConference(friendlyName) {
            // Here, you'll make an API call to join the conference.
            // You can integrate Twilio's Participant API to handle this.
            this.$inertia.post(route('admin.joinConference'), { friendlyName, mute: true });
        },
    },
};
</script>

<style>
.p-card {
    margin: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
