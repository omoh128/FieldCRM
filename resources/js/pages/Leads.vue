<template>
  <div class="dashboard">
    <Sidebar />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Leads</h1>
          <p>Track and manage incoming leads</p>
        </div>
        <div class="topbar-right">
          <button class="btn btn-ghost" @click="exportLeads">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
          </button>
          <button class="btn btn-primary" @click="openAddModal">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Lead
          </button>
        </div>
      </header>

      <div class="content">

        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card stat-blue">
            <div class="stat-label">New Leads</div>
            <div class="stat-value">{{ stats.new }}</div>
            <div class="stat-sub">Awaiting contact</div>
          </div>
          <div class="stat-card stat-amber">
            <div class="stat-label">Quoted</div>
            <div class="stat-value">{{ stats.quoted }}</div>
            <div class="stat-sub">Proposals sent</div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-label">Won</div>
            <div class="stat-value">{{ stats.won }}</div>
            <div class="stat-sub delta-up">↑ this month</div>
          </div>
          <div class="stat-card stat-red">
            <div class="stat-label">Lost</div>
            <div class="stat-value">{{ stats.lost }}</div>
            <div class="stat-sub delta-down">Monitor closely</div>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
          <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="searchQuery"
              class="search-input"
              placeholder="Search leads..."
            />
          </div>
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Statuses</option>
            <option value="new">New</option>
            <option value="quoted">Quoted</option>
            <option value="won">Won</option>
            <option value="lost">Lost</option>
          </select>
          <select v-model="priorityFilter" class="filter-select">
            <option value="">All Priorities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
          <div class="toolbar-spacer"></div>
          <span class="count-label">{{ filteredLeads.length }} lead{{ filteredLeads.length !== 1 ? 's' : '' }}</span>
        </div>

        <!-- Table -->
        <div class="table-card">
          <table>
            <thead>
              <tr>
                <th>Lead</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Source</th>
                <th>Assigned</th>
                <th>Value</th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="8">
                  <div class="empty-state"><p>Loading leads…</p></div>
                </td>
              </tr>
              <tr v-else-if="pagedLeads.length === 0">
                <td colspan="8">
                  <div class="empty-state">
                    <p>No leads found</p>
                    <span>Try adjusting your filters</span>
                  </div>
                </td>
              </tr>
              <tr
                v-for="lead in pagedLeads"
                :key="lead.id"
                @click="openDetail(lead)"
              >
                <td>
                  <div class="entity-cell">
                    <div class="e-avatar" :style="{ background: leadColor(lead) + '22', color: leadColor(lead) }">
                      {{ initials(lead) }}
                    </div>
                    <div>
                      <div class="e-name">{{ lead.firstName }} {{ lead.lastName }}</div>
                      <div class="e-sub">{{ lead.company || '—' }}</div>
                    </div>
                  </div>
                </td>
                <td><span :class="['badge', `badge-${lead.status}`]">{{ capitalize(lead.status) }}</span></td>
                <td>
                  <span :class="['priority-dot', `priority-${lead.priority}`]"></span>
                  {{ capitalize(lead.priority) }}
                </td>
                <td class="text-muted2">{{ lead.source }}</td>
                <td class="text-muted2">{{ lead.assigned }}</td>
                <td class="value-cell">${{ lead.value.toLocaleString() }}</td>
                <td class="text-muted">{{ formatDate(lead.date) }}</td>
                <td>
                  <div class="row-actions" @click.stop>
                    <button class="action-btn" title="Edit" @click="openEdit(lead)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button class="action-btn del" title="Delete" @click="askDelete(lead.id)">
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

          <!-- Pagination -->
          <div class="pagination">
            <span class="page-info">
              Showing {{ Math.min((currentPage - 1) * PAGE_SIZE + 1, filteredLeads.length) }}–{{ Math.min(currentPage * PAGE_SIZE, filteredLeads.length) }} of {{ filteredLeads.length }}
            </span>
            <div class="page-btns">
              <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">‹</button>
              <button
                v-for="p in totalPages"
                :key="p"
                :class="['page-btn', { active: p === currentPage }]"
                @click="currentPage = p"
              >{{ p }}</button>
              <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">›</button>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- ── ADD / EDIT MODAL ── -->
    <transition name="fade">
      <div v-if="showFormModal" class="modal-overlay" @click.self="closeFormModal">
        <div class="modal">
          <div class="modal-header">
            <span class="modal-title">{{ editingId ? 'Edit Lead' : 'Add Lead' }}</span>
            <button class="modal-close" @click="closeFormModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">First Name *</label>
                <input v-model="form.firstName" class="form-input" placeholder="John" />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name *</label>
                <input v-model="form.lastName" class="form-input" placeholder="Doe" />
              </div>
              <div class="form-group">
                <label class="form-label">Company</label>
                <input v-model="form.company" class="form-input" placeholder="Acme Inc." />
              </div>
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input v-model="form.phone" class="form-input" placeholder="+1 555 000 0000" />
              </div>
              <div class="form-group full">
                <label class="form-label">Email</label>
                <input v-model="form.email" class="form-input" placeholder="john@example.com" />
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="new">New</option>
                  <option value="quoted">Quoted</option>
                  <option value="won">Won</option>
                  <option value="lost">Lost</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Priority</label>
                <select v-model="form.priority" class="form-select">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Source</label>
                <select v-model="form.source" class="form-select">
                  <option>Referral</option>
                  <option>Website</option>
                  <option>Cold Call</option>
                  <option>Social Media</option>
                  <option>Walk-in</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Assigned To</label>
                <select v-model="form.assigned" class="form-select">
                  <option>Jake R.</option>
                  <option>Lena K.</option>
                  <option>Mike D.</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Est. Value ($)</label>
                <input v-model.number="form.value" type="number" class="form-input" placeholder="0" />
              </div>
              <div class="form-group full">
                <label class="form-label">Notes</label>
                <textarea v-model="form.notes" class="form-textarea" placeholder="Additional details..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="closeFormModal">Cancel</button>
            <button class="btn btn-primary" @click="saveLead" :disabled="isSaving">
              {{ isSaving ? 'Saving…' : 'Save Lead' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── DETAIL MODAL ── -->
    <transition name="fade">
      <div v-if="showDetailModal && selectedLead" class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal">
          <div class="modal-header">
            <span class="modal-title">Lead Details</span>
            <button class="modal-close" @click="showDetailModal = false">✕</button>
          </div>
          <div class="modal-body">
            <div class="detail-section">
              <div class="detail-hero">
                <div class="e-avatar detail-avatar" :style="{ background: leadColor(selectedLead) + '22', color: leadColor(selectedLead) }">
                  {{ initials(selectedLead) }}
                </div>
                <div>
                  <div class="detail-name">{{ selectedLead.firstName }} {{ selectedLead.lastName }}</div>
                  <div class="detail-company">{{ selectedLead.company || 'No company' }}</div>
                </div>
                <span :class="['badge', `badge-${selectedLead.status}`]" style="margin-left:auto">
                  {{ capitalize(selectedLead.status) }}
                </span>
              </div>
              <div class="detail-grid">
                <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">{{ selectedLead.email || '—' }}</div></div>
                <div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">{{ selectedLead.phone || '—' }}</div></div>
                <div class="detail-item"><div class="detail-label">Source</div><div class="detail-value">{{ selectedLead.source }}</div></div>
                <div class="detail-item"><div class="detail-label">Assigned</div><div class="detail-value">{{ selectedLead.assigned }}</div></div>
                <div class="detail-item">
                  <div class="detail-label">Priority</div>
                  <div class="detail-value">
                    <span :class="['priority-dot', `priority-${selectedLead.priority}`]"></span>
                    {{ capitalize(selectedLead.priority) }}
                  </div>
                </div>
                <div class="detail-item"><div class="detail-label">Est. Value</div><div class="detail-value value-cell">${{ selectedLead.value.toLocaleString() }}</div></div>
                <div class="detail-item"><div class="detail-label">Date Added</div><div class="detail-value">{{ formatDate(selectedLead.date) }}</div></div>
              </div>
            </div>
            <div v-if="selectedLead.notes" class="detail-section">
              <h4>Notes</h4>
              <p class="detail-notes">{{ selectedLead.notes }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="showDetailModal = false">Close</button>
            <button
              v-if="selectedLead && selectedLead.status !== 'customer'"
              class="btn btn-convert"
              :disabled="isConverting"
              @click="convertToCustomer(selectedLead)"
            >
              {{ isConverting ? 'Converting…' : '→ Convert to Customer' }}
            </button>
            <span v-else-if="selectedLead && selectedLead.status === 'customer'" class="badge badge-customer" style="margin-right:auto">
              ✓ Customer
            </span>
            <button class="btn btn-primary" @click="openEditFromDetail">Edit Lead</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── CONFIRM DELETE ── -->
    <transition name="fade">
      <div v-if="showConfirmModal" class="confirm-overlay" @click.self="showConfirmModal = false">
        <div class="confirm-box">
          <div class="confirm-icon">🗑️</div>
          <h3>Delete Lead?</h3>
          <p>This action cannot be undone. The lead and all associated data will be permanently removed.</p>
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
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/api'
import Sidebar from '@/components/Sidebar.vue'

// ── CONSTANTS ───────────────────────────────────────────────────────────────
const PAGE_SIZE = 7
const COLORS = ['#f97316','#8b5cf6','#06b6d4','#22c55e','#ef4444','#f59e0b','#3b82f6','#ec4899','#14b8a6','#a855f7']

// ── LEADS DATA ───────────────────────────────────────────────────────────────
const leads      = ref([])
const isLoading  = ref(false)
const isSaving   = ref(false)

/**
 * The API returns { id, first_name, last_name, email, phone, company,
 * status, priority, source, assigned_to, value, notes, created_at }.
 * Normalise into the shape the template expects.
 */
const normalise = (l) => ({
  ...l,
  firstName: l.first_name || '',
  lastName:  l.last_name  || '',
  assigned:  l.assigned_to || '',
  date:      (l.created_at || '').split('T')[0],
  value:     parseFloat(l.value) || 0,
  status:    (l.status   || 'New').toLowerCase(),
  priority:  (l.priority || 'Medium').toLowerCase(),
})

const fetchLeads = async () => {
  isLoading.value = true
  try {
    const { data } = await api.get('/leads', { params: { per_page: 100 } })
    // Laravel paginator returns { data: [...] }; a plain array also works
    leads.value = (data.data ?? data).map(normalise)
  } catch (err) {
    showToast('Failed to load leads', 'error')
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchLeads)

// ── FILTERS ──────────────────────────────────────────────────────────────────
const searchQuery    = ref('')
const statusFilter   = ref('')
const priorityFilter = ref('')
const currentPage    = ref(1)

watch([searchQuery, statusFilter, priorityFilter], () => { currentPage.value = 1 })

const filteredLeads = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return leads.value.filter(l => {
    const matchQ = !q || `${l.firstName} ${l.lastName} ${l.company} ${l.email}`.toLowerCase().includes(q)
    const matchS = !statusFilter.value   || l.status   === statusFilter.value
    const matchP = !priorityFilter.value || l.priority === priorityFilter.value
    return matchQ && matchS && matchP
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredLeads.value.length / PAGE_SIZE)))

const pagedLeads = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return filteredLeads.value.slice(start, start + PAGE_SIZE)
})

// ── STATS ────────────────────────────────────────────────────────────────────
const stats = computed(() => ({
  new:    leads.value.filter(l => l.status === 'new').length,
  quoted: leads.value.filter(l => l.status === 'quoted').length,
  won:    leads.value.filter(l => l.status === 'won').length,
  lost:   leads.value.filter(l => l.status === 'lost').length,
}))

// ── HELPERS ──────────────────────────────────────────────────────────────────
const initials   = (l) => (l.firstName?.[0] || '') + (l.lastName?.[0] || '')
const leadColor  = (l) => COLORS[(l.id - 1) % COLORS.length]
const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const formatDate = (d) => d
  ? new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  : '—'

// ── FORM MODAL ────────────────────────────────────────────────────────────────
const showFormModal = ref(false)
const editingId     = ref(null)

const defaultForm = () => ({
  firstName: '', lastName: '', company: '', phone: '',
  email: '', status: 'new', priority: 'medium',
  source: 'Referral', assigned: 'Jake R.', value: 0, notes: '',
})
const form = ref(defaultForm())

const openAddModal = () => {
  editingId.value = null
  form.value = defaultForm()
  showFormModal.value = true
}

const openEdit = (lead) => {
  editingId.value = lead.id
  form.value = { ...lead }
  showFormModal.value = true
  showDetailModal.value = false
}

const openEditFromDetail = () => openEdit(selectedLead.value)

const closeFormModal = () => {
  showFormModal.value = false
  editingId.value = null
}

const saveLead = async () => {
  if (!form.value.firstName.trim() || !form.value.lastName.trim()) {
    showToast('First and last name are required', 'error')
    return
  }

  // Build the payload matching the actual DB columns
  const payload = {
    first_name:  form.value.firstName.trim(),
    last_name:   form.value.lastName.trim(),
    email:       form.value.email,
    phone:       form.value.phone,
    company:     form.value.company,
    status:      capitalize(form.value.status),
    priority:    capitalize(form.value.priority),
    source:      form.value.source,
    assigned_to: form.value.assigned,
    value:       form.value.value,
    notes:       form.value.notes,
  }

  isSaving.value = true
  try {
    if (editingId.value) {
      // ── UPDATE ──────────────────────────────────────────────────────────
      const { data } = await api.put(`/leads/${editingId.value}`, payload)
      const idx = leads.value.findIndex(l => l.id === editingId.value)
      if (idx !== -1) leads.value[idx] = normalise(data)
      showToast('Lead updated')
    } else {
      // ── CREATE ──────────────────────────────────────────────────────────
      const { data } = await api.post('/leads', payload)
      leads.value.unshift(normalise(data))
      showToast('Lead added')
    }
    closeFormModal()
  } catch (err) {
    const msg = err.response?.data?.message || 'Could not save lead'
    showToast(msg, 'error')
    console.error(err)
  } finally {
    isSaving.value = false
  }
}

// ── CONVERT TO CUSTOMER ───────────────────────────────────────────────────────
const isConverting = ref(false)

const convertToCustomer = async (lead) => {
  isConverting.value = true
  try {
    const { data } = await api.post(`/leads/${lead.id}/convert`)
    // Update the lead in the local array
    const idx = leads.value.findIndex(l => l.id === lead.id)
    if (idx !== -1) leads.value[idx] = normalise(data.lead)
    selectedLead.value = normalise(data.lead)
    showToast(`${lead.firstName} ${lead.lastName} converted to customer!`)
  } catch (err) {
    const msg = err.response?.data?.message || 'Could not convert lead'
    showToast(msg, 'error')
  } finally {
    isConverting.value = false
  }
}
const showDetailModal = ref(false)
const selectedLead    = ref(null)

const openDetail = (lead) => {
  selectedLead.value = lead
  showDetailModal.value = true
}

// ── DELETE ────────────────────────────────────────────────────────────────────
const showConfirmModal = ref(false)
const deletingId       = ref(null)

const askDelete = (id) => {
  deletingId.value = id
  showConfirmModal.value = true
}

const confirmDelete = async () => {
  try {
    await api.delete(`/leads/${deletingId.value}`)
    leads.value = leads.value.filter(l => l.id !== deletingId.value)
    showConfirmModal.value = false
    showToast('Lead deleted')
  } catch (err) {
    showToast('Could not delete lead', 'error')
    console.error(err)
  }
}

// ── EXPORT CSV ────────────────────────────────────────────────────────────────
const exportLeads = () => {
  const rows = filteredLeads.value.map(l => ({
    Name: `${l.firstName} ${l.lastName}`,
    Company: l.company, Email: l.email, Phone: l.phone,
    Status: l.status, Priority: l.priority, Source: l.source,
    Assigned: l.assigned, Value: l.value, Date: l.date, Notes: l.notes,
  }))
  const headers = Object.keys(rows[0])
  const csv = [headers.join(','), ...rows.map(r => headers.map(h => `"${r[h] ?? ''}"`).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = 'leads.csv'
  a.click()
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
/* ── LAYOUT ── */
.dashboard { display: flex; min-height: 100vh; background: #0c0f1a; }
.main { margin-left: 220px; flex: 1; display: flex; flex-direction: column; }
.content { padding: 28px 32px; flex: 1; }

/* ── TOPBAR ── */
.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 32px;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  background: rgba(19,23,38,0.8);
  backdrop-filter: blur(10px);
  position: sticky; top: 0; z-index: 5;
  gap: 16px;
}
.topbar-left h1 { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: #e8eaf2; }
.topbar-left p  { font-size: 12px; color: #6b7280; margin-top: 2px; }
.topbar-right   { display: flex; align-items: center; gap: 10px; }

/* ── BUTTONS ── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif;
  cursor: pointer; border: none; transition: all 0.2s;
}
.btn-primary { background: #f5a623; color: #000; }
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }
.btn-ghost {
  background: transparent; color: #6b7280;
  border: 1px solid rgba(255,255,255,0.1);
}
.btn-ghost:hover { color: #e8eaf2; border-color: rgba(255,255,255,0.2); }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover { opacity: 0.88; }
.btn-convert { background: #8b5cf6; color: #fff; }
.btn-convert:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-convert:disabled { opacity: 0.5; cursor: not-allowed; }
.badge-customer { background: rgba(139,92,246,0.15); color: #a78bfa; }

/* ── STATS ── */
.stats-row {
  display: grid; grid-template-columns: repeat(4,1fr);
  gap: 16px; margin-bottom: 24px;
}
.stat-card {
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px; padding: 22px;
  position: relative; overflow: hidden;
  transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0;
  height: 2px; border-radius: 14px 14px 0 0;
}
.stat-blue::before  { background: #3b82f6; }
.stat-amber::before { background: #f5a623; }
.stat-green::before { background: #22c55e; }
.stat-red::before   { background: #ef4444; }

.stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; font-weight: 500; margin-bottom: 10px; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; color: #e8eaf2; line-height: 1; margin-bottom: 6px; }
.stat-sub   { font-size: 12px; color: #6b7280; }
.delta-up   { color: #22c55e; }
.delta-down { color: #ef4444; }

/* ── TOOLBAR ── */
.toolbar {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 16px; flex-wrap: wrap;
}
.search-wrap {
  display: flex; align-items: center; gap: 8px;
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 8px 12px; flex: 1; min-width: 180px;
}
.search-wrap svg { width: 14px; height: 14px; color: #6b7280; flex-shrink: 0; }
.search-input { background: none; border: none; outline: none; color: #e8eaf2; font-size: 13px; font-family: 'DM Sans', sans-serif; width: 100%; }
.search-input::placeholder { color: #4b5563; }

.filter-select {
  background: #131726; color: #e8eaf2;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 8px 12px;
  font-size: 13px; font-family: 'DM Sans', sans-serif;
  cursor: pointer; outline: none; appearance: none;
}
.filter-select:focus { border-color: #f5a623; }
.toolbar-spacer { flex: 1; }
.count-label { font-size: 12px; color: #6b7280; }

/* ── TABLE ── */
.table-card {
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px; overflow: hidden;
}
table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
th {
  padding: 11px 18px; font-size: 11px;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: #6b7280; font-weight: 500; text-align: left;
}
tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; cursor: pointer; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #1a1f35; }
td { padding: 13px 18px; font-size: 13px; color: #e8eaf2; }

.entity-cell { display: flex; align-items: center; gap: 10px; }
.e-avatar { width: 30px; height: 30px; border-radius: 50%; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.e-name { font-weight: 500; font-size: 13px; }
.e-sub  { font-size: 11px; color: #6b7280; }
.text-muted  { color: #6b7280; }
.text-muted2 { color: #9ca3af; }
.value-cell  { font-family: 'Syne', sans-serif; font-weight: 600; }

/* ── BADGES ── */
.badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 500; }
.badge-new    { background: rgba(59,130,246,0.15);  color: #60a5fa; }
.badge-quoted { background: rgba(245,166,35,0.12);  color: #f5a623; }
.badge-won    { background: rgba(34,197,94,0.12);   color: #4ade80; }
.badge-lost   { background: rgba(239,68,68,0.12);   color: #f87171; }

/* ── PRIORITY ── */
.priority-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
.priority-high   { background: #ef4444; }
.priority-medium { background: #f5a623; }
.priority-low    { background: #22c55e; }

/* ── ROW ACTIONS ── */
.row-actions { display: flex; align-items: center; gap: 4px; opacity: 0; transition: opacity 0.2s; }
tbody tr:hover .row-actions { opacity: 1; }
.action-btn {
  width: 28px; height: 28px; border-radius: 6px;
  background: rgba(255,255,255,0.05); border: none;
  color: #6b7280; cursor: pointer; display: flex;
  align-items: center; justify-content: center; transition: all 0.2s;
}
.action-btn svg { width: 13px; height: 13px; }
.action-btn:hover { background: rgba(255,255,255,0.1); color: #e8eaf2; }
.action-btn.del:hover { background: rgba(239,68,68,0.15); color: #f87171; }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 48px 0; }
.empty-state p    { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 600; color: #e8eaf2; margin-bottom: 4px; }
.empty-state span { font-size: 12px; color: #6b7280; }

/* ── PAGINATION ── */
.pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.07); }
.page-info  { font-size: 12px; color: #6b7280; }
.page-btns  { display: flex; gap: 4px; }
.page-btn {
  min-width: 30px; height: 30px; border-radius: 6px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.07);
  color: #6b7280; font-size: 12px; cursor: pointer; transition: all 0.15s;
  display: flex; align-items: center; justify-content: center;
}
.page-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); color: #e8eaf2; }
.page-btn.active { background: rgba(245,166,35,0.15); color: #f5a623; border-color: rgba(245,166,35,0.3); }
.page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

/* ── MODAL ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px); display: flex;
  align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: #131726; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; width: 100%; max-width: 540px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 24px 64px rgba(0,0,0,0.5);
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.07);
  position: sticky; top: 0; background: #131726; z-index: 1;
}
.modal-title { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #e8eaf2; }
.modal-close { background: none; border: none; color: #6b7280; cursor: pointer; font-size: 16px; padding: 4px 8px; border-radius: 6px; transition: all 0.2s; }
.modal-close:hover { color: #e8eaf2; background: rgba(255,255,255,0.06); }
.modal-body   { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.07); }

/* ── FORM ── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1 / -1; }
.form-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; }
.form-input, .form-select, .form-textarea {
  background: #1a1f35; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px; padding: 9px 12px; color: #e8eaf2;
  font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none;
  transition: border-color 0.2s; appearance: none;
}
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #f5a623; }
.form-input::placeholder, .form-textarea::placeholder { color: #4b5563; }
.form-textarea { resize: vertical; min-height: 80px; }

/* ── DETAIL ── */
.detail-section { margin-bottom: 20px; }
.detail-section:last-child { margin-bottom: 0; }
.detail-section h4 { font-family: 'Syne', sans-serif; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 10px; }
.detail-hero { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.detail-avatar { width: 48px !important; height: 48px !important; font-size: 16px !important; }
.detail-name    { font-family: 'Syne', sans-serif; font-size: 17px; font-weight: 700; color: #e8eaf2; }
.detail-company { color: #6b7280; font-size: 13px; }
.detail-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.detail-item  { display: flex; flex-direction: column; gap: 3px; }
.detail-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; }
.detail-value { font-size: 13px; color: #e8eaf2; display: flex; align-items: center; }
.detail-notes { font-size: 13px; color: #9ca3af; line-height: 1.6; }

/* ── CONFIRM ── */
.confirm-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  backdrop-filter: blur(4px); display: flex;
  align-items: center; justify-content: center; z-index: 110;
}
.confirm-box {
  background: #131726; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px; padding: 32px; max-width: 360px;
  text-align: center; box-shadow: 0 24px 64px rgba(0,0,0,0.5);
}
.confirm-icon { font-size: 32px; margin-bottom: 12px; }
.confirm-box h3  { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: #e8eaf2; margin-bottom: 8px; }
.confirm-box p   { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 20px; }
.confirm-btns    { display: flex; gap: 10px; justify-content: center; }

/* ── TOAST ── */
.toast {
  position: fixed; bottom: 24px; right: 24px;
  padding: 12px 18px; border-radius: 10px;
  font-size: 13px; font-weight: 500; display: flex;
  align-items: center; gap: 8px; z-index: 200;
  box-shadow: 0 8px 32px rgba(0,0,0,0.4);
}
.toast-success { background: #166534; color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
.toast-error   { background: #7f1d1d; color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

/* ── TRANSITIONS ── */
.fade-enter-active, .fade-leave-active   { transition: opacity 0.2s ease; }
.fade-enter-from,   .fade-leave-to       { opacity: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to       { opacity: 0; transform: translateY(12px); }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) { .stats-row { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 720px) {
  .main { margin-left: 0; }
  .content { padding: 20px 16px; }
  .stats-row { grid-template-columns: 1fr 1fr; }
  .form-grid { grid-template-columns: 1fr; }
  .detail-grid { grid-template-columns: 1fr; }
}
</style>