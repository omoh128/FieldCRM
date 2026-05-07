import { createRouter, createWebHistory } from 'vue-router'
import Login from './pages/Login.vue'
// Import Register directly to ensure it loads immediately
import Register from './pages/Register.vue' 

const routes = [
  // ── Public ──────────────────────────────────────────────────────────────
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { public: true },
  },
  {
    path: '/register', // This MUST match the path in your <router-link>
    name: 'register',
    component: Register,
    meta: { public: true },
  },

  // ── Admin only ───────────────────────────────────────────────────────────
  {
    path: '/admin',
    name: 'admin',
    component: () => import('./pages/Admin.vue'),
    meta: { role: 'admin' },
  },

  // ── Owner/Dashboard routes ────────────────────────────────────────────────
  {
    path: '/',
    name: 'dashboard',
    component: () => import('./pages/Dashboard.vue'),
    meta: { role: 'owner' },
  },
  {
    path: '/leads',
    name: 'leads',
    component: () => import('./pages/Leads.vue'),
    meta: { role: 'owner' },
  },
  // ... other routes (customers, jobs, invoices, settings)
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ── Auth Guard ─────────────────────────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const user  = JSON.parse(localStorage.getItem('auth_user') || 'null')
  const role  = user?.role

  // 1. Allow public pages (Login/Register)
  if (to.meta.public) {
    // If already logged in, don't let them go back to login/register
    if (token) {
      return next(role === 'admin' ? { name: 'admin' } : { name: 'dashboard' })
    }
    return next()
  }

  // 2. Protect all other routes
  if (!token) return next({ name: 'login' })

  // 3. Role-based access
  if (role === 'admin' && to.meta.role === 'owner') return next({ name: 'admin' })
  if (role === 'owner' && to.meta.role === 'admin') return next({ name: 'dashboard' })

  next()
})

export default router