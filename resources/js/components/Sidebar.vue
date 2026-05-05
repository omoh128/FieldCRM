<template>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">F</div>
      <router-link to="/"><h2>FieldCRM</h2></router-link> 
    </div>

    <p class="nav-label">Menu</p>

    <nav>
      <ul>
        <!-- Admin nav -->
        <template v-if="isAdmin">
          <li :class="{ active: $route.name === 'admin' }">
            <router-link to="/admin" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              Owners
            </router-link>
          </li>
        </template>

        <!-- Owner nav -->
        <template v-else>
          <li :class="{ active: $route.name === 'dashboard' }">
            <router-link to="/" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
              Dashboard
            </router-link>
          </li>
          <li :class="{ active: $route.name === 'leads' }">
            <router-link to="/leads" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              Leads
            </router-link>
          </li>
          <li :class="{ active: $route.name === 'customers' }">
            <router-link to="/customers" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Customers
            </router-link>
          </li>
          <li :class="{ active: $route.name === 'invoices' }">
            <router-link to="/invoices" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              Invoices
            </router-link>
          </li>
          <li :class="{ active: $route.name === 'jobs' }">
            <router-link to="/jobs" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
              </svg>
              Jobs
            </router-link>
          </li>
          <li :class="{ active: $route.name === 'settings' }">
            <router-link to="/settings" class="nav-link">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
              Settings
            </router-link>
          </li>
        </template>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="avatar">{{ userInitials }}</div>
        <div class="user-info">
          <div class="user-name">{{ user?.name || 'User' }}</div>
          <div class="user-role">{{ isAdmin ? 'Admin' : 'Owner' }}</div>
        </div>
      </div>
      <button class="logout-btn" @click="logout" title="Sign out">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const router = useRouter()

const user      = computed(() => JSON.parse(localStorage.getItem('auth_user') || 'null'))
const isAdmin   = computed(() => user.value?.role === 'admin')
const userInitials = computed(() => {
  const name = user.value?.name || ''
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || '?'
})

const logout = async () => {
  try {
    await api.post('/auth/logout')
  } catch (_) {
    // If token already expired, still clear local state
  }
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 220px;
  background: #131726;
  border-right: 1px solid rgba(255,255,255,0.07);
  display: flex;
  flex-direction: column;
  padding: 28px 0;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 10;
}

/* Logo */
.sidebar-logo {
  padding: 0 24px 32px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo-mark {
  width: 32px; height: 32px;
  background: #f5a623;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 14px;
  color: #000;
  flex-shrink: 0;
}
.sidebar-logo h2 {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 17px;
  letter-spacing: 0.02em;
  color: #e8eaf2;
}

/* Nav label */
.nav-label {
  font-size: 10px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #6b7280;
  padding: 0 24px 8px;
  font-weight: 500;
}

/* Nav items */
nav { flex: 1; padding: 0 12px; }
nav ul { list-style: none; margin: 0; padding: 0; }

nav ul li {
  border-radius: 8px;
  margin-bottom: 2px;
  transition: background 0.2s;
}
nav ul li:hover {
  background: #1a1f35;
}
nav ul li.active {
  background: rgba(245,166,35,0.12);
}

/* Remove router-link default styles */
.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  color: #6b7280;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 400;
  transition: color 0.2s;
  width: 100%;
  border-radius: 8px;
}
.nav-link:hover { color: #e8eaf2; }

li.active .nav-link {
  color: #f5a623;
  font-weight: 500;
}

.icon {
  width: 16px; height: 16px;
  opacity: 0.6;
  flex-shrink: 0;
  transition: opacity 0.2s;
}
.nav-link:hover .icon { opacity: 1; }
li.active .nav-link .icon { opacity: 1; }

/* Leads badge */
.badge-count {
  margin-left: auto;
  background: rgba(245,166,35,0.15);
  color: #f5a623;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 20px;
  flex-shrink: 0;
}

/* Footer */
.sidebar-footer {
  padding: 20px 24px 0;
  border-top: 1px solid rgba(255,255,255,0.07);
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 8px;
}
.user-pill { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f5a623, #f97316);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; color: #000;
  flex-shrink: 0;
}
.user-info { min-width: 0; }
.user-name { font-size: 13px; font-weight: 500; color: #e8eaf2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 11px; color: #6b7280; }

.logout-btn {
  width: 30px; height: 30px; border-radius: 7px; flex-shrink: 0;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.07);
  color: #6b7280; cursor: pointer; display: flex; align-items: center;
  justify-content: center; transition: all 0.2s;
}
.logout-btn:hover { background: rgba(239,68,68,0.12); color: #f87171; border-color: rgba(239,68,68,0.2); }
.logout-btn svg { width: 14px; height: 14px; }

/* Responsive */
@media (max-width: 720px) {
  .sidebar { transform: translateX(-100%); }
}
</style>