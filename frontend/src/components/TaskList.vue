<template>
  <div class="filters-section">
      <h2>Filter Tasks</h2>
      <div class="filters">
        <div class="filter-item">
          <label>Search Title:</label>
          <input
            v-model="filter.search"
            type="search"
            placeholder="Search by title or description"
            class="filter-input"
          />
        </div>

        <div class="filter-item">
          <label>Priority:</label>
          <select v-model="filter.priority" class="filter-select">
            <option value="">None</option>
            <option value="Urgent">Urgent</option>
            <option value="High">High</option>
            <option value="Normal">Normal</option>
            <option value="Low">Low</option>
          </select>
        </div>

        <div class="filter-item">
          <label>Due Date From:</label>
          <input type="date" v-model="filter.due_date_from" class="filter-date" />
        </div>

        <div class="filter-item">
          <label>Due Date To:</label>
          <input type="date" v-model="filter.due_date_to" class="filter-date" />
        </div>

        <div class="filter-item">
          <label>Completed Date From:</label>
          <input type="date" v-model="filter.completed_date_from" class="filter-date" />
        </div>

        <div class="filter-item">
          <label>Completed Date To:</label>
          <input type="date" v-model="filter.completed_date_to" class="filter-date" />
        </div>

        <div class="filter-item">
          <label>Archived Date From:</label>
          <input type="date" v-model="filter.archived_date_from" class="filter-date" />
        </div>

        <div class="filter-item">
          <label>Archived Date To:</label>
          <input type="date" v-model="filter.archived_date_to" class="filter-date" />
        </div>
       
        <div class="filter-item filter-button">
          <button @click="applyFilter">Apply Filters</button>
        </div>
      </div>

    <div class="sorting-section">
      <h2>Sort Tasks</h2>
      <div class="sorting">
        <div class="sorting-item">
          <label>Sort By:</label>
          <select v-model="sort.sort_by" class="sorting-select">
            <option value="">Select Field</option>
            <option value="title">Title</option>
            <option value="description">Description</option>
            <option value="due_date">Due Date</option>
            <option value="created_at">Created Date</option>
            <option value="completed_at">Completed Date</option>
            <option value="priority">Priority</option>
          </select>
        </div>

        <div class="sorting-item">
          <label>Order:</label>
          <select v-model="sort.sort_order" class="sorting-select">
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>
        </div>

        <div class="sorting-item sorting-button">
          <button @click="applySorting">Apply Sorting</button>
        </div>
      </div>
    </div>

    <div class="filter-item" style="margin-top: 30px;">
      <label>
        <input type="checkbox" v-model="filter.showArchived" />
        Show Archived Tasks
      </label>
    </div>
    <!-- Task List -->
    <div class="tasks">
      <TaskCard
        v-for="task in tasks"
        :key="task.id"
        :task="task"
        @view="viewTask"
        @toggleComplete="toggleTaskCompletion"
        @toggleArchiveRestore="toggleTaskArchiveRestore"
      />
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button @click="prevPage" :disabled="page === 1" class="previous-button">Previous</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="page === totalPages" class="next-button">Next</button>
    </div>

    <!-- View/Edit Task Modal -->
    <CreateTaskModal
      v-if="showModal"
      :isVisible="showModal"
      :task="selectedTask"
      :isEditing="true"
      @close="closeModal"
      @taskUpdated="handleTaskUpdated"
      @taskDeleted="handleTaskDeleted"
    />

    <!-- Error Message -->
    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useTaskStore } from '../stores/taskStore';
import TaskCard from './TaskCard.vue';
import CreateTaskModal from './CreateTaskModal.vue';

