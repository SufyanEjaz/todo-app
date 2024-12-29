import { defineStore } from 'pinia';
import api from '../api/auth'; 

export const useTaskStore = defineStore('taskStore', {
  state: () => ({
    tasks: [],
    totalPages: 1,
    currentTask: null,
    error: null,
  }),
  actions: {
    async fetchTasks({ 
      page = 1, 
      search = '',
      priority = '',
      due_date_from = '',
      due_date_to = '',
      completed_date_from = '',
      completed_date_to = '',
      archived_date_from = '',
      archived_date_to = '',
      sort_by = '',
      sort_order = '',
      showArchived = false
      } = {}) {
      try {
        const response = await api.get('/tasks',{
          params: {
            page,
            search,
            priority,
            due_date_from,
            due_date_to,
            completed_date_from,
            completed_date_to,
            archived_date_from,
            archived_date_to,
            sort_by,
            sort_order,
            showArchived,
          },
        });
        this.tasks = response.data.data;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error('Error fetching tasks:', error);
        this.error = 'Failed to fetch tasks. Please try again later.';
      }
    },
    async fetchTask(id) {
      try {
        const response = await api.get(`/tasks/${id}`);
        this.currentTask = response.data;
      } catch (error) {
        console.error('Error fetching task:', error);
        this.error = 'Failed to fetch task details.';
      }
    },
    async createTask(task) {
      try {
        const response = await api.post('/tasks', task);
        this.tasks.push(response.data);
      } catch (error) {
        console.error('Error creating task:', error);
        this.error = 'Failed to create task.';
      }
    },
    async updateTask(id, task) {
      try {
        const response = await api.post(`/tasks/${id}`, task, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        const index = this.tasks.findIndex((t) => t.id === id);
        if (index !== -1) this.tasks[index] = response.data;
      } catch (error) {
        console.error('Error updating task:', error);
        this.error = 'Failed to update task.';
      }
    },
    async deleteTask(id) {
      try {
        await api.delete(`/tasks/${id}`);
        this.tasks = this.tasks.filter((task) => task.id !== id);
      } catch (error) {
        console.error('Error deleting task:', error);
        this.error = 'Failed to delete task.';
      }
    },
    async toggleTaskCompletion(id) {
      try {
        const response = await api.put(`/tasks/${id}/toggleComplete`, {});
        const updatedTask = response.data;
        const index = this.tasks.findIndex((task) => task.id === id);
        if (index !== -1) {
          this.tasks[index] = updatedTask;
        }
      } catch (error) {
        console.error('Error toggling task completion:', error);
        this.error = 'Failed to update task status.';
      }
    },
    async toggleTaskArchiveRestore(id) {
      try {
        const response = await api.put(`/tasks/${id}/toggleArchiveRestore`, {});
        const updatedTask = response.data;
        const index = this.tasks.findIndex((task) => task.id === id);
        if (index !== -1) {
          // this.tasks[index] = updatedTask;
          if (updatedTask.archived_at) {
            // Remove archived task from the list
            this.tasks.splice(index, 1);
          } else {
            // Add restored task back to the list
            this.tasks.splice(index, 0, updatedTask);
          }
        }
      } catch (error) {
        console.error('Error toggling task completion:', error);
        this.error = error.response?.data?.error || 'Failed to update task status.'
      }
    },

    clearError() {
      this.error = null;
    },
  },
});
