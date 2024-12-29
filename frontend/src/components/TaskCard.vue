<template>
  <div class="task-card" @click="onCardClick">
    <p>ID #{{ task.id }}</p>
    <h3 :class="{ completed: task.completed }">{{ task.title }}</h3>
    <p>{{ task.description }}</p>
    <p>Priority: {{ task.priority || 'None' }}</p>
    <p>Due Date: {{ task.due_date || 'No due date' }}</p>
    <p>Status: {{ task.completed ? 'Completed' : 'Todo' }}</p>

    <!-- Display tags -->
    <div class="task-tags" v-if="task.tags && task.tags.length">
      <p>Tags:</p>
      <div class="tags">
        <span v-for="(tag, index) in task.tags" :key="index" class="tag">
          {{ tag.name }}
        </span>
      </div>
    </div>

    <!-- Display attachments -->
    <div
      class="task-attachments"
      v-if="task.attachments && task.attachments.length"
    >
      <p>Attachments:</p>
      <ul>
        <li v-for="(attachment, index) in task.attachments" :key="index">
          <a :href="attachment.url" target="_blank" download>
            {{ attachment.original_file_name }}
          </a>
        </li>
      </ul>
    </div>

    <!-- Task actions -->
    <div class="task-actions">
      <button @click.stop="$emit('toggleComplete', task.id)">
        {{ task.completed ? 'Mark as Todo' : 'Mark as Completed' }}
      </button>
      <button @click.stop="$emit('toggleArchiveRestore', task.id)">
        {{ task.archived && task.archived_at ? 'Restore' : 'Archive' }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    task: {
      type: Object,
      required: true,
    },
  },
  methods: {
    onCardClick() {
      this.$emit('view', this.task);
    },
  },
};
</script>

<style scoped>
.task-card {
  padding: 1em;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #fff;
  margin-bottom: 1em;
}

.completed {
  text-decoration: line-through;
  color: #888;
}

.task-tags, .task-attachments {
  margin-top: 0.5em;
}

.tags {
  display: flex;
  gap: 0.5em;
  flex-wrap: wrap;
}

.tag {
  background-color: #e0e0e0;
  padding: 0.3em 0.6em;
  border-radius: 3px;
  font-size: 0.9em;
}

.task-actions {
  display: flex;
  gap: 0.5em;
  margin-top: 0.5em;
}

.task-actions button {
  cursor: pointer;
  padding: 0.5em;
  border: none;
  border-radius: 4px;
  background-color: #3f51b5;
  color: white;
  font-size: 0.9em;
}

.task-actions button:hover {
  background-color: #303f9f;
}

.delete-btn {
  background-color: #e57373;
}

.delete-btn:hover {
  background-color: #d32f2f;
}
</style>