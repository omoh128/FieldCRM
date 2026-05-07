<template>
  <div class="login-page">
    <div class="login-card">

      <!-- Logo -->
      <div class="login-logo">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
          <rect width="32" height="32" rx="10" fill="#f5a623"/>
          <path d="M8 16L14 22L24 10" stroke="#000" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>FieldCRM</span>
      </div>

      <h1 class="login-title">Create Account</h1>
      <p class="login-sub">Join FieldCRM as a Business Owner</p>

      <!-- Error banner -->
      <div v-if="error" class="error-banner">
        {{ error }}
      </div>

      <div class="form">
        <!-- Full Name -->
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input v-model="form.name" type="text" class="form-input" placeholder="Omomoh Agiogu" />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="form.email" type="email" class="form-input" placeholder="you@example.com" />
        </div>

        <div class="form-row">
          <!-- Company -->
          <div class="form-group">
            <label class="form-label">Company</label>
            <input v-model="form.company" type="text" class="form-input" placeholder="Webb Builders" />
          </div>
          <!-- Phone -->
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input v-model="form.phone" type="text" class="form-input" placeholder="+1..." />
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="form.password" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <button class="btn-login" @click="handleRegister" :disabled="loading">
          <span v-if="!loading">Create Account</span>
          <span v-else class="spinner"></span>
        </button>

        <p class="register-link">
          Already have an account?
          <router-link to="/login">Sign in</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const router = useRouter()
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  company: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const handleRegister = async () => {
  error.value = ''

  // Basic frontend validation
  if (!form.name || !form.email || !form.password) {
    error.value = 'Please fill in all required fields.'
    return
  }

  loading.value = true
  try {
    // Your AuthController@register handles this
    const { data } = await api.post('/auth/register', form)

    // Store token and user (exactly like Login)
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))

    // Redirect to owner dashboard
    router.push('/')
  } catch (err) {
    if (err.response?.status === 422) {
      // Show the first validation error from Laravel
      const errors = err.response.data.errors
      error.value = errors ? Object.values(errors)[0][0] : err.response.data.message
    } else {
      error.value = 'Registration failed. Please check your connection.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Reusing your Login.vue styles */
.login-page {
  min-height: 100vh;
  background: #0c0f1a;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 460px; /* Slightly wider for the row */
  background: #131726;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.4);
}

.login-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
.login-logo span { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: #e8eaf2; }
.login-title { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; color: #e8eaf2; margin-bottom: 6px; }
.login-sub { font-size: 13px; color: #6b7280; margin-bottom: 28px; }

.error-banner {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.25);
  color: #f87171;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  margin-bottom: 20px;
}

.form { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; }

.form-input {
  background: #1a1f35;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 10px 14px;
  color: #e8eaf2;
  font-size: 13px;
  outline: none;
  transition: border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}
.form-input:focus { border-color: #f5a623; }

.btn-login {
  width: 100%;
  padding: 11px;
  background: #f5a623;
  color: #000;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  margin-top: 10px;
}
.btn-login:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-login:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(0,0,0,0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.register-link { text-align: center; font-size: 13px; color: #6b7280; margin-top: 10px; }
.register-link a { color: #f5a623; text-decoration: none; }

@media (max-width: 500px) {
  .form-row { grid-template-columns: 1fr; }
}
</style>
