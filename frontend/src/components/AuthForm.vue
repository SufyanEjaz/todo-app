<template>
  <div class="auth-container">
    <form @submit.prevent="handleSubmit" class="auth-form">
      <h1>{{ !isLogin ? "Register" : "Login" }}</h1>
      <input
        v-model="name"
        type="name"
        v-if="!isLogin"
        placeholder="Full Name"
        class="auth-input"
      />
      <p v-if="errorMessages.errors?.name" class="error-message">{{ errorMessages.errors.name[0] }}</p>
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="auth-input"
      />
      <p v-if="errorMessages.errors?.email" class="error-message">{{ errorMessages.errors.email[0] }}</p>
      <input
        v-model="password"
        type="password"
        placeholder="Password"
        class="auth-input"
      />
      <p v-if="errorMessages.errors?.password" class="error-message">{{ errorMessages.errors.password[0] }}</p>
      <input
        v-model="confirm_password"
        name="confirm_password"
        type="password"
        v-if="!isLogin"
        placeholder="Confirm Password"
        class="auth-input"
      />
      <p v-if="errorMessages.errors?.confirm_password" class="error-message">{{ errorMessages.errors.confirm_password[0] }}</p>
      <p v-if="errorMessages?.message" class="error-message">{{ errorMessages.message }}</p>
      <button type="submit" class="auth-button">{{ buttonText }}</button>
      <p class="switch-auth">
        <router-link :to="isLogin ? '/register' : '/login'">
          {{ isLogin ? "Don't have an account? Register" : "Already have an account? Login" }}
        </router-link>
      </p>
    </form>
  </div>
</template>

<script>
export default {
  props: ["buttonText", "onSubmit", "isLogin", "errorMessages"],
  data() {
    return {
      name: "",
      email: "",
      password: "",
      confirm_password: "",
    };
  },
  methods: {
    handleSubmit() {
      const userData = this.isLogin
        ? { email: this.email, password: this.password }
        : {
            name: this.name,
            email: this.email,
            password: this.password,
            confirm_password: this.confirm_password,
          };
      this.onSubmit(userData);
    },
  },
};
</script>

<style scoped>
/* Container styling to center the form */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(to right, #3f51b5, #2196f3);
}

/* Form styling */
.auth-form {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  padding: 2em;
  width: 100%;
  max-width: 600px; /* Increased max width for larger screens */
  display: flex;
  flex-direction: column;
}

/* Input fields styling */
.auth-input {
  font-size: 1rem;
  padding: 0.75em 1em;
  margin-bottom: 1em;
  border: 1px solid #ccc;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.3s ease;
  /* width: 100%; */
}

.auth-input:focus {
  border-color: #3f51b5;
}

/* Submit button styling */
.auth-button {
  font-size: 1rem;
  padding: 0.75em 1em;
  background-color: #3f51b5;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  width: 100%;
}

.auth-button:hover {
  background-color: #2c3e90;
}

.error-message {
  color: red;
  font-size: 0.9rem;
  margin-top: 10px;
}
.switch-auth {
  margin-top: 15px;
}
.switch-auth a {
  color: #3f51b5;
  text-decoration: underline;
  cursor: pointer;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .auth-form {
    max-width: 500px;
    padding: 1.8em;
  }
}

@media (max-width: 768px) {
  .auth-form {
    max-width: 400px;
    padding: 1.5em;
  }

  .auth-input,
  .auth-button {
    font-size: 0.9rem;
    padding: 0.65em;
  }
}

@media (max-width: 480px) {
  .auth-form {
    max-width: 90%;
    padding: 1.25em;
  }

  .auth-input,
  .auth-button {
    font-size: 0.85rem;
    padding: 0.6em;
  }
}
</style>
