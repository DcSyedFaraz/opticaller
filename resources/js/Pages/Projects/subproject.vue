<template>

    <Head title="Sub Project Management" />

    <AuthenticatedLayout>

        <div class="max-w-7xl mx-auto  px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight text-gray-900">Sub Project Management</h1>
            <form @submit.prevent="createProject" class="mt-8">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-12">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <InputText v-model="newProject.title" type="text"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                    <div class="sm:col-span-12">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <Textarea v-model="newProject.description" rows="5" cols="30"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                    <div class="sm:col-span-12">
                        <InputLabel for="Projects">Project</InputLabel>
                        <Select v-model="newProject.project_id" :options="projects" optionValue="id" optionLabel="title"
                            placeholder="Select Project" class="w-full" />
                    </div>
                    <div class="sm:col-span-12">
                        <InputLabel for="priority">Priority</InputLabel>
                        <Select v-model="newProject.priority" :options="priorityOptions" optionValue="value"
                            optionLabel="label" placeholder="Select Priority" class="w-full" />
                    </div>
                    <div class="sm:col-span-12">
                        <label for="pdf" class="block text-sm font-medium text-gray-700">Upload PDF</label>
                        <input type="file" @change="handleFileUpload($event, 'newProject')" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                                   file:rounded-md file:border-0 file:text-sm file:font-semibold
                                   file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                </div>

                <div class="mt-8">
                    <Button type="submit" label="Add Sub Project"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" />
                </div>
            </form>

            <DataTable :value="subprojects" responsiveLayout="scroll" class="mt-8">
                <Column field="id" header="id"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <!-- <Column field="project_id" header="project_id"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column> -->
                <Column field="title" header="Title"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column field="description" header="Description"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column header="Project"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <template #body="slotProps">
                        <span>
                            {{ slotProps.data.projects?.title }}
                        </span>
                    </template>
                </Column>
                <Column header="Priority"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <template #body="slotProps">
                        <span>
                            {{ getPriorityLabel(slotProps.data.priority) }}
                        </span>
                    </template>
                </Column>
                <Column header="PDF"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <template #body="slotProps">
                        <span v-if="slotProps.data.pdf_url">
                            <a :href="slotProps.data.pdf_url" target="_blank"
                                class="text-indigo-600 hover:underline">View PDF</a>
                        </span>
                        <span v-else>
                            N/A
                        </span>
                    </template>
                </Column>

                <Column header="Actions"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider flex">
                    <template #body="slotProps">
                        <Button @click="editProject(slotProps.data)" label="Edit"
                            class="!bg-[#3E3E3E] !border-[#3E3E3E] mx-2 !rounded !px-[2rem]" />
                        <Button @click="deleteProject(slotProps.data)" label="Delete"
                            class="!bg-secondary mx-2 !border-secondary !rounded !px-[2rem]" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No sub projects found.
                    </div>
                </template>
            </DataTable>

            <Dialog v-model:visible="editDialogVisible" :style="{ width: '450px' }" header="Edit Sub Project"
                :modal="true" class="p-fluid max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
                <form @submit.prevent="updateProject" class="grid grid-cols-1 gap-4">
                    <div class="field">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <InputText v-model="editProjectData.title" type="text"
                            class="mt-1 block w-full border border-gray-300 rounded-md py-2 pl-10 text-sm text-gray-700" />
                    </div>
                    <div class="field">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <Textarea v-model="editProjectData.description" rows="5" cols="30"
                            class="mt-1 block w-full border border-gray-300 rounded-md py-2 pl-10 text-sm text-gray-700" />
                    </div>
                    <div class="field">
                        <Select v-model="editProjectData.priority" :options="priorityOptions" optionValue="value"
                            optionLabel="label" placeholder="Select Priority" class="w-full" />
                    </div>
                    <div class="field">
                        <label for="pdf" class="block text-sm font-medium text-gray-700">Upload PDF</label>
                        <input type="file" @change="handleFileUpload($event, 'editProjectData')"
                            accept="application/pdf" class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                                   file:rounded-md file:border-0 file:text-sm file:font-semibold
                                   file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>

                    <div class="field">
                        <InputLabel for="Projects">Project</InputLabel>
                        <Select v-model="editProjectData.project_id" :options="projects" optionValue="id"
                            optionLabel="title" placeholder="Select Project" class="w-full" />
                    </div>
                    <Button type="submit" label="Update"
                        class="mt-4 justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" />
                </form>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>

export default {
    props: {
        subprojects: Array,
        projects: Array,
    },
    data() {
        return {
            newProject: {
                title: '',
                description: '',
                project_id: null,
                priority: null,
                pdf: null,
            },
            priorityOptions: [
                { label: 'Low', value: 1 },
                { label: 'Medium', value: 2 },
                { label: 'High', value: 3 },
                { label: 'Critical', value: 4 },
            ],
            editProjectData: {
                pdf: null,
            },
            editDialogVisible: false,
        };
    },
    mounted() {

        if (this.$page.props.message) {
            this.$toast.add({ severity: 'message', summary: this.$page.props.success, life: 3000 });
        }
    },
    methods: {
        handleFileUpload(event, target) {
            const file = event.target.files[0];
            if (file && file.type === 'application/pdf') {
                this[target].pdf = file;
            } else {
                this.$toast.add({ severity: 'error', summary: 'Invalid File', detail: 'Please upload a valid PDF file.', life: 3000 });
                event.target.value = null; // Reset the input
            }
        },
        getPriorityLabel(priorityValue) {
            const priority = this.priorityOptions.find(option => option.value === priorityValue);
            return priority ? priority.label : 'N/A';
        },
        createProject() {
            const formData = new FormData();
            formData.append('title', this.newProject.title);
            formData.append('description', this.newProject.description);
            formData.append('project_id', this.newProject.project_id);
            formData.append('priority', this.newProject.priority);
            if (this.newProject.pdf) {
                formData.append('pdf', this.newProject.pdf);
            }

            this.$inertia.post('/subprojects', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onSuccess: () => {
                    this.newProject = { title: '', description: '', project_id: null, priority: null, pdf: null };

                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Sub project created successfully', life: 3000 });
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key], life: 3000 });
                    });
                },
            });
        },
        editProject(project) {
            this.editProjectData = { ...project };
            this.editDialogVisible = true;
        },
        updateProject() {
            const formData = new FormData();
            formData.append('title', this.editProjectData.title);
            formData.append('description', this.editProjectData.description);
            formData.append('project_id', this.editProjectData.project_id);
            formData.append('priority', this.editProjectData.priority);
            if (this.editProjectData.pdf) {
                formData.append('pdf', this.editProjectData.pdf);
            }

            this.$inertia.put(`/subprojects/${this.editProjectData.id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onSuccess: () => {
                    this.editDialogVisible = false;
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Sub project updated successfully', life: 3000 });
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key], life: 3000 });
                    });
                },
            });
        },

        deleteProject(project) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this sub project?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                rejectProps: {
                    label: 'Cancel',
                    severity: 'success',

                },
                acceptProps: {
                    label: 'Yes',
                    severity: 'danger',
                    outlined: true
                },
                accept: () => {
                    this.$inertia.delete(`/subprojects/${project.id}`, {
                        onSuccess: () => {
                            this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Sub Project deleted successfully', life: 3000 });
                        },
                        onError: (errors) => {
                            Object.keys(errors).forEach(key => {
                                this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key] });
                            });
                        },
                    })
                }
            });
        },
    },
};
</script>
