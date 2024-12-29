<template>
    <header class="header">
      <router-link to="/" class="home">Home</router-link>
      <span @click="logout" class="logout-button">Logout</span>
    </header>
  </template>
  
  <script>
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../stores/auth';
  
  export default {
    setup() {
      const authStore = useAuthStore();
      const router = useRouter();
  
      const logout = async () => {
        await authStore.logout();
        router.push('/login');
      };
  
      return { logout };
    },
  };
  </script>
  
  <style scoped>
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1em;
    background-color: #007bff;
    color: #ffffff;
  }
  
  .logout-button {
    background-color: #ffffff;
    color: #3f51b5;
    border: none;
    padding: 0.5em 1em;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
  }
  
  .logout-button:hover {
    background-color: #f0f0f0;
  }

  .home {
    color: white;
    text-decoration: none;
  }
  </style>
  