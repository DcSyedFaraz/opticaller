<template>

    <Head title="Assign Projects" />

    <AuthenticatedLayout>
        <div class="container mx-auto py-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Assign Projects</h2>
                <DataTable :value="subProjects" dataKey="id" class="w-full">
                    <Column header="Project Title" class="text-lg font-semibold">
                        <template #body="slotProps">
                            {{ slotProps.data.projects.title ? slotProps.data.projects.title : 'N/A' }}
                        </template>
                    </Column>
                    <Column field="title" header="Sub Project Title" class="font-semibold text-lg"></Column>
                    <Column header="Assigned Users" class="text-lg">
                        <template #body="slotProps">
                            <div>
                                <span v-if="slotProps.data.users && slotProps.data.users.length > 0">
                                
                                    <ul>
                                        <li v-for="user in slotProps.data.users" :key="user.id">{{ user.name }}</li>
                                    </ul>
                                </span>
                                <span v-else>No users assigned.</span>
                            </div>

                        </template>
                    </Column>
                    <Column header="Action" class="text-lg">
                        <template #body="slotProps">
                            <Button @click="openDialog(slotProps.data)" label="Assign Users"
                                class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300" />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <Dialog v-model:visible="dialogVisible" :header="'Assign Users to ' + selectedSubProject.title"
                class="w-full md:w-1/2">
                <div class="p-4">
                    <MultiSelect fluid placeholder="Select Users" v-model="selectedUsers" display="chip"
                        class="w-full mb-4" filter :options="users" optionLabel="name" optionValue="id" />
                </div>
                <template #footer>
                    <div class="flex justify-end p-4 ">
                        <Button @click="saveUsers" label="Save"
                            class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-300" />
                    </div>
                </template>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    props: {
        subProjects: Array,
        users: Array
    },

    data() {
        return {
            dialogVisible: false,
            selectedSubProject: { id: 0 },
            selectedUsers: []
        }
    },

    methods: {
        openDialog(subProject) {
            this.dialogVisible = true;
            this.selectedSubProject = subProject;
            this.selectedUsers = subProject.users ? subProject.users.map(user => user.id) : [];
            // console.log(this.selectedUsers,subProject);

        },

        saveUsers() {
            axios.post(route('subProjects.assignUsers', this.selectedSubProject.id), {
                user_ids: this.selectedUsers
            })
                .then(response => {

                    const subProjectIndex = this.subProjects.findIndex(sp => sp.id === response.data.subProject.id);

                    // If found, update the subproject in the array
                    if (subProjectIndex !== -1) {
                        this.subProjects[subProjectIndex] = response.data.subProject;
                    }

                    this.dialogVisible = false;
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Users assigned successfully',
                        life: 3000
                    });
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        let errors = error.response.data.errors;
                        for (let key in errors) {
                            this.$toast.add({
                                severity: 'error',
                                summary: 'Error',
                                detail: errors[key][0],
                                life: 3000
                            });
                        }
                    } else {
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Error',
                            detail: 'An error occurred',
                            life: 3000
                        });
                    }
                });
        }
    }
}
</script>
