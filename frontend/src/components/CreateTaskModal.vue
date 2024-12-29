<template>
  <div v-if="isVisible" class="modal-overlay">
    <div class="modal-content">
      <button class="close-button" @click="closeModal">&times;</button>
      <TaskForm
        :task="task"
        :isEditing="isEditing"
        @save="handleSave"
        @delete="handleDelete"
        @close="closeModal"
      />
    </div>
  </div>
</template>

<script>
import TaskForm from './TaskForm.vue';
import { useTaskStore } from '../stores/taskStore';

export default {
  components: {
    TaskForm,
  },
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
    task: {
      type: Object,
      default: () => ({}),
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close', 'taskCreated', 'taskUpdated', 'taskDeleted'],
  setup(props, { emit }) {
    const taskStore = useTaskStore();

    const handleSave = () => {
      if (props.isEditing) {
        emit('taskUpdated');
      } else {
        emit('taskCreated');
      }
      emit('close');
    };

    const handleDelete = async (taskId) => {
      try {
        await taskStore.deleteTask(taskId);
        alert('Task deleted successfully!');
        emit('taskDeleted');
        emit('close');
      } catch (error) {
        alert(taskStore.error || 'An error occurred while deleting the task.');
      }
    };

    const closeModal = () => {
      emit('close');
    };

    return {
      handleSave,
      handleDelete,
      closeModal,
    };
  },
};
</script>

  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .modal-content {
    background-color: white;
    padding: 1em;
    width: 500px;
    max-width: 90%;
    position: relative;
    border-radius: 8px;
  }
  
  .close-button {
    position: absolute;
    top: 0.5em;
    right: 0.5em;
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
  }
  </style>
  