import { createRouter, createWebHistory } from 'vue-router'
import Login from './pages/Login.vue'

const routes = [
  // ── Public ──────────────────────────────────────────────────────────────
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { public: true },
  },

  // ── Admin only ───────────────────────────────────────────────────────────
  {
    path: '/admin',
    name: 'admin',
    component: () => import('./pages/Admin.vue'),
    meta: { role: 'admin' },
  },

  // ── Owner routes ─────────────────────────────────────────────────────────
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
  {
    path: '/customers',
    name: 'customers',
    component: () => import('./pages/Customers.vue'),
    meta: { role: 'owner' },
  },
  {
    path: '/jobs',
    name: 'jobs',
    component: () => import('./pages/Jobs.vue'),
    meta: { role: 'owner' },
  },
  {
    path: '/invoices',
    name: 'invoices',
    component: () => import('./pages/Invoices.vue'),
    meta: { role: 'owner' },
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('./pages/Settings.vue'),
    meta: { role: 'owner' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ── Auth + role guard ─────────────────────────────────────────────────────────
router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  const user  = JSON.parse(localStorage.getItem('auth_user') || 'null')
  const role  = user?.role

  // Not logged in → login
  if (!to.meta.public && !token) return { name: 'login' }

  // Already logged in → redirect away from /login
  if (to.meta.public && token) {
    return role === 'admin' ? { name: 'admin' } : { name: 'dashboard' }
  }

  // Admin trying to access owner routes → redirect to admin panel
  if (role === 'admin' && to.meta.role === 'owner') return { name: 'admin' }

  // Owner trying to access admin panel → redirect to dashboard
  if (role === 'owner' && to.meta.role === 'admin') return { name: 'dashboard' }
})

export default router
