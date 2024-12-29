<template>
    <div class="home-container">
      <Header />
      <h1>My Tasks</h1>
  
      <button @click="showCreateModal = true" class="create-task-btn">Create Task</button>
  
      <TaskList />
  
      <!-- Create Task Modal -->
      <CreateTaskModal
        v-if="showCreateModal"
        :isVisible="showCreateModal"
        @close="showCreateModal = false"
        @taskCreated="handleTaskCreated"
      />
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import Header from '../components/Header.vue';
  import TaskList from '../components/TaskList.vue';
  import CreateTaskModal from '../components/CreateTaskModal.vue';
  
  export default {
    components: {
      Header,
      TaskList,
      CreateTaskModal,
    },
    setup() {
      const showCreateModal = ref(false);
  
      const handleTaskCreated = () => {
        // You can refresh the task list or handle any additional logic here
      };
  
      return {
        showCreateModal,
        handleTaskCreated,
      };
    },
  };
  </script>
  
  <style scoped>
  .home-container {
    max-width: 80%;
    margin: 0 auto;
    padding: 1em;
  }
  
  .create-task-btn {
    background-color: #007bff;
    color: white;
    padding: 0.5em 1em;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 1em;
    margin-bottom: 1em;
    display: inline-block;
  }
  </style>
  