export default {
  components: { TaskCard, CreateTaskModal },
  setup() {
    const taskStore = useTaskStore();
    const page = ref(1);
    const filter = ref({
      search: '',
      priority: '',
      due_date_from: '',
      due_date_to: '',
      completed_date_from: '',
      completed_date_to: '',
      archived_date_from: '',
      archived_date_to: '',
      showArchived: false,
    });

    const sort = ref({
      sort_by: '',
      sort_order: 'asc',
    });

    const showModal = ref(false);
    const errorMessage = ref('');
    const selectedTask = ref(null);

    const fetchTasks = async () => {
      try {
        await taskStore.fetchTasks({
          page: page.value,
          ...filter.value,
          ...sort.value,
        });
      } catch (error) {
        errorMessage.value = 'Failed to fetch tasks.';
      }
    };

    const applyFilter = () => {
      page.value = 1;
      fetchTasks();
    };

    const applySorting = () => {
      page.value = 1;
      fetchTasks();
    };


    const nextPage = () => {
      if (page.value < taskStore.totalPages) {
        page.value++;
        fetchTasks();
      }
    };

    const prevPage = () => {
      if (page.value > 1) {
        page.value--;
        fetchTasks();
      }
    };

    const viewTask = (task) => {
      selectedTask.value = task;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      selectedTask.value = null;
    };

    const handleTaskUpdated = () => {
      fetchTasks();
    };

    const handleTaskDeleted = () => {
      fetchTasks();
    };

    const deleteTask = async (taskId) => {
      if (confirm('Are you sure you want to delete this task?')) {
        try {
          await taskStore.deleteTask(taskId);
          fetchTasks();
        } catch (error) {
          errorMessage.value = 'Failed to delete task.';
        }
      }
    };

    const toggleTaskCompletion = async (taskId) => {
      try {
        await taskStore.toggleTaskCompletion(taskId);
        fetchTasks();
      } catch (error) {
        errorMessage.value = 'Failed to update task completion status.';
      }
    };

    const toggleTaskArchiveRestore = async (taskId) => {
      try {
        await taskStore.toggleTaskArchiveRestore(taskId);
        fetchTasks();
      } catch (error) {
        errorMessage.value =  taskStore.error || 'Failed to update task archive status.';
      }
    };

    onMounted(fetchTasks);
    watch(filter, applyFilter, { deep: true });
    watch(sort, applySorting, { deep: true });

    return {
      tasks: computed(() => taskStore.tasks),
      totalPages: computed(() => taskStore.totalPages),
      page,
      filter,
      applyFilter,
      sort,
      applySorting,
      nextPage,
      prevPage,
      viewTask,
      toggleTaskCompletion,
      toggleTaskArchiveRestore,
      showModal,
      selectedTask,
      closeModal,
      handleTaskUpdated,
      handleTaskDeleted,
      errorMessage,
    };
  },
};
</script>


<style scoped>
/* Container Styles */
.task-list-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Filters Section */
.filters-section,
.sorting-section {
  margin-bottom: 20px;
  margin-top: 20px;
  padding: 15px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.filters-section h2,
.sorting-section h2 {
  margin-bottom: 10px;
}

/* Filters and Sorting Layout */
.filters,
.sorting {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.filter-item,
.sorting-item {
  display: flex;
  flex-direction: column;
  flex: 1 1 200px;
}

.filter-item label,
.sorting-item label {
  margin-bottom: 5px;
  font-weight: bold;
}

.filter-input,
.filter-select,
.filter-date,
.sorting-select {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.filter-button,
.sorting-button {
  align-self: flex-end;
  margin-top: auto;
}

.filter-button button,
.sorting-button button {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.filter-button button:hover,
.sorting-button button:hover {
  background-color: #0056b3;
}

/* Task List Styles */
.tasks {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Pagination Styles */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

.pagination-button {
  padding: 10px 15px;
  margin: 0 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.pagination-button[disabled] {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination span {
  font-size: 16px;
}

/* Error Message Styles */
.error-message {
  margin-top: 20px;
  color: red;
  font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
  .filter-item,
  .sorting-item {
    flex: 1 1 100%;
  }

  .filter-button,
  .sorting-button {
    align-self: stretch;
  }
}
</style>
