<template>

    <Head title="Transcription" />
    <AuthenticatedLayout>

        <div class="p-m-4">
            <h1 class="text-2xl font-bold mb-4">Transcriptions</h1>

            <!-- DataTable Component -->
            <DataTable :value="transcriptions.data" :paginator="false" responsiveLayout="scroll"
                class="p-datatable-striped" :sortField="currentSortField" :sortOrder="currentSortOrder" @sort="onSort">
                <Column field="id" header="ID" sortable></Column>
                <Column field="transcription_sid" header="Transcription SID" sortable></Column>
                <Column field="recording_sid" header="Recording SID" sortable></Column>
                <Column field="transcription_text" header="Transcription Text"></Column>
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
            </DataTable>

            <!-- Paginator Component -->
            <div class="p-mt-4">
                <Paginator :first="(transcriptions.current_page - 1) * transcriptions.per_page"
                    :rows="transcriptions.per_page" :totalRecords="transcriptions.total"
                    :page="transcriptions.current_page" @page="onPageChange"
                    template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink" />
            </div>
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
    },
};
</script>

<style scoped>
/* Optional: Customize styles if needed */
</style>
