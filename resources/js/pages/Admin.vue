<template>
  <div class="admin-page">
    <Sidebar />

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Admin Panel</h1>
          <p>Manage owner accounts</p>
        </div>
        <div class="topbar-right">
          <button class="btn btn-primary" @click="openAddModal">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            New Owner
          </button>
        </div>
      </header>

      <div class="content">

        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card stat-blue">
            <div class="stat-label">Total Owners</div>
            <div class="stat-value">{{ owners.length }}</div>
            <div class="stat-sub">Registered accounts</div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-label">Logged in as</div>
            <div class="stat-value" style="font-size:16px">{{ currentUser?.name || '—' }}</div>
            <div class="stat-sub">Admin account</div>
          </div>
        </div>

        <!-- Owners Table -->
        <div class="table-card">
          <div class="table-header">
            <span class="table-title">Owner Accounts</span>
            <div class="search-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
              </svg>
              <input v-model="searchQuery" class="search-input" placeholder="Search owners…" />
            </div>
          </div>

          <table>
            <thead>
              <tr>
                <th>Owner</th>
                <th>Email</th>
                <th>Created</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="4"><div class="empty-state"><p>Loading…</p></div></td>
              </tr>
              <tr v-else-if="filteredOwners.length === 0">
                <td colspan="4">
                  <div class="empty-state">
                    <p>No owners yet</p>
                    <span>Create the first owner account above</span>
                  </div>
                </td>
              </tr>
              <tr v-for="owner in filteredOwners" :key="owner.id">
                <td>
                  <div class="entity-cell">
                    <div class="e-avatar" :style="{ background: ownerColor(owner) + '22', color: ownerColor(owner) }">
                      {{ initials(owner) }}
                    </div>
                    <div>
                      <div class="e-name">{{ owner.name }}</div>
                      <div class="e-sub">Owner</div>
                    </div>
                  </div>
                </td>
                <td class="text-muted">{{ owner.email }}</td>
                <td class="text-muted">{{ formatDate(owner.created_at) }}</td>
                <td>
                  <div class="row-actions" @click.stop>
                    <button class="action-btn del" title="Delete" @click="askDelete(owner)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14H6L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4h6v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </main>

    <!-- ── ADD OWNER MODAL ── -->
    <transition name="fade">
      <div v-if="showFormModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <span class="modal-title">Create Owner Account</span>
            <button class="modal-close" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group full">
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" class="form-input" placeholder="Jane Smith" />
              </div>
              <div class="form-group full">
                <label class="form-label">Email *</label>
                <input v-model="form.email" type="email" class="form-input" placeholder="jane@company.com" />
              </div>
              <div class="form-group full">
                <label class="form-label">Password *</label>
                <div class="password-wrap">
                  <input
                    v-model="form.password"
                    :type="showPw ? 'text' : 'password'"
                    class="form-input"
                    placeholder="Min. 8 characters"
                  />
                  <button class="toggle-pw" type="button" @click="showPw = !showPw">
                    <svg v-if="!showPw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                      <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="closeModal">Cancel</button>
            <button class="btn btn-primary" @click="saveOwner" :disabled="isSaving">
              {{ isSaving ? 'Creating…' : 'Create Owner' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── CONFIRM DELETE ── -->
    <transition name="fade">
      <div v-if="showConfirmModal" class="modal-overlay" @click.self="showConfirmModal = false">
        <div class="confirm-box">
          <div class="confirm-icon">⚠️</div>
          <h3>Delete Owner?</h3>
          <p>
            This will permanently delete <strong>{{ deletingOwner?.name }}</strong>
            and all their leads and jobs.
          </p>
          <div class="confirm-btns">
            <button class="btn btn-ghost" @click="showConfirmModal = false">Cancel</button>
            <button class="btn btn-danger" @click="confirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── TOAST ── -->
    <transition name="toast-slide">
      <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
        <span>{{ toast.type === 'success' ? '✓' : '✕' }}</span>
        {{ toast.message }}
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api'
import Sidebar from '@/components/Sidebar.vue'

const COLORS = ['#f97316','#8b5cf6','#06b6d4','#22c55e','#ef4444','#f59e0b','#3b82f6','#ec4899']

// ── CURRENT USER ──────────────────────────────────────────────────────────────
const currentUser = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))

