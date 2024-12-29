<template>
  <AuthForm buttonText="Login" :onSubmit="login" :isLogin="true" :errorMessages="errorMessages" />
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AuthForm from '../components/AuthForm.vue';

export default {
  components: { AuthForm },
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const errorMessages = ref('');

    const login = async (userData) => {
      try {
        await authStore.login(userData);
        router.push('/');
      } catch (error) {
        errorMessages.value = error.response?.data || 'Failed to login';
      }
    };

    return { login, errorMessages };
  },
};
</script>
