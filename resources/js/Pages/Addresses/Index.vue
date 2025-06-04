<template>

    <Head title="Address List" />

    <AuthenticatedLayout>
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Address List</h1>
            <div class="flex gap-2">
                <Button icon="pi pi-upload" label="Import Excel" class="p-button-outlined p-button-success"
                    @click="showImportDialog = true" />
                <Link :href="route('addresses.create')" class="p-button p-component p-button-contrast" as="button">
                Create New
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <DataTable :value="addresses.data" v-model:filters="tableFilters" filterDisplay="row"
                :globalFilterFields="globalFilterFields" :sortField="query.sortField"
                :sortOrder="sortOrderMap[query.sortOrder]" lazy responsiveLayout="scroll" dataKey="id" :paginator="true"
                :rows="addresses.per_page" :totalRecords="addresses.total" @page="onPage" @sort="onSort"
                @filter="onFilter">
                <!-- Global filter bar -->
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="tableFilters.global.value" placeholder="Keyword Search" />
                        </IconField>
                    </div>
                </template>

                <!-- Company Name -->
                <Column field="company_name" header="Company Name" sortable style="min-width:12rem">
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Sub-Project title -->
                <Column header="Sub-Project" filterField="subproject.title" style="min-width:12rem">
                    <template #body="{ data }">{{ data.subproject?.title }}</template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="email_address_system" header="Email System" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="feedback" header="Last Feedback" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="follow_up_date" header="Follow Date" sortable style="min-width:18rem">
                    <template #filter="{ filterModel, filterCallback }">
                        <DatePicker v-model="filterModel.value" @date-select="filterCallback()" dateFormat="yy-mm-dd"
                            showIcon />
                    </template>
                </Column>

                <Column field="deal_id" header="Deal ID" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <Column field="contact_id" header="Contact ID" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Closure user name (joined) -->
                <Column header="Closure User" sortable sortField="closure_user_name" filterField="closure_user_name">
                    <template #body="{ data }">{{ data.lastuser?.users?.name }}</template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" @input="filterCallback()" placeholder="Search…" />
                    </template>
                </Column>

                <!-- Action buttons -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex space-x-2">
                            <Button icon="pi pi-eye" class="p-button-text p-button-info"
                                @click="viewAddress(data.id)" />
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger"
                                @click="confirmDelete(data)" />
                        </div>
                    </template>
                </Column>

                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No address found. Try different filters or add a new address.
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Excel Import Dialog -->
        <Dialog v-model:visible="showImportDialog" modal header="Import Excel File" :style="{ width: '55rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="flex flex-col gap-6">
                <!-- Import Results Section -->
                <div v-if="importResults && !importing">
                    <Card class="shadow-lg border-0">
                        <template #content>
                            <!-- Success Header -->
                            <div class="text-center mb-6">
                                <div
                                    class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="pi pi-check-circle text-4xl text-green-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                    {{ importResults.success ? 'Import Completed!' : 'Import Finished with Issues' }}
                                </h3>
                                <p class="text-gray-600">
                                    {{ importResults.success ? 'Your data has been successfully imported.' : 'The import process completed but encountered some issues.' }}
                                </p>
                            </div>

                            <!-- Statistics Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <!-- Total Processed -->
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-blue-600 mb-1">Total Processed</p>
                                            <p class="text-2xl font-bold text-blue-900">{{ importResults.total || 0 }}
                                            </p>
                                        </div>
                                        <div class="w-12 h-12 bg-blue-200 rounded-lg flex items-center justify-center">
                                            <i class="pi pi-file text-blue-700 text-xl"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Successfully Imported -->
                                <div
                                    class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-green-600 mb-1">Successfully Imported</p>
                                            <p class="text-2xl font-bold text-green-900">{{ importResults.imported || 0
                                            }}</p>
                                        </div>
                                        <div class="w-12 h-12 bg-green-200 rounded-lg flex items-center justify-center">
                                            <i class="pi pi-check text-green-700 text-xl"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Skipped/Errors -->
                                <div
                                    class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-orange-600 mb-1">
                                                {{ (importResults.skipped || 0) > 0 ? 'Skipped' : 'Errors' }}
                                            </p>
                                            <p class="text-2xl font-bold text-orange-900">
                                                {{ (importResults.skipped || 0) + (importResults.errors?.length || 0) }}
                                            </p>
                                        </div>
                                        <div
                                            class="w-12 h-12 bg-orange-200 rounded-lg flex items-center justify-center">
                                            <i class="pi pi-exclamation-triangle text-orange-700 text-xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Import Progress</span>
                                    <span class="text-sm text-gray-500">
                                        {{ Math.round(((importResults.imported || 0) / (importResults.total || 1)) *
                                            100) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full transition-all duration-500"
                                        :style="{ width: Math.round(((importResults.imported || 0) / (importResults.total || 1)) * 100) + '%' }">
                                    </div>
                                </div>
                            </div>

                            <!-- Error Details (if any) -->
                            <div v-if="importResults.errors && importResults.errors.length > 0" class="mb-4">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="pi pi-times-circle text-red-500 text-lg"></i>
                                        </div>
                                        <div class="ml-3 w-full">
                                            <h4 class="text-sm font-medium text-red-800 mb-2">
                                                Issues Encountered ({{ importResults.errors.length }})
                                            </h4>
                                            <div class="max-h-32 overflow-y-auto">
                                                <ul class="text-sm text-red-700 space-y-1">
                                                    <li v-for="(error, index) in importResults.errors.slice(0, 5)"
                                                        :key="index" class="flex items-start">
                                                        <span
                                                            class="inline-block w-2 h-2 bg-red-400 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                        <span>{{ error.message || error }}</span>
                                                    </li>
                                                    <li v-if="importResults.errors.length > 5"
                                                        class="text-red-600 font-medium">
                                                        ... and {{ importResults.errors.length - 5 }} more issues
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Success Message -->
                            <div v-if="importResults.success && (!importResults.errors || importResults.errors.length === 0)"
                                class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="pi pi-check-circle text-green-500 text-lg mr-3"></i>
                                    <div>
                                        <h4 class="text-sm font-medium text-green-800">Perfect Import!</h4>
                                        <p class="text-sm text-green-700">All records were successfully imported without
                                            any issues.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-center gap-3 mt-6">
                                <Button label="View Imported Data" icon="pi pi-eye"
                                    class="p-button-outlined p-button-success" @click="viewImportedData" />
                                <Button label="Import Another File" icon="pi pi-upload"
                                    class="p-button-outlined p-button-info" @click="startNewImport" />
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Original Import Interface (when no results) -->
                <div v-else-if="!importing">
                    <!-- Drag & Drop Upload Section -->
                    <div class="border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200"
                        :class="dragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100'"
                        @dragenter="onDragEnter" @dragover="onDragOver" @dragleave="onDragLeave" @drop="onDrop">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center transition-colors"
                                :class="dragActive ? 'bg-blue-200' : 'bg-blue-100'">
                                <i class="text-3xl transition-colors"
                                    :class="dragActive ? 'pi pi-download text-blue-700' : 'pi pi-cloud-upload text-blue-600'"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    {{ dragActive ? 'Drop your file here' : 'Drag & Drop Excel file' }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-6">
                                    or click to browse and select file
                                </p>
                                <div class="flex flex-col items-center gap-2">
                                    <Button icon="pi pi-folder-open" label="Browse Files"
                                        class="p-button-outlined p-button-lg hover:!text-white"
                                        @click="triggerFileInput" />
                                    <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="hidden"
                                        @change="onFileInputChange" />
                                    <p class="text-xs text-gray-500 mt-2">
                                        Supports: .xlsx, .xls, .csv • Max size: 10MB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Info Section -->
                    <div v-if="selectedFile"
                        class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-file-excel text-green-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-green-800 text-lg">{{ selectedFile.name }}</p>
                                <div class="flex items-center gap-4 mt-1">
                                    <p class="text-sm text-green-600">{{ formatFileSize(selectedFile.size) }}</p>
                                    <div class="flex items-center gap-1 text-green-600">
                                        <i class="pi pi-check-circle text-xs"></i>
                                        <span class="text-xs">Ready to import</span>
                                    </div>
                                </div>
                            </div>
                            <Button icon="pi pi-times" class="p-button-text p-button-rounded p-button-danger"
                                @click="clearFile" v-tooltip.top="'Remove file'" />
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div v-if="previewData.length > 0">
                        <Card class="shadow-sm">
                            <template #title>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-eye text-blue-600"></i>
                                        <span>Data Preview</span>
                                    </div>
                                    <Tag :value="`${previewData.length} rows found`" severity="success" />
                                </div>
                            </template>
                            <template #content>
                                <div class="border rounded-lg overflow-hidden bg-white">
                                    <DataTable :value="previewData.slice(0, 5)" scrollable scrollHeight="250px"
                                        class="text-sm" stripedRows>
                                        <Column v-for="(column, index) in previewColumns" :key="index"
                                            :field="column.field" :header="column.header" style="min-width: 150px">
                                            <template #body="{ data }">
                                                <span class="text-gray-700">{{ data[column.field] || '-' }}</span>
                                            </template>
                                        </Column>
                                    </DataTable>
                                    <div v-if="previewData.length > 5" class="text-center p-3 bg-blue-50 border-t">
                                        <div class="flex items-center justify-center gap-2 text-blue-700">
                                            <i class="pi pi-info-circle"></i>
                                            <span class="text-sm font-medium">
                                                Showing first 5 rows • {{ previewData.length - 5 }} more rows will be
                                                imported
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Progress Section -->
                <div v-if="importing">
                    <Card class="shadow-sm border-l-4 border-l-blue-500">
                        <template #content>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="pi pi-spin pi-spinner text-blue-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">Importing your data...</h4>
                                    <p class="text-sm text-gray-600">{{ importStatus }}</p>
                                </div>
                            </div>
                            <ProgressBar :value="importProgress" class="mb-2 h-3" :showValue="false" />
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Progress: {{ Math.round(importProgress) }}%</span>
                                <span class="text-blue-600 font-medium">{{ importProgress === 100 ? 'Complete!' :
                                    'Processing...' }}</span>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="pi pi-info-circle"></i>
                        <span>Supports: Excel (.xlsx, .xls) and CSV files</span>
                    </div>
                    <div class="flex gap-2">
                        <Button v-if="!importResults" label="Cancel" icon="pi pi-times"
                            class="p-button-text hover:!text-white" @click="closeImportDialog" :disabled="importing" />
                        <Button v-if="importResults" label="Close" icon="pi pi-times"
                            class="p-button-text hover:!text-white" @click="closeImportDialog" />
                        <Button v-if="!importResults" :label="importing ? 'Importing...' : 'Import Data'"
                            :icon="importing ? 'pi pi-spin pi-spinner' : 'pi pi-check'" class="p-button-success"
                            @click="importData" :disabled="!selectedFile || importing" />
                    </div>
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';

export default {
    components: { Link },
    props: {
        addresses: Object,
        filters: Object // comes from backend, used to prime initial state
    },
    data() {
        return {
            globalFilterFields: [
                'company_name',
                'subproject.title',
                'email_address_system',
                'feedback',
                'follow_up_date',
                'deal_id',
                'contact_id',
                'closure_user_name'
            ],
            query: {
                // persisted query-string params (page, sort, etc.)
                search: '',
                sortField: this.filters.sortField || 'id',
                sortOrder: this.filters.sortOrder || 'asc',
                page: this.filters.page || 1
            },
            tableFilters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                company_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'subproject.title': { value: null, matchMode: FilterMatchMode.CONTAINS },
                email_address_system: { value: null, matchMode: FilterMatchMode.CONTAINS },
                feedback: { value: null, matchMode: FilterMatchMode.CONTAINS },
                follow_up_date: { value: null, matchMode: FilterMatchMode.DATE_IS },
                deal_id: { value: null, matchMode: FilterMatchMode.CONTAINS },
                contact_id: { value: null, matchMode: FilterMatchMode.CONTAINS },
                closure_user_name: { value: null, matchMode: FilterMatchMode.CONTAINS }
            },
            sortOrderMap: { asc: 1, desc: -1 },

            // Excel Import related data
            showImportDialog: false,
            selectedFile: null,
            previewData: [],
            previewColumns: [],
            importing: false,
            importProgress: 0,
            importStatus: '',
            dragActive: false,
            importResults: null // New property to store import results
        };
    },
    methods: {
        /** central loader – called for page / sort / filter changes */
        fetchData(extra = {}) {
            const payload = {
                ...this.query,
                ...extra,
                filters: JSON.stringify(this.tableFilters) // send full filter object
            };
            this.$inertia.get(route('addresses.index'), payload, { preserveState: true, replace: true });
        },
        /* events from DataTable */
        onPage({ page }) {
            this.query.page = page + 1;
            this.fetchData();
        },
        onSort({ sortField, sortOrder }) {
            this.query.sortField = sortField || 'id';
            this.query.sortOrder = sortOrder === 1 ? 'asc' : 'desc';
            this.fetchData();
        },
        onFilter() {
            // whenever any column filter or global changes
            this.query.page = 1; // reset to first page after filtering
            this.fetchData();
        },
        /* CRUD helpers */
        viewAddress(id) {
            this.$inertia.visit(route('addresses.show', id));
        },
        confirmDelete(address) {
            this.$confirm.require({
                message: 'Delete this address?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => this.deleteAddress(address)
            });
        },
        deleteAddress(address) {
            this.$inertia.delete(route('addresses.destroy', address.id), { preserveScroll: true });
        },

        // Excel Import Methods
        onFileSelect(file) {
            this.selectedFile = file;
            this.processExcelFile();
        },

        triggerFileInput() {
            this.$refs.fileInput.click();
        },

        onFileInputChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.onFileSelect(file);
            }
        },

        // Drag and Drop Methods
        onDragEnter(e) {
            e.preventDefault();
            this.dragActive = true;
        },

        onDragOver(e) {
            e.preventDefault();
            this.dragActive = true;
        },

        onDragLeave(e) {
            e.preventDefault();
            // Only deactivate if leaving the drop zone completely
            if (!e.currentTarget.contains(e.relatedTarget)) {
                this.dragActive = false;
            }
        },

        onDrop(e) {
            e.preventDefault();
            this.dragActive = false;

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];

                // Validate file type
                const allowedTypes = ['.xlsx', '.xls', '.csv'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                if (!allowedTypes.includes(fileExtension)) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Invalid File Type',
                        detail: 'Please select an Excel (.xlsx, .xls) or CSV file',
                        life: 5000
                    });
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'File Too Large',
                        detail: 'File size must not exceed 10MB',
                        life: 5000
                    });
                    return;
                }

                this.onFileSelect(file);
            }
        },

        clearFile() {
            this.selectedFile = null;
            this.previewData = [];
            this.previewColumns = [];
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async processExcelFile() {
            if (!this.selectedFile) return;

            try {
                const arrayBuffer = await this.selectedFile.arrayBuffer();
                const workbook = XLSX.read(arrayBuffer, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                // Convert to JSON - assume first row contains headers
                const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                    header: 1, // Get raw data first
                    defval: '',
                    blankrows: false
                });

                if (jsonData.length === 0) {
                    throw new Error('No data found in the file');
                }

                // Process headers and data
                const headers = jsonData[0];
                const dataRows = jsonData.slice(1);

                // Convert to object format
                const processedData = dataRows.map(row => {
                    const obj = {};
                    headers.forEach((header, index) => {
                        if (header) {
                            obj[header] = row[index] || '';
                        }
                    });
                    return obj;
                }).filter(row => {
                    // Filter out completely empty rows
                    return Object.values(row).some(value => value && value.toString().trim() !== '');
                });

                this.previewData = processedData;

                // Generate columns for preview
                if (processedData.length > 0) {
                    const firstRow = processedData[0];
                    this.previewColumns = Object.keys(firstRow).map(key => ({
                        field: key,
                        header: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')
                    }));
                }

                this.$toast.add({
                    severity: 'success',
                    summary: 'File Processed Successfully',
                    detail: `Found ${processedData.length} rows ready for import`,
                    life: 4000
                });

            } catch (error) {
                console.error('Error processing Excel file:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'File Processing Error',
                    detail: error.message || 'Failed to process Excel file. Please check the file format.',
                    life: 6000
                });
                this.clearFile();
            }
        },

        async importData() {
            if (!this.selectedFile || !this.previewData.length) return;

            this.importing = true;
            this.importProgress = 0;
            this.importStatus = 'Preparing import...';
            this.importResults = null; // Clear previous results

            try {
                const formData = new FormData();
                formData.append('excel_file', this.selectedFile);
                formData.append('preview_data', JSON.stringify(this.previewData));

                // Simulate progress updates
                const progressInterval = setInterval(() => {
                    if (this.importProgress < 90) {
                        this.importProgress += Math.random() * 15;
                        const currentRow = Math.floor(this.importProgress / 100 * this.previewData.length);
                        this.importStatus = `Processing row ${currentRow} of ${this.previewData.length}...`;
                    }
                }, 800);

                // Make the actual import request
                this.$inertia.post(route('addresses.import'), formData, {
                    preserveScroll: true,
                    onSuccess: (page) => {
                        this.importProgress = 100;
                        this.importStatus = 'Import completed successfully!';

                        // Extract import results from the response
                        const flashData = page.props.flash;
                        this.importResults = {
                            success: flashData.success || false,
                            message: flashData.message || 'Import completed',
                            imported: flashData.imported || 0,
                            skipped: flashData.skipped || 0,
                            errors: flashData.importErrors || [],
                            total: flashData.total || 0
                        };

                        // Show success toast
                        this.$toast.add({
                            severity: this.importResults.success ? 'success' : 'warn',
                            summary: this.importResults.success ? 'Import Completed' : 'Import Completed with Issues',
                            detail: `Successfully imported ${this.importResults.imported} out of ${this.importResults.total} records`,
                            life: 6000
                        });
                    },
                    onError: (error) => {
                        console.error('Import error:', error);
                        this.importProgress = 0;
                        this.importStatus = '';
                        this.importing = false;

                        this.$toast.add({
                            severity: 'error',
                            summary: 'Import Failed',
                            detail: error.message || 'An error occurred during import. Please try again.',
                            life: 6000
                        });
                    },
                    onFinish: () => {
                        clearInterval(progressInterval);
                        this.importing = false;
                        this.fetchData(); // Refresh the table
                    }
                });

            } catch (error) {
                clearInterval(progressInterval);
                console.error('Import error:', error);
                this.importProgress = 0;
                this.importStatus = '';
                this.importing = false;

                this.$toast.add({
                    severity: 'error',
                    summary: 'Import Failed',
                    detail: error.message || 'An error occurred during import. Please try again.',
                    life: 6000
                });
            }
        },

        closeImportDialog() {
            this.showImportDialog = false;
            this.selectedFile = null;
            this.previewData = [];
            this.previewColumns = [];
            this.importing = false;
            this.importProgress = 0;
            this.importStatus = '';
            this.dragActive = false;
            this.importResults = null; // Clear results
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        // New methods for import results actions
        viewImportedData() {
            this.closeImportDialog();
            // Optionally apply filters to show recently imported data
            this.fetchData();
        },

        startNewImport() {
            // Reset to initial import state
            this.selectedFile = null;
            this.previewData = [];
            this.previewColumns = [];
            this.importResults = null;
            this.importing = false;
            this.importProgress = 0;
            this.importStatus = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    },
    mounted() {
        if (this.$page.props.flash.message) {
            this.$toast.add({ severity: 'success', summary: this.$page.props.flash.message, life: 3000 });
        }

        // Load SheetJS library for Excel processing
        if (!window.XLSX) {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            document.head.appendChild(script);
        }
    }
};
</script>
