<template>
    <div>
      <h1>Add New Project</h1>
      <form @submit.prevent="createProject">
        <div>
          <label>Title</label>
          <input v-model="newProject.title" type="text" required />
        </div>
        <div>
          <label>Description</label>
          <textarea v-model="newProject.description"></textarea>
        </div>
        <button type="submit">Add Project</button>
      </form>
    </div>
  </template>

  <script>

  export default {
    data() {
      return {
        newProject: {
          title: '',
          description: '',
        },
      };
    },
    methods: {
      createProject() {
        this.$inertia.post('/projects', this.newProject);
        this.resetForm();
      },
      resetForm() {
        this.newProject = {
          title: '',
          description: '',
        };
      },
    },
  };
  </script>
