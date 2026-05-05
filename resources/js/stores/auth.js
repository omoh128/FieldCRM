// stores/auth.js
import { defineStore } from 'pinia'
import api from '@/api'

export const useAuthStore = defineStore('auth', {
  actions: {
    async login(credentials) {
      const { data } = await api.post('/auth/login', credentials)
      localStorage.setItem('auth_token', data.token)
      // Set your user state here
    }
  }
})