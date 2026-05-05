<template>
  <div class="dashboard">
    <Sidebar />

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Settings</h1>
          <p>Manage your account and preferences</p>
        </div>
      </header>

      <div class="content">
        <div class="settings-layout">

          <!-- ── SETTINGS NAV ── -->
          <div class="settings-nav">
            <button
              v-for="item in navItems"
              :key="item.id"
              :class="['settings-nav-item', { active: activePanel === item.id, 'danger-nav-item': item.id === 'danger' }]"
              @click="activePanel = item.id"
            >
              <!-- Profile -->
              <svg v-if="item.id === 'profile'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <!-- Team -->
              <svg v-else-if="item.id === 'team'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <!-- Notifications -->
              <svg v-else-if="item.id === 'notifications'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <!-- Appearance -->
              <svg v-else-if="item.id === 'appearance'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
              </svg>
              <!-- Billing -->
              <svg v-else-if="item.id === 'billing'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
              <!-- Integrations -->
              <svg v-else-if="item.id === 'integrations'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
              </svg>
              <!-- Danger -->
              <svg v-else-if="item.id === 'danger'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              {{ item.label }}
            </button>
          </div>

          <!-- ── PANELS ── -->
          <div>

            <!-- PROFILE -->
            <div v-show="activePanel === 'profile'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Personal Information</h3>
                    <p>Update your name, email, and profile picture</p>
                  </div>
                  <button class="btn btn-primary btn-sm" @click="saveProfile" :disabled="isSaving">{{ isSaving ? 'Saving…' : 'Save Changes' }}</button>
                </div>
                <div class="settings-card-body">
                  <div class="avatar-upload" style="margin-bottom:24px">
                    <div class="avatar-large">JR</div>
                    <div class="avatar-upload-info">
                      <h4>Profile Photo</h4>
                      <p>JPG, PNG or GIF. Max 2MB.</p>
                      <button class="btn btn-ghost btn-sm">Upload Photo</button>
                    </div>
                  </div>
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">First Name</label>
                      <input v-model="profile.firstName" class="form-input" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Name</label>
                      <input v-model="profile.lastName" class="form-input" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Email</label>
                      <input v-model="profile.email" class="form-input" type="email" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Phone</label>
                      <input v-model="profile.phone" class="form-input" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Role</label>
                      <select v-model="profile.role" class="form-select">
                        <option>Admin</option>
                        <option>Manager</option>
                        <option>Field Tech</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Timezone</label>
                      <select v-model="profile.timezone" class="form-select">
                        <option>America/Chicago (CT)</option>
                        <option>America/New_York (ET)</option>
                        <option>America/Los_Angeles (PT)</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Password &amp; Security</h3>
                    <p>Keep your account secure</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div class="form-grid">
                    <div class="form-group full">
                      <label class="form-label">Current Password</label>
                      <input v-model="password.current" class="form-input" type="password" placeholder="••••••••" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">New Password</label>
                      <input v-model="password.next" class="form-input" type="password" placeholder="••••••••" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Confirm Password</label>
                      <input v-model="password.confirm" class="form-input" type="password" placeholder="••••••••" />
                    </div>
                  </div>
                  <div style="margin-top:16px">
                    <button class="btn btn-primary" @click="updatePassword">Update Password</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- TEAM -->
            <div v-show="activePanel === 'team'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Team Members</h3>
                    <p>Manage who has access to FieldCRM</p>
                  </div>
                  <button class="btn btn-primary btn-sm" @click="showToast('Invite sent!')">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Invite Member
                  </button>
                </div>
                <div class="settings-card-body" style="padding:0">
                  <table class="team-table" style="width:calc(100% - 48px);margin:0 24px">
                    <thead>
                      <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="member in team" :key="member.email">
                        <td>
                          <div class="entity-cell">
                            <div class="e-avatar" :style="{ background: member.color + '22', color: member.color }">
                              {{ member.initials }}
                            </div>
                            <div>
                              <div class="e-name">{{ member.name }}</div>
                              <div class="e-sub">{{ member.email }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="text-muted2">{{ member.role }}</td>
                        <td>
                          <span :class="['badge', member.status === 'active' ? 'badge-won' : 'badge-quoted']">
                            {{ member.status === 'active' ? 'Active' : 'Pending' }}
                          </span>
                        </td>
                        <td class="text-muted">{{ member.joined }}</td>
                        <td>
                          <div class="row-actions" style="opacity:1">
                            <button
                              v-if="member.name !== 'Jake Russo'"
                              class="action-btn del"
                              @click="removeMember(member)"
                            >
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14H6L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4h6v2"/>
                              </svg>
                            </button>
                            <span v-else class="you-label">You</span>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- NOTIFICATIONS -->
            <div v-show="activePanel === 'notifications'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Email Notifications</h3>
                    <p>Choose which emails you receive</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div v-for="item in emailNotifications" :key="item.title" class="settings-row">
                    <div class="row-info">
                      <h4>{{ item.title }}</h4>
                      <p>{{ item.desc }}</p>
                    </div>
                    <label class="toggle">
                      <input v-model="item.enabled" type="checkbox" />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Push / SMS</h3>
                    <p>Real-time alerts to your device</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div v-for="item in pushNotifications" :key="item.title" class="settings-row">
                    <div class="row-info">
                      <h4>{{ item.title }}</h4>
                      <p>{{ item.desc }}</p>
                    </div>
                    <label class="toggle">
                      <input v-model="item.enabled" type="checkbox" />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- APPEARANCE -->
            <div v-show="activePanel === 'appearance'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Theme &amp; Accent</h3>
                    <p>Personalise your workspace</p>
                  </div>
                  <button class="btn btn-primary btn-sm" @click="saveNotifications">Save</button>
                </div>
                <div class="settings-card-body">
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Color Mode</h4>
                      <p>Switch between dark and light interface</p>
                    </div>
                    <select v-model="appearance.colorMode" class="filter-select">
                      <option>Dark</option>
                      <option>Light</option>
                      <option>System</option>
                    </select>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Accent Color</h4>
                      <p>Primary color used for highlights and buttons</p>
                    </div>
                    <div class="color-options">
                      <div
                        v-for="color in accentColors"
                        :key="color"
                        :class="['color-swatch', { selected: appearance.accentColor === color }]"
                        :style="{ background: color }"
                        @click="selectColor(color)"
                      ></div>
                    </div>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Compact Mode</h4>
                      <p>Reduce padding for a denser layout</p>
                    </div>
                    <label class="toggle">
                      <input v-model="appearance.compactMode" type="checkbox" />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Sidebar Condensed</h4>
                      <p>Show only icons in the sidebar</p>
                    </div>
                    <label class="toggle">
                      <input v-model="appearance.sidebarCondensed" type="checkbox" />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- BILLING -->
            <div v-show="activePanel === 'billing'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Current Plan</h3>
                    <p>You are on the <strong class="accent-text">Pro</strong> plan</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div class="plan-cards" style="margin-bottom:24px">
                    <div v-for="plan in plans" :key="plan.name" :class="['plan-card', { current: plan.current }]">
                      <div class="plan-name">{{ plan.name }}{{ plan.current ? ' ✓ Current' : '' }}</div>
                      <div class="plan-price">
                        ${{ plan.price }} <span>/ month</span>
                      </div>
                      <div class="plan-features">
                        <div v-for="feat in plan.features" :key="feat" class="plan-feat">{{ feat }}</div>
                      </div>
                      <button
                        v-if="!plan.current"
                        class="btn btn-ghost"
                        style="margin-top:14px;width:100%;justify-content:center"
                        @click="showToast('Downgrade scheduled')"
                      >Downgrade</button>
                    </div>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Payment Method</h4>
                      <p>Visa ending in 4242 · Expires 12/27</p>
                    </div>
                    <button class="btn btn-ghost btn-sm" @click="showToast('Redirecting to billing portal...')">Update Card</button>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Next Billing Date</h4>
                      <p>March 25, 2026</p>
                    </div>
                    <button class="btn btn-ghost btn-sm" @click="showToast('Invoice downloaded')">Download Invoice</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- INTEGRATIONS -->
            <div v-show="activePanel === 'integrations'">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div>
                    <h3>Connected Apps</h3>
                    <p>Sync FieldCRM with tools you already use</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div v-for="app in integrations" :key="app.name" class="settings-row">
                    <div class="row-info integration-info">
                      <span class="integration-icon">{{ app.icon }}</span>
                      <div>
                        <h4>{{ app.name }}</h4>
                        <p>{{ app.desc }}</p>
                      </div>
                    </div>
                    <button
                      :class="['btn', app.connected ? 'btn-ghost' : 'btn-primary', 'btn-sm']"
                      @click="toggleIntegration(app)"
                    >
                      {{ app.connected ? 'Disconnect' : 'Connect' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- DANGER ZONE -->
            <div v-show="activePanel === 'danger'">
              <div class="settings-card danger-zone">
                <div class="settings-card-header">
                  <div>
                    <h3>⚠ Danger Zone</h3>
                    <p>Irreversible and destructive actions</p>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Export All Data</h4>
                      <p>Download a full backup of your CRM data as CSV</p>
                    </div>
                    <button class="btn btn-ghost btn-sm" @click="showToast('Export preparing...')">Export Backup</button>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Reset CRM Data</h4>
                      <p>Delete all leads, customers, and jobs. Team accounts remain.</p>
                    </div>
                    <button class="btn btn-danger btn-sm" @click="showToast('Enter RESET to confirm — this cannot be undone', 'error')">Reset Data</button>
                  </div>
                  <div class="settings-row">
                    <div class="row-info">
                      <h4>Delete Account</h4>
                      <p>Permanently delete your FieldCRM account and all data</p>
                    </div>
                    <button class="btn btn-danger btn-sm" @click="showToast('Deletion request submitted', 'error')">Delete Account</button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </main>

    <!-- ── TOAST ── -->
    <transition name="toast-slide">
      <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
        <span>{{ toast.type === 'success' ? '✓' : toast.type === 'error' ? '✕' : 'ℹ' }}</span>
        {{ toast.message }}
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import Sidebar from '@/components/Sidebar.vue'

// ── NAV ───────────────────────────────────────────────────────────────────────
const navItems = [
  { id: 'profile',       label: 'Profile' },
  { id: 'team',          label: 'Team' },
  { id: 'notifications', label: 'Notifications' },
  { id: 'appearance',    label: 'Appearance' },
  { id: 'billing',       label: 'Billing' },
  { id: 'integrations',  label: 'Integrations' },
]
const activePanel = ref('profile')
const isSaving    = ref(false)

// ── PROFILE ───────────────────────────────────────────────────────────────────
const profile  = ref({ name: '', email: '' })
const password = ref({ current: '', next: '', confirm: '', showCurrent: false, showNext: false })

const fetchProfile = async () => {
  const { data } = await api.get('/settings/profile')
  profile.value = { name: data.name, email: data.email }
}

const saveProfile = async () => {
  isSaving.value = true
  try {
    const { data } = await api.put('/settings/profile', { name: profile.value.name, email: profile.value.email })
    // Update localStorage so sidebar shows new name
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}')
    localStorage.setItem('auth_user', JSON.stringify({ ...user, name: data.name, email: data.email }))
    showToast('Profile updated')
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not save profile', 'error')
  } finally { isSaving.value = false }
}

const updatePassword = async () => {
  if (!password.value.current) return showToast('Current password is required', 'error')
  if (password.value.next !== password.value.confirm) return showToast('Passwords do not match', 'error')
  if (password.value.next.length < 8) return showToast('Password must be at least 8 characters', 'error')

  isSaving.value = true
  try {
    await api.put('/settings/profile', {
      current_password:      password.value.current,
      password:              password.value.next,
      password_confirmation: password.value.confirm,
    })
    showToast('Password updated')
    password.value = { current: '', next: '', confirm: '', showCurrent: false, showNext: false }
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not update password', 'error')
  } finally { isSaving.value = false }
}

// ── TEAM ──────────────────────────────────────────────────────────────────────
const team           = ref([])
const showAddMember  = ref(false)
const newMember      = ref({ name: '', email: '', role: 'technician', phone: '' })
const COLORS         = ['#f97316','#8b5cf6','#06b6d4','#22c55e','#ef4444','#f59e0b','#3b82f6','#ec4899']
const memberColor    = (m) => COLORS[(m.id - 1) % COLORS.length]
const memberInitials = (m) => (m.name || '').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()

const fetchTeam = async () => {
  const { data } = await api.get('/settings/team')
  team.value = data
}

const addMember = async () => {
  if (!newMember.value.name.trim()) return showToast('Name is required', 'error')
  try {
    const { data } = await api.post('/settings/team', newMember.value)
    team.value.unshift(data)
    showAddMember.value = false
    newMember.value = { name: '', email: '', role: 'technician', phone: '' }
    showToast('Team member added')
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not add member', 'error')
  }
}

const removeMember = async (member) => {
  try {
    await api.delete(`/settings/team/${member.id}`)
    team.value = team.value.filter(m => m.id !== member.id)
    showToast(`${member.name} removed`)
  } catch (err) {
    showToast('Could not remove member', 'error')
  }
}

// ── NOTIFICATIONS ─────────────────────────────────────────────────────────────
const emailNotifications = ref([
  { key: 'notif_new_lead',       title: 'New lead assigned',     desc: 'When a lead is assigned to you',               enabled: true  },
  { key: 'notif_lead_status',    title: 'Lead status changes',   desc: 'When a lead moves to a new stage',             enabled: true  },
  { key: 'notif_job_scheduled',  title: 'Job scheduled',         desc: 'When a new job is added to your calendar',     enabled: true  },
  { key: 'notif_job_completed',  title: 'Job completed',         desc: 'When a field job is marked complete',          enabled: false },
  { key: 'notif_weekly_digest',  title: 'Weekly digest',         desc: 'Summary of activity every Monday',             enabled: true  },
])

const pushNotifications = ref([
  { key: 'push_browser',  title: 'Push notifications',  desc: 'Browser and mobile push alerts',        enabled: true  },
  { key: 'push_sms',      title: 'SMS alerts',          desc: 'Text messages for high-priority jobs',  enabled: false },
])

const saveNotifications = async () => {
  const prefs = {}
  ;[...emailNotifications.value, ...pushNotifications.value].forEach(n => { prefs[n.key] = n.enabled ? '1' : '0' })
  try {
    await api.put('/settings/preferences', { preferences: prefs })
    showToast('Notifications saved')
  } catch (err) { showToast('Could not save', 'error') }
}

// ── APPEARANCE ────────────────────────────────────────────────────────────────
const accentColors = ['#f5a623','#3b82f6','#22c55e','#a855f7','#ef4444','#ec4899','#06b6d4','#f97316']
const appearance   = ref({ accentColor: '#f5a623', compactMode: false })

const selectColor = async (color) => {
  appearance.value.accentColor = color
  try {
    await api.put('/settings/preferences', { preferences: { accent_color: color } })
    showToast('Accent color saved')
  } catch (err) { showToast('Could not save', 'error') }
}

const saveAppearance = async () => {
  try {
    await api.put('/settings/preferences', { preferences: {
      accent_color:  appearance.value.accentColor,
      compact_mode:  appearance.value.compactMode ? '1' : '0',
    }})
    showToast('Appearance saved')
  } catch (err) { showToast('Could not save', 'error') }
}

// ── BILLING ───────────────────────────────────────────────────────────────────
const plans = ref([
  { name: 'Starter', price: 0,  current: false, features: ['Up to 3 users', '50 leads / month', 'Basic reporting'] },
  { name: 'Pro',     price: 49, current: true,  features: ['Unlimited users', 'Unlimited leads', 'Advanced analytics', 'CSV export', 'Priority support'] },
])

// ── INTEGRATIONS ──────────────────────────────────────────────────────────────
const stripeKey = ref('')

const integrations = ref([
  { key: 'stripe',    name: 'Stripe',          desc: 'Accept card payments from customers',        icon: '💳', connected: false },
  { key: 'gcal',      name: 'Google Calendar', desc: 'Sync jobs to Google Calendar',               icon: '📅', connected: false },
  { key: 'quickbooks',name: 'QuickBooks',      desc: 'Auto-create invoices from completed jobs',   icon: '📒', connected: false },
  { key: 'slack',     name: 'Slack',           desc: 'Send lead and job alerts to Slack',          icon: '💬', connected: false },
  { key: 'zapier',    name: 'Zapier',          desc: 'Connect with 5000+ other apps',              icon: '⚡', connected: false },
  { key: 'twilio',    name: 'Twilio',          desc: 'Send SMS updates to customers',              icon: '📱', connected: false },
])

const saveStripe = async () => {
  if (!stripeKey.value.trim()) return showToast('Enter your Stripe publishable key', 'error')
  try {
    await api.put('/settings/preferences', { preferences: { stripe_key: stripeKey.value } })
    integrations.value.find(i => i.key === 'stripe').connected = true
    showToast('Stripe connected')
  } catch (err) { showToast('Could not save', 'error') }
}

// ── LOAD ALL ──────────────────────────────────────────────────────────────────
const loadPreferences = async () => {
  try {
    const { data } = await api.get('/settings/preferences')
    if (data.accent_color) appearance.value.accentColor = data.accent_color
    if (data.compact_mode) appearance.value.compactMode = data.compact_mode === '1'
    if (data.stripe_key)   { stripeKey.value = data.stripe_key; integrations.value.find(i => i.key === 'stripe').connected = true }
    emailNotifications.value.forEach(n => { if (data[n.key] !== undefined) n.enabled = data[n.key] === '1' })
    pushNotifications.value.forEach(n => { if (data[n.key] !== undefined) n.enabled = data[n.key] === '1' })
  } catch (_) {}
}

onMounted(async () => {
  await fetchProfile()
  await fetchTeam()
  await loadPreferences()
})

// ── TOAST ─────────────────────────────────────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 2800)
}
</script>

<style scoped>
.dashboard {
  display: flex;
  min-height: 100vh;
  background: #0c0f1a;
}

.main {
  margin-left: 220px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.content {
  padding: 28px 32px;
  flex: 1;
}

.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 20px;
  margin-bottom: 24px;
}

/* ── MODAL ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: #131726;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  width: 100%;
  max-width: 480px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.5);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
.modal-header h2 {
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #e8eaf2;
}
.modal-close {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: 6px;
  transition: color 0.2s, background 0.2s;
}
.modal-close:hover { color: #e8eaf2; background: rgba(255,255,255,0.06); }

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6b7280;
  font-weight: 500;
}
.form-group input,
.form-group select {
  background: #1a1f35;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 9px 12px;
  color: #e8eaf2;
  font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color 0.2s;
  appearance: none;
}
.form-group input:focus,
.form-group select:focus { border-color: #f5a623; }
.form-group input::placeholder { color: #4b5563; }

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid rgba(255,255,255,0.07);
}

.btn-cancel {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  color: #6b7280;
  padding: 8px 16px;
  font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover { color: #e8eaf2; border-color: rgba(255,255,255,0.2); }

.btn-save {
  background: #f5a623;
  border: none;
  border-radius: 8px;
  color: #000;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 600;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.15s;
}
.btn-save:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── TRANSITIONS ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
  .bottom-grid { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
  .main { margin-left: 0; }
  .content { padding: 20px 16px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>