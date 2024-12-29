<template>
  <div class="task-form">
    <form @submit.prevent="handleSubmit">
      <!-- Title Field -->
      <label>Title:</label>
      <input v-model="taskData.title" type="text" :minlength="3" required/>
      <span v-if="errors.title" class="error-message">{{ errors.title }}</span>

      <!-- Description Field -->
      <label>Description:</label>
      <textarea v-model="taskData.description" :minlength="3" required></textarea>
      <span v-if="errors.description" class="error-message">{{ errors.description }}</span>

      <!-- Due Date Field -->
      <label>Due Date:</label>
      <input
        v-model="taskData.due_date"
        type="date"
        :min="today"
        placeholder="YYYY-MM-DD"
      />
      <span v-if="errors.due_date" class="error-message">{{ errors.due_date }}</span>

      <!-- Priority Field -->
      <label>Priority:</label>
      <select v-model="taskData.priority">
        <option value="Urgent">Urgent</option>
        <option value="High">High</option>
        <option value="Normal">Normal</option>
        <option value="Low">Low</option>
      </select>

      <!-- Tags Field -->
      <label>Tags:</label>
      <div class="tags-input">
        <input
          v-model="tagInput"
          @keydown.enter.prevent="addTag"
          type="text"
          placeholder="Add a tag and press Enter"
        />
        <div class="tags">
          <span v-for="(tag, index) in taskData.tags" :key="index" class="tag">
            {{ tag }}
            <button type="button" @click="removeTag(index)">x</button>
          </span>
        </div>
      </div>

      <!-- Attachments Field -->
      <label>Attachments:</label>
      <!-- Display existing attachments if editing -->
      <div v-if="isEditing && existingAttachments.length">
        <p>Existing Attachments:</p>
        <ul>
          <li v-for="(attachment, index) in existingAttachments" :key="index">
            <a :href="attachment.url" target="_blank" download>
              {{ attachment.original_file_name }}
            </a>
            <button type="button" @click="removeExistingAttachment(index)">Remove</button>
          </li>
        </ul>
      </div>

      <!-- Display newly uploaded attachments -->
      <div v-if="taskData.attachments && taskData.attachments.length">
        <p>New Attachments:</p>
        <ul>
          <li v-for="(file, index) in taskData.attachments" :key="index">
            {{ file.name }}
            <button type="button" @click="removeNewAttachment(index)">Remove</button>
          </li>
        </ul>
      </div>
      <input type="file" @change="handleFileUpload" multiple ref="fileInput" />

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="submit">
          {{ isEditing ? 'Save Changes' : 'Create Task' }}
        </button>
        <button
          v-if="isEditing"
          @click.prevent="handleDelete"
          type="button"
        >
          Delete Task
        </button>
      </div>
    </form>

    <div v-if="generalErrorMessage" class="error-message">
      {{ generalErrorMessage }}
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useTaskStore } from '../stores/taskStore';

export default {
  props: {
    task: {
      type: Object,
      default: () => ({}),
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['save', 'delete', 'close'],
  setup(props, { emit }) {
    const taskStore = useTaskStore();
    const attachmentsToDelete = ref([]);
    const taskData = ref({
      title: props.task.title || '',
      description: props.task.description || '',
      due_date: props.task.due_date || '',
      priority: props.task.priority || '',
      tags: props.task.tags ? props.task.tags.map((tag) => tag.name) : [],
      attachments: [],
    });

    const existingAttachments = ref(props.task.attachments ? [...props.task.attachments] : []);

    const tagInput = ref('');
    const errors = ref({});
    const generalErrorMessage = ref('');
    const fileInput = ref(null);

    const today = computed(() => {
      const date = new Date();
      return date.toISOString().split('T')[0];
    });

    const validateForm = () => {
      errors.value = {};

      if (!taskData.value.title) {
        errors.value.title = 'Title is required.';
      }

      if (!taskData.value.description) {
        errors.value.description = 'Description is required.';
      }

      if (taskData.value.due_date) {
        const dueDate = new Date(taskData.value.due_date);
        const currentDate = new Date(today.value);
        if (dueDate < currentDate) {
          errors.value.due_date = 'Due date cannot be in the past.';
        }
      }

      return Object.keys(errors.value).length === 0;
    };

    const addTag = () => {
      if (
        tagInput.value &&
        !taskData.value.tags.includes(tagInput.value.trim())
      ) {
        taskData.value.tags.push(tagInput.value.trim());
        tagInput.value = '';
      }
    };

    const removeTag = (index) => {
      taskData.value.tags.splice(index, 1);
    };

    const removeNewAttachment = (index) => {
      taskData.value.attachments.splice(index, 1);
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    }

    const handleFileUpload = (event) => {
      const files = Array.from(event.target.files);
      taskData.value.attachments.push(...files);
      
      // Reset the file input value
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };

    const removeExistingAttachment = (index) => {
      const removedAttachment = existingAttachments.value.splice(index, 1)[0];
      if (removedAttachment && removedAttachment.id) {
        attachmentsToDelete.value.push(removedAttachment.id);
      }
    };

    const handleSubmit = async () => {
      if (validateForm()) {
        try {
          // Prepare FormData
          const formData = new FormData();
          formData.append('title', taskData.value.title);
          formData.append('description', taskData.value.description);
          if (taskData.value.due_date) {
            formData.append('due_date', taskData.value.due_date);
          }
          if (taskData.value.priority) {
            formData.append('priority', taskData.value.priority);
          }
          if (taskData.value.tags && taskData.value.tags.length) {
            taskData.value.tags.forEach((tag, index) => {
              formData.append(`tags[${index}]`, tag);
            });
          }
          if (taskData.value.attachments && taskData.value.attachments.length) {
            taskData.value.attachments.forEach((file, index) => {
              formData.append(`attachments[${index}]`, file);
            });
          }

          if (attachmentsToDelete.value && attachmentsToDelete.value.length) {
            attachmentsToDelete.value.forEach((attachmentId, index) => {
              formData.append(`attachmentsToDelete[${index}]`, attachmentId);
            });
          }

          if (props.isEditing) {
            formData.append('_method', 'PUT');
            await taskStore.updateTask(props.task.id, formData);
            alert('Task updated successfully!');
          } else {
            await taskStore.createTask(formData);
            alert('Task created successfully!');
          }
          emit('save');
        } catch (error) {
          generalErrorMessage.value = taskStore.error || 'An error occurred.';
        }
      } else {
        generalErrorMessage.value = 'Please correct the errors above.';
      }
    };

    const handleDelete = () => {
      if (confirm('Are you sure you want to delete this task?')) {
        emit('delete', props.task.id);
      }
    };

    return {
      taskData,
      existingAttachments,
      tagInput,
      errors,
      generalErrorMessage,
      today,
      addTag,
      removeTag,
      handleFileUpload,
      attachmentsToDelete,
      removeExistingAttachment,
      removeNewAttachment,
      handleSubmit,
      handleDelete,
    };
  },
};
</script>

<style scoped>
.task-form label {
  display: block;
  margin-top: 10px;
  font-weight: bold;
}

.task-form input,
.task-form textarea,
.task-form select {
  width: 95%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.error-message {
  color: red;
  font-size: 0.9rem;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
}

.tags-input {
  margin-top: 5px;
}

.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: 5px;
}

.tag {
  background-color: #e0e0e0;
  padding: 0.3em 0.6em;
  border-radius: 3px;
  font-size: 0.9em;
  display: flex;
  align-items: center;
}

.tag button {
  margin-left: 5px;
  background: none;
  border: none;
  cursor: pointer;
}
</style>
