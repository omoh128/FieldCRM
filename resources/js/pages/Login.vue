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

      <h1 class="login-title">Welcome back</h1>
      <p class="login-sub">Sign in to your account</p>

      <!-- Error banner -->
      <div v-if="error" class="error-banner">
        {{ error }}
      </div>

      <div class="form">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input
            v-model="email"
            type="email"
            class="form-input"
            placeholder="you@example.com"
            @keydown.enter="login"
            autocomplete="email"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="password-wrap">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="••••••••"
              @keydown.enter="login"
              autocomplete="current-password"
            />
            <button class="toggle-pw" type="button" @click="showPassword = !showPassword">
              <!-- Eye open -->
              <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <!-- Eye closed -->
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <button class="btn-login" @click="login" :disabled="loading">
          <span v-if="!loading">Sign in</span>
          <span v-else class="spinner"></span>
        </button>

        <p class="register-link">
          Don't have an account?
          <router-link to="/register">Create one</router-link>
        </p>
      </div>

      <!-- Dev hint -->
      <div class="dev-hint">
        <strong>Demo:</strong> admin@fieldcrm.test / password
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const router   = useRouter()
const email    = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')
const showPassword = ref(false)

const login = async () => {
  error.value = ''
  if (!email.value || !password.value) {
    error.value = 'Please enter your email and password.'
    return
  }

  loading.value = true
  try {
    const { data } = await api.post('/auth/login', {
      email:    email.value,
      password: password.value,
    })

    // Store token and user for use across all pages
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user',  JSON.stringify(data.user))

    // Redirect based on role
    if (data.user.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data.message || 'Invalid credentials.'
    } else {
      error.value = 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
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
  max-width: 400px;
  background: #131726;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.4);
}

.login-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
}
.login-logo span {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #e8eaf2;
}

.login-title {
  font-family: 'Syne', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #e8eaf2;
  margin-bottom: 6px;
}
.login-sub {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 28px;
}

.error-banner {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.25);
  color: #f87171;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  margin-bottom: 20px;
}

.form { display: flex; flex-direction: column; gap: 18px; }

.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6b7280;
  font-weight: 500;
}

.form-input {
  background: #1a1f35;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 10px 14px;
  color: #e8eaf2;
  font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}
.form-input:focus { border-color: #f5a623; }
.form-input::placeholder { color: #4b5563; }

.password-wrap { position: relative; }
.password-wrap .form-input { padding-right: 42px; }
.toggle-pw {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 2px;
  display: flex;
  align-items: center;
}
.toggle-pw:hover { color: #e8eaf2; }
.toggle-pw svg { width: 16px; height: 16px; }

.btn-login {
  width: 100%;
  padding: 11px;
  background: #f5a623;
  color: #000;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  margin-top: 4px;
}
.btn-login:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-login:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.register-link {
  text-align: center;
  font-size: 13px;
  color: #6b7280;
}
.register-link a { color: #f5a623; text-decoration: none; }
.register-link a:hover { text-decoration: underline; }

.dev-hint {
  margin-top: 28px;
  padding: 12px 14px;
  background: rgba(245,166,35,0.06);
  border: 1px solid rgba(245,166,35,0.15);
  border-radius: 8px;
  font-size: 12px;
  color: #9ca3af;
  text-align: center;
}
.dev-hint strong { color: #f5a623; }
</style>