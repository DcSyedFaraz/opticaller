<template>

    <Head title="Transcription" />
    <AuthenticatedLayout>

        <div class="p-m-4">
            <h1 class="text-2xl font-bold mb-4">Transcriptions</h1>

            <!-- DataTable Component -->
            <DataTable :value="transcriptions.data" :paginator="false" responsiveLayout="scroll"
                class="p-datatable-striped" :sortField="currentSortField" :sortOrder="currentSortOrder" @sort="onSort">
                <Column field="id" header="ID" sortable></Column>
                <Column field="callerIdentity" header="Caller"></Column>
                <Column field="recording_sid" header="Recording SID" sortable></Column>
                <Column field="created_at" header="Created At" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>
                <Column field="updated_at" header="Updated At" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.updated_at) }}
                    </template>
                </Column>
                <!-- New Actions Column -->
                <Column header="Actions" class="action-column">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" class="p-button-rounded p-button-success p-mr-2" title="Show Details"
                            @click="showDetails(data)" />
                        <Button icon="pi pi-trash" class="p-button-rounded p-button-danger" title="Delete Transcription"
                            @click="confirmDelete(data)" />
                    </template>
                </Column>
                <template #empty>
                    <div class="empty-state">
                        <i class="pi pi-info-circle" style="font-size: 2rem; color: #6c757d;"></i>
                        <p>No transcriptions available.</p>
                    </div>
                </template>
            </DataTable>

            <!-- Paginator Component -->
            <div class="p-mt-4">
                <Paginator :first="(transcriptions.current_page - 1) * transcriptions.per_page"
                    :rows="transcriptions.per_page" :totalRecords="transcriptions.total"
                    :page="transcriptions.current_page" @page="onPageChange"
                    template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink" />
            </div>
            <!-- Modal for Showing Transcription Details -->
            <Dialog v-model:visible="showModal" header="Transcription Details" :modal="true" :style="{ width: '50vw' }">
                <pre
                    v-if="selectedTranscription?.transcription_text">{{ selectedTranscription.transcription_text }}</pre>
                <p v-else>No transcription text available.</p>
                <template #footer>
                    <Button label="Close" @click="showModal = false" />
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    name: 'TranscriptionsIndex',

    props: {
        transcriptions: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            currentSortField: 'created_at',
            currentSortOrder: -1,
            showModal: false, // Controls the visibility of the details modal
            selectedTranscription: null,
        };
    },
    mounted() {
        this.currentSortField = this.$route?.query?.sortField || 'created_at';
        this.currentSortOrder = parseInt(this.$route?.query?.sortOrder, 10) || -1;
    },
    methods: {
        /**
         * Format the date string to a more readable format.
         * @param {String} date
         * @return {String}
         */
        formatDate(date) {
            return new Date(date).toLocaleString();
        },

        /**
         * Handle page changes from the Paginator component.
         * @param {Object} event
         */
        onPageChange(event) {
            const newPage = event.page + 1;
            console.log(event);

            this.$inertia.get(route('addresses.transcription'), {
                page: newPage,
                per_page: this.transcriptions.per_page,
                sortField: this.currentSortField,
                sortOrder: this.currentSortOrder === 1 ? 'asc' : 'desc',
            }, {
                preserveState: true,
                preserveScroll: true,
            });
        },

        /**
         * Handle sorting events from the DataTable component.
         * @param {Object} event
         */
        onSort(event) {
            this.currentSortField = event.sortField;
            this.currentSortOrder = event.sortOrder; // 1 for asc, -1 for desc

            this.$inertia.get(route('addresses.transcription'), {
                page: this.transcriptions.current_page,
                per_page: this.transcriptions.per_page,
                sortField: this.currentSortField,
                sortOrder: this.currentSortOrder === 1 ? 'asc' : 'desc',
            }, {
                preserveState: true,
                preserveScroll: true,
            });
        },
        showDetails(data) {
            this.selectedTranscription = data;
            this.showModal = true;
        },
        confirmDelete(data) {
            this.$confirm.require({
                message: `Are you sure you want to delete transcription ${data.transcription_sid}?`,
                header: 'Confirm Deletion',
                icon: 'pi pi-exclamation-triangle',
                acceptClass: 'p-button-danger',
                rejectClass: 'p-button-secondary p-button-text',
                accept: () => {
                    this.deleteTranscription(data);
                },
                reject: () => {
                    // Optional: Handle rejection (e.g., log or notify)
                    console.log('Deletion cancelled');
                },
            });
        },
        // Delete a transcription with confirmation
        deleteTranscription(data) {
            this.$inertia.delete(route('transcriptions.destroy', data.id), {
                onSuccess: () => {
                    this.$inertia.reload(); // Refresh the page after successful deletion
                },
                onError: (errors) => {
                    alert('Failed to delete transcription: ' + (errors.message || 'Unknown error'));
                },
            });
        },
    },
};
</script>
<style scoped>
.action-column {
    text-align: center;
    width: 120px;
}

.p-button {
    transition: transform 0.2s, box-shadow 0.2s;
}

.p-button:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.p-mr-2 {
    margin-right: 8px;
}

pre {
    white-space: pre-wrap;
    font-family: 'Courier New', Courier, monospace;
    font-size: 14px;
    background-color: #f8f8f8;
    padding: 1rem;
    border-radius: 4px;
    max-height: 400px;
    overflow-y: auto;
}

/* Style the empty state */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    /* Gray color for a subtle look */
}

.empty-state i {
    margin-bottom: 1rem;
}

.empty-state p {
    font-size: 1.1rem;
    margin: 0;
}
</style>
