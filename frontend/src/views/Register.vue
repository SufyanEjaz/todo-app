<template>
  <AuthForm buttonText="Register" :onSubmit="register" :isLogin="false" :errorMessages="errorMessages" />
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

    const register = async (userData) => {
      try {
        await authStore.register(userData);
        router.push('/');
      } catch (error) {
        errorMessages.value = error.response?.data || 'Failed to register';
      }
    };

    return { register, errorMessages };
  },
};
</script>