// ── OWNERS DATA ───────────────────────────────────────────────────────────────
const owners    = ref([])
const isLoading = ref(false)

const fetchOwners = async () => {
  isLoading.value = true
  try {
    const { data } = await api.get('/owners')
    owners.value = data
  } catch (err) {
    showToast('Failed to load owners', 'error')
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchOwners)

// ── SEARCH ────────────────────────────────────────────────────────────────────
const searchQuery = ref('')
const filteredOwners = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return !q ? owners.value : owners.value.filter(o =>
    `${o.name} ${o.email}`.toLowerCase().includes(q)
  )
})

// ── HELPERS ───────────────────────────────────────────────────────────────────
const initials   = (o) => (o.name || '').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
const ownerColor = (o) => COLORS[(o.id - 1) % COLORS.length]
const formatDate = (d) => d
  ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  : '—'

// ── FORM MODAL ────────────────────────────────────────────────────────────────
const showFormModal = ref(false)
const isSaving      = ref(false)
const showPw        = ref(false)

const defaultForm = () => ({ name: '', email: '', password: '' })
const form = ref(defaultForm())

const openAddModal = () => { form.value = defaultForm(); showFormModal.value = true }
const closeModal   = () => { showFormModal.value = false; form.value = defaultForm() }

const saveOwner = async () => {
  if (!form.value.name.trim() || !form.value.email.trim() || !form.value.password) {
    showToast('All fields are required', 'error')
    return
  }
  if (form.value.password.length < 8) {
    showToast('Password must be at least 8 characters', 'error')
    return
  }

  isSaving.value = true
  try {
    const { data } = await api.post('/owners', form.value)
    owners.value.unshift(data)
    closeModal()
    showToast(`Owner ${data.name} created`)
  } catch (err) {
    const msg = err.response?.data?.message || 'Could not create owner'
    showToast(msg, 'error')
    console.error(err)
  } finally {
    isSaving.value = false
  }
}

// ── DELETE ────────────────────────────────────────────────────────────────────
const showConfirmModal = ref(false)
const deletingOwner    = ref(null)

const askDelete = (owner) => { deletingOwner.value = owner; showConfirmModal.value = true }

