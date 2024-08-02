<template>

    <Head title="Projects" />

    <AuthenticatedLayout>

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight text-gray-900">Projects</h1>
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
                </div>
                <div class="mt-8">
                    <Button type="submit" label="Add Project"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" />
                </div>
            </form>
            <DataTable :value="projects" responsiveLayout="scroll" class="mt-8">
                <Column field="title" header="Title"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column field="description" header="Description"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column header="Actions"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <template #body="slotProps">
                        <Button @click="editProject(slotProps.data)" label="Edit"
                            class="text-indigo-600 hover:text-indigo-900" />
                        <Button @click="deleteProject(slotProps.data)" label="Delete"
                            class="text-red-600 hover:text-red-900" />
                    </template>
                </Column>
            </DataTable>
            <Dialog v-model:visible="editDialogVisible" :style="{ width: '450px' }" header="Edit Project" :modal="true"
                class="p-fluid">
                <form @submit.prevent="updateProject">
                    <div class="field">
                        <label for="title">Title</label>
                        <InputText v-model="editProjectData.title" type="text" />
                    </div>
                    <div class="field">
                        <label for="description">Description</label>
                        <Textarea v-model="editProjectData.description" rows="5" cols="30" />
                    </div>
                    <Button type="submit" label="Update" class="mt-2" />
                </form>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>

export default {
    props: {
        projects: Array,
    },
    data() {
        return {
            newProject: {
                title: '',
                description: '',
            },
            editProjectData: {},
            editDialogVisible: false,
        };
    },
    mounted() {

        if (this.$page.props.message) {
            this.$toast.add({ severity: 'message', summary: this.$page.props.success, life:3000 });
        }
    },
    methods: {
        createProject() {
            this.$inertia.post('/projects', this.newProject, {
                onSuccess: () => {
                    this.newProject.title = '';
                    this.newProject.description = '';
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project created successfully' ,life:3000});
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key] });
                    });
                },
            });
        },

        updateProject() {
            this.$inertia.put(`/projects/${this.editProjectData.id}`, this.editProjectData, {
                onSuccess: () => {
                    this.editDialogVisible = false;
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project updated successfully' });
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key] });
                    });
                },
            });
        },

        deleteProject(project) {
            this.$inertia.delete(`/projects/${project.id}`, {
                onSuccess: () => {
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project deleted successfully' });
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key] });
                    });
                },
            });
        },
    },
};
</script>
