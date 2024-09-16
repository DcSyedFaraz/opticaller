<template>

    <Head title="Project Management " />

    <AuthenticatedLayout>

        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight text-gray-900">Project Management </h1>
            <form @submit.prevent="createProject" class="mt-8">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-12">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <InputText v-model="newProject.title" type="text"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                    <div class="sm:col-span-12">
                        <InputLabel for="priority">Project Priority</InputLabel>
                        <Select v-model="newProject.priority" :options="priorityOptions" optionValue="value"
                            optionLabel="label" placeholder="Select Priority" class="w-full" />
                        <div class="sm:col-span-12">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <Textarea v-model="newProject.description" rows="5" cols="30"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        </div>
                    </div>
                    <div class="sm:col-span-12">
                        <label for="color" class="block text-sm font-medium text-gray-700">Select Color</label>
                        <div class="mt-2 flex space-x-4">
                            <RadioButton v-for="color in colors" :key="color.value" :value="color.value"
                                v-model="newProject.color" :style="{ backgroundColor: color.value, color: 'white' }"
                                class="p-radiobutton" />
                        </div>
                    </div>

                </div>

                <div class="mt-8">
                    <Button type="submit" label="Add Project"
                        class="inline-flex justify-center py-2 !px-[4rem] border border-transparent shadow-sm text-sm font-medium !rounded text-white " />
                </div>
            </form>
            <DataTable :value="projects" responsiveLayout="scroll" class="mt-8">
                <Column field="id" header="ID"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column field="title" header="Title"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column field="description" header="Description"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></Column>
                <Column header="Actions"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider flex">
                    <template #body="slotProps">
                        <Button @click="editProject(slotProps.data)" label="Edit"
                            class="!bg-[#3E3E3E] !border-[#3E3E3E] mx-2 !rounded !px-[2rem]" />
                        <Button @click="deleteProject(slotProps.data)" severity="danger" label="Delete"
                            class="!rounded !px-[2rem]" />
                    </template>
                </Column>
                <template #empty>
                    <div class="text-center py-4 text-gray-500">
                        No projects found.
                    </div>
                </template>
            </DataTable>
            <Dialog v-model:visible="editDialogVisible" :style="{ width: '450px' }" header="Edit Project" :modal="true"
                class="p-fluid max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
                <form @submit.prevent="updateProject" class="grid grid-cols-1 gap-4">
                    <div class="field">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <InputText v-model="editProjectData.title" type="text"
                            class="mt-1 block w-full border border-gray-300 rounded-md py-2 pl-10 text-sm text-gray-700" />
                    </div>
                    <div class="field">
                        <Select v-model="editProjectData.priority" :options="priorityOptions" optionValue="value"
                            optionLabel="label" placeholder="Select Priority" class="w-full" />
                    </div>
                    <!-- Color Display and Selection -->
                    <div class="field">
                        <label for="color" class="block text-sm font-medium text-gray-700">Selected Color</label>
                        <div class="flex items-center mb-2">
                            <!-- Show selected color visually -->
                            <div :style="{ backgroundColor: editProjectData.color }"
                                :class="getBorderClass(editProjectData.color)"
                                class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                            <span class="ml-2">{{ editProjectData.color }}</span>
                        </div>

                        <!-- Color options to change -->
                        <div class="grid grid-cols-5 gap-2">
                            <RadioButton v-for="color in colors" :key="color.value" :value="color.value"
                                v-model="editProjectData.color"
                                :style="{ backgroundColor: color.value, color: 'white' }" class="p-radiobutton" />
                        </div>
                    </div>

                    <div class="field">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <Textarea v-model="editProjectData.description" rows="5" cols="30"
                            class="mt-1 block w-full border border-gray-300 rounded-md py-2 pl-10 text-sm text-gray-700" />
                    </div>
                    <Button type="submit" label="Update" severity="success" class="!bg-[#3E3E3E] !border-[#3E3E3E]" />
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
            priorityOptions: [
                { label: 'Low', value: 1 },
                { label: 'Medium', value: 2 },
                { label: 'High', value: 3 },
                { label: 'Critical', value: 4 },
            ],
            newProject: {
                title: '',
                description: '',
            },
            editProjectData: {},
            colors: [
                { label: 'Red', value: '#509EE9' },
                { label: 'Green', value: '#2CA77F' },
                { label: 'Blue', value: '#A72C53' },
                { label: 'Yellow', value: '#6A1C5C' },
                { label: 'Purple', value: '#2D0C17' },
                { label: 'Orange', value: '#1C356A' },
                { label: 'Cyan', value: '#EC1E85' },
                { label: 'Magenta', value: '#4757BC' },
                { label: 'Pink', value: '#ED6659' },
            ],
            editDialogVisible: false,
        };
    },
    mounted() {

        if (this.$page.props.message) {
            this.$toast.add({ severity: 'message', summary: this.$page.props.success, life: 3000 });
        }
    },
    methods: {
        getBorderClass(color) {
            const colorMap = {
                '#509EE9': 'border-[#509EE9]', // Red
                '#2CA77F': 'border-[#2CA77F]', // Green
                '#A72C53': 'border-[#A72C53]', // Blue
                '#6A1C5C': 'border-[#6A1C5C]', // Yellow
                '#2D0C17': 'border-[#2D0C17]', // Purple
                '#1C356A': 'border-[#1C356A]', // Orange
                '#EC1E85': 'border-[#EC1E85]', // Cyan
                '#4757BC': 'border-[#4757BC]', // Magenta
                '#ED6659': 'border-[#ED6659]',
                '#E4B53E': 'border-[#E4B53E]',
                '#5C6A1C': 'border-[#5C6A1C]',
                '#6E0B0B': 'border-[#6E0B0B]',
            };
            // Return mapped class or a default if color not found
            return colorMap[color] || 'border-gray-500';
        },
        createProject() {
            this.$inertia.post('/projects', this.newProject, {
                onSuccess: () => {
                    this.newProject.title = '';
                    this.newProject.description = '';
                    this.newProject.color = '';
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project created successfully', life: 3000 });
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
            this.$inertia.put(`/projects/${this.editProjectData.id}`, this.editProjectData, {
                onSuccess: () => {
                    this.editDialogVisible = false;
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project updated successfully', life: 3000 });
                },
                onError: (errors) => {
                    Object.keys(errors).forEach(key => {
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[key], life: 3000 });
                    });
                },
            });
        },

        deleteProject(project) {
            this.$inertia.delete(`/projects/${project.id}`, {
                onSuccess: () => {
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Project deleted successfully', life: 3000 });
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