const confirmDelete = async () => {
  try {
    await api.delete(`/owners/${deletingOwner.value.id}`)
    owners.value = owners.value.filter(o => o.id !== deletingOwner.value.id)
    showConfirmModal.value = false
    showToast('Owner deleted')
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not delete owner', 'error')
  }
}

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
.admin-page { display: flex; min-height: 100vh; background: #0c0f1a; }
.main { margin-left: 220px; flex: 1; display: flex; flex-direction: column; }
.content { padding: 28px 32px; flex: 1; }

.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 32px; border-bottom: 1px solid rgba(255,255,255,0.07);
  background: rgba(19,23,38,0.8); backdrop-filter: blur(10px);
  position: sticky; top: 0; z-index: 5;
}
.topbar-left h1 { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: #e8eaf2; }
.topbar-left p  { font-size: 12px; color: #6b7280; margin-top: 2px; }
.topbar-right   { display: flex; align-items: center; gap: 10px; }

.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 8px; font-size: 13px;
  font-weight: 600; font-family: 'DM Sans', sans-serif;
  cursor: pointer; border: none; transition: all 0.2s;
}
.btn-primary { background: #f5a623; color: #000; }
.btn-primary:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost { background: transparent; color: #6b7280; border: 1px solid rgba(255,255,255,0.1); }
.btn-ghost:hover { color: #e8eaf2; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover { opacity: 0.88; }

.stats-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-card {
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px; padding: 22px; position: relative; overflow: hidden;
}
.stat-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0;
  height: 2px; border-radius: 14px 14px 0 0;
}
.stat-blue::before  { background: #3b82f6; }
.stat-green::before { background: #22c55e; }
.stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; font-weight: 500; margin-bottom: 10px; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; color: #e8eaf2; line-height: 1; margin-bottom: 6px; }
.stat-sub   { font-size: 12px; color: #6b7280; }

.table-card {
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px; overflow: hidden;
}
.table-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 18px; border-bottom: 1px solid rgba(255,255,255,0.07);
}
.table-title { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; color: #e8eaf2; }
.search-wrap {
  display: flex; align-items: center; gap: 8px;
  background: #1a1f35; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 7px 12px; min-width: 220px;
}
.search-wrap svg { width: 14px; height: 14px; color: #6b7280; flex-shrink: 0; }
.search-input { background: none; border: none; outline: none; color: #e8eaf2; font-size: 13px; font-family: 'DM Sans', sans-serif; width: 100%; }

table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
th { padding: 11px 18px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; text-align: left; }
tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #1a1f35; }
td { padding: 13px 18px; font-size: 13px; color: #e8eaf2; }
.text-muted { color: #6b7280; }

.entity-cell { display: flex; align-items: center; gap: 10px; }
.e-avatar { width: 32px; height: 32px; border-radius: 50%; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.e-name { font-weight: 500; font-size: 13px; }
.e-sub  { font-size: 11px; color: #6b7280; }

.row-actions { display: flex; align-items: center; gap: 4px; opacity: 0; transition: opacity 0.2s; }
tbody tr:hover .row-actions { opacity: 1; }
.action-btn { width: 28px; height: 28px; border-radius: 6px; background: rgba(255,255,255,0.05); border: none; color: #6b7280; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.action-btn svg { width: 13px; height: 13px; }
.action-btn.del:hover { background: rgba(239,68,68,0.15); color: #f87171; }

.empty-state { text-align: center; padding: 48px 0; }
.empty-state p    { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 600; color: #e8eaf2; margin-bottom: 4px; }
.empty-state span { font-size: 12px; color: #6b7280; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #131726; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; width: 100%; max-width: 440px; box-shadow: 0 24px 64px rgba(0,0,0,0.5); }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.modal-title  { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #e8eaf2; }
.modal-close  { background: none; border: none; color: #6b7280; cursor: pointer; font-size: 16px; padding: 4px 8px; border-radius: 6px; }
.modal-close:hover { color: #e8eaf2; background: rgba(255,255,255,0.06); }
.modal-body   { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.07); }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1 / -1; }
.form-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; }
.form-input { background: #1a1f35; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 9px 12px; color: #e8eaf2; font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; box-sizing: border-box; }
.form-input:focus { border-color: #f5a623; }
.form-input::placeholder { color: #4b5563; }
.password-wrap { position: relative; }
.password-wrap .form-input { padding-right: 40px; }
.toggle-pw { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer; display: flex; }
.toggle-pw:hover { color: #e8eaf2; }
.toggle-pw svg { width: 15px; height: 15px; }

.confirm-box { background: #131726; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; max-width: 360px; text-align: center; box-shadow: 0 24px 64px rgba(0,0,0,0.5); }
.confirm-icon { font-size: 32px; margin-bottom: 12px; }
.confirm-box h3 { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: #e8eaf2; margin-bottom: 8px; }
.confirm-box p  { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 20px; }
.confirm-btns   { display: flex; gap: 10px; justify-content: center; }

.toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; z-index: 200; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
.toast-success { background: #166534; color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
.toast-error   { background: #7f1d1d; color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to       { opacity: 0; transform: translateY(12px); }
</style>