<template>
  <div class="dashboard">
    <Sidebar />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Jobs</h1>
          <p>Schedule, track, and manage all field jobs</p>
        </div>
        <div class="topbar-right">
          <!-- View Tabs -->
          <div class="view-tabs">
            <button
              :class="['view-tab', { active: currentView === 'list' }]"
              @click="currentView = 'list'"
            >List</button>
            <button
              :class="['view-tab', { active: currentView === 'kanban' }]"
              @click="currentView = 'kanban'"
            >Board</button>
          </div>
          <button class="btn btn-ghost" @click="exportJobs">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
          </button>
          <button class="btn btn-primary" @click="openAddModal">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Job
          </button>
        </div>
      </header>

      <div class="content">

        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card stat-blue">
            <div class="stat-label">Scheduled</div>
            <div class="stat-value">{{ stats.scheduled }}</div>
            <div class="stat-sub">Upcoming jobs</div>
          </div>
          <div class="stat-card stat-amber">
            <div class="stat-label">In Progress</div>
            <div class="stat-value">{{ stats.inProgress }}</div>
            <div class="stat-sub">Active now</div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ stats.completed }}</div>
            <div class="stat-sub delta-up">This month</div>
          </div>
          <div class="stat-card stat-purple">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">${{ stats.revenue.toLocaleString() }}</div>
            <div class="stat-sub">Won jobs</div>
          </div>
        </div>

        <!-- ── LIST VIEW ── -->
        <template v-if="currentView === 'list'">
          <!-- Toolbar -->
          <div class="toolbar">
            <div class="search-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
              </svg>
              <input v-model="searchQuery" class="search-input" placeholder="Search jobs..." />
            </div>
            <select v-model="statusFilter" class="filter-select">
              <option value="">All Statuses</option>
              <option value="Scheduled">Scheduled</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <select v-model="typeFilter" class="filter-select">
              <option value="">All Types</option>
              <option value="Plumbing">Plumbing</option>
              <option value="Electrical">Electrical</option>
              <option value="Roofing">Roofing</option>
              <option value="Painting">Painting</option>
              <option value="Renovation">Renovation</option>
              <option value="Other">Other</option>
            </select>
            <div class="toolbar-spacer"></div>
            <span class="count-label">{{ filteredJobs.length }} job{{ filteredJobs.length !== 1 ? 's' : '' }}</span>
          </div>

          <!-- Table -->
          <div class="table-card">
            <table>
              <thead>
                <tr>
                  <th>Job</th>
                  <th>Customer</th>
                  <th>Status</th>
                  <th>Assigned</th>
                  <th>Date</th>
                  <th>Progress</th>
                  <th>Value</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="pagedJobs.length === 0">
                  <td colspan="8" v-if="isLoading">
                    <div class="empty-state">
                      <p>Loading jobs…</p>
                    </div>
                  </td>
                </tr>
                <tr
                  v-for="job in pagedJobs"
                  :key="job.id"
                  @click="openDetail(job)"
                >
                  <td>
                    <div class="entity-cell">
                      <div class="e-avatar" :style="{ background: jobColor(job) + '22', color: jobColor(job) }">
                        {{ titleInitials(job.title) }}
                      </div>
                      <div>
                        <div class="e-name">{{ job.title }}</div>
                        <div class="e-sub">{{ job.type }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-muted2">{{ job.customer }}</td>
                  <td>
                    <span :class="['badge', `badge-${job.status}`]">{{ statusLabel(job.status) }}</span>
                  </td>
                  <td class="text-muted2">{{ job.assigned }}</td>
                  <td class="text-muted">{{ formatDate(job.startDate) }}</td>
                  <td>
                    <div class="progress-cell">
                      <span class="progress-pct">{{ job.progress }}%</span>
                      <div class="progress-wrap">
                        <div class="progress-fill" :style="{ width: job.progress + '%' }"></div>
                      </div>
                    </div>
                  </td>
                  <td class="value-cell">${{ job.value.toLocaleString() }}</td>
                  <td>
                    <div class="row-actions" @click.stop>
                      <button class="action-btn" title="Edit" @click="openEdit(job)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                      </button>
                      <button class="action-btn del" title="Delete" @click="askDelete(job.id)">
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
                Showing {{ Math.min((currentPage - 1) * PAGE_SIZE + 1, filteredJobs.length) }}–{{ Math.min(currentPage * PAGE_SIZE, filteredJobs.length) }} of {{ filteredJobs.length }}
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
        </template>

        <!-- ── KANBAN VIEW ── -->
        <div v-else class="kanban">
          <div
            v-for="col in kanbanCols"
            :key="col.status"
            class="kanban-col"
          >
            <div class="kanban-header">
              <div class="kanban-col-title">
                <span class="k-dot" :style="{ background: col.dot }"></span>
                {{ col.label }}
              </div>
              <span class="kanban-count">{{ jobsByStatus(col.status).length }}</span>
            </div>
            <div class="kanban-cards">
              <div
                v-for="job in jobsByStatus(col.status)"
                :key="job.id"
                class="k-card"
                @click="openDetail(job)"
              >
                <div class="k-card-title">{{ job.title }}</div>
                <div class="k-card-sub">{{ job.customer }} · {{ job.type }}</div>
                <div v-if="job.progress > 0" class="k-progress">
                  <div class="progress-wrap k-progress-bar">
                    <div class="progress-fill" :style="{ width: job.progress + '%' }"></div>
                  </div>
                </div>
                <div class="k-card-footer">
                  <div class="k-assignee">
                    <div class="k-mini-avatar" :style="{ background: jobColor(job) + '22', color: jobColor(job) }">
                      {{ assigneeInitials(job.assigned) }}
                    </div>
                    {{ job.assigned }}
                  </div>
                  <span class="k-value">${{ job.value.toLocaleString() }}</span>
                </div>
              </div>
              <div v-if="jobsByStatus(col.status).length === 0" class="kanban-empty">
                No jobs
              </div>
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
            <span class="modal-title">{{ editingId ? 'Edit Job' : 'Add Job' }}</span>
            <button class="modal-close" @click="closeFormModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group full">
                <label class="form-label">Job Title *</label>
                <input v-model="form.title" class="form-input" placeholder="Kitchen Renovation" />
              </div>
              <div class="form-group">
                <label class="form-label">Customer *</label>
                <select v-model="form.lead_id" class="form-select">
                  <option value="">Select a customer…</option>
                  <option v-for="c in customers" :key="c.id" :value="c.id">
                    {{ c.first_name }} {{ c.last_name }}{{ c.company ? ` (${c.company})` : '' }}
                  </option>
                </select>
              </div>
              </div>
              <div class="form-group">
                <label class="form-label">Type</label>
                <select v-model="form.type" class="form-select">
                  <option>Plumbing</option>
                  <option>Electrical</option>
                  <option>Roofing</option>
                  <option>Painting</option>
                  <option>Renovation</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="Scheduled">Scheduled</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
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
                <label class="form-label">Start Date</label>
                <input v-model="form.startDate" type="date" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">End Date</label>
                <input v-model="form.endDate" type="date" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Value ($)</label>
                <input v-model.number="form.value" type="number" class="form-input" placeholder="0" />
              </div>
              <div class="form-group">
                <label class="form-label">Progress (%)</label>
                <input v-model.number="form.progress" type="number" min="0" max="100" class="form-input" placeholder="0" />
              </div>
              <div class="form-group full">
                <label class="form-label">Address</label>
                <input v-model="form.address" class="form-input" placeholder="Job site address" />
              </div>
              <div class="form-group full">
                <label class="form-label">Notes</label>
                <textarea v-model="form.notes" class="form-textarea" placeholder="Job details..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="closeFormModal">Cancel</button>
            <button class="btn btn-primary" @click="saveJob" :disabled="isSaving">
              {{ isSaving ? 'Saving…' : 'Save Job' }}
            </button>
          </div>
        </div>
      
    </transition>

    <!-- ── DETAIL MODAL ── -->
    <transition name="fade">
      <div v-if="showDetailModal && selectedJob" class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal">
          <div class="modal-header">
            <span class="modal-title">Job Details</span>
            <button class="modal-close" @click="showDetailModal = false">✕</button>
          </div>
          <div class="modal-body">
            <div class="detail-section">
              <div class="detail-hero">
                <div class="e-avatar detail-avatar" :style="{ background: jobColor(selectedJob) + '22', color: jobColor(selectedJob) }">
                  {{ titleInitials(selectedJob.title) }}
                </div>
                <div>
                  <div class="detail-name">{{ selectedJob.title }}</div>
                  <div class="detail-company">{{ selectedJob.type }} · {{ selectedJob.customer }}</div>
                </div>
                <span :class="['badge', `badge-${selectedJob.status}`]" style="margin-left:auto">
                  {{ statusLabel(selectedJob.status) }}
                </span>
              </div>

              <!-- Progress bar in detail -->
              <div v-if="selectedJob.progress > 0" class="detail-progress">
                <div class="detail-progress-label">
                  <span>Progress</span>
                  <span>{{ selectedJob.progress }}%</span>
                </div>
                <div class="progress-wrap detail-progress-bar">
                  <div class="progress-fill" :style="{ width: selectedJob.progress + '%' }"></div>
                </div>
              </div>

              <div class="detail-grid">
                <div class="detail-item"><div class="detail-label">Assigned</div><div class="detail-value">{{ selectedJob.assigned }}</div></div>
                <div class="detail-item"><div class="detail-label">Value</div><div class="detail-value value-cell">${{ selectedJob.value.toLocaleString() }}</div></div>
                <div class="detail-item"><div class="detail-label">Start Date</div><div class="detail-value">{{ formatDate(selectedJob.startDate) }}</div></div>
                <div class="detail-item"><div class="detail-label">End Date</div><div class="detail-value">{{ formatDate(selectedJob.endDate) }}</div></div>
                <div class="detail-item detail-full"><div class="detail-label">Address</div><div class="detail-value">{{ selectedJob.address || '—' }}</div></div>
              </div>
            </div>
            <div v-if="selectedJob.notes" class="detail-section">
              <h4>Notes</h4>
              <p class="detail-notes">{{ selectedJob.notes }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="showDetailModal = false">Close</button>
            <button class="btn btn-primary" @click="openEditFromDetail">Edit Job</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── CONFIRM DELETE ── -->
    <transition name="fade">
      <div v-if="showConfirmModal" class="confirm-overlay" @click.self="showConfirmModal = false">
        <div class="confirm-box">
          <div class="confirm-icon">🗑️</div>
          <h3>Delete Job?</h3>
          <p>This job and all its data will be permanently removed.</p>
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

const PAGE_SIZE = 7
const COLORS = ['#f97316','#8b5cf6','#06b6d4','#22c55e','#ef4444','#f59e0b','#3b82f6','#ec4899']

const kanbanCols = [
  { status: 'Scheduled',   label: 'Scheduled',   dot: '#3b82f6' },
  { status: 'In Progress', label: 'In Progress',  dot: '#f5a623' },
  { status: 'Completed',   label: 'Completed',    dot: '#22c55e' },
  { status: 'Cancelled',   label: 'Cancelled',    dot: '#ef4444' },
]

// ── NORMALISE ─────────────────────────────────────────────────────────────────
const normalise = (j) => ({
  ...j,
  customer:  j.lead ? `${j.lead.first_name} ${j.lead.last_name}`.trim() : (j.customer || ''),
  assigned:  j.assigned_to || j.assigned || '',
  startDate: j.scheduled_date || j.startDate || '',
  endDate:   j.due_date || j.endDate || '',
  value:     parseFloat(j.value) || 0,
  progress:  parseInt(j.progress) || 0,
  status:    j.status || 'Scheduled',
})

// ── DATA ──────────────────────────────────────────────────────────────────────
const jobs      = ref([])
const customers = ref([])
const isLoading = ref(false)
const isSaving  = ref(false)

const fetchJobs = async () => {
  isLoading.value = true
  try {
    const { data } = await api.get('/jobs', { params: { per_page: 100 } })
    jobs.value = (data.data ?? data).map(normalise)
  } catch (err) { console.error(err); jobs.value = [] }
  finally { isLoading.value = false }
}

const fetchCustomers = async () => {
  try {
    const { data } = await api.get('/customers', { params: { per_page: 100 } })
    customers.value = data.data ?? data
  } catch (err) { console.error(err) }
}

onMounted(() => { fetchJobs(); fetchCustomers() })

// ── VIEW / FILTERS ────────────────────────────────────────────────────────────
const currentView  = ref('list')
const searchQuery  = ref('')
const statusFilter = ref('')
const typeFilter   = ref('')
const currentPage  = ref(1)

watch([searchQuery, statusFilter, typeFilter], () => { currentPage.value = 1 })

const filteredJobs = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return jobs.value.filter(j => {
    const matchQ = !q || `${j.title} ${j.customer}`.toLowerCase().includes(q)
    const matchS = !statusFilter.value || j.status === statusFilter.value
    const matchT = !typeFilter.value   || j.type   === typeFilter.value
    return matchQ && matchS && matchT
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredJobs.value.length / PAGE_SIZE)))
const pagedJobs  = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return filteredJobs.value.slice(start, start + PAGE_SIZE)
})

const jobsByStatus = (status) => jobs.value.filter(j => j.status === status)

// ── STATS ─────────────────────────────────────────────────────────────────────
const stats = computed(() => ({
  scheduled:  jobs.value.filter(j => j.status === 'Scheduled').length,
  inProgress: jobs.value.filter(j => j.status === 'In Progress').length,
  completed:  jobs.value.filter(j => j.status === 'Completed').length,
  revenue:    jobs.value.filter(j => j.status === 'Completed').reduce((a, j) => a + j.value, 0),
}))

// ── HELPERS ───────────────────────────────────────────────────────────────────
const jobColor         = (j) => COLORS[(j.id - 1) % COLORS.length]
const titleInitials    = (t) => (t || '').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
const assigneeInitials = (n) => (n || '').split(' ').map(w => w[0]).join('')
const capitalize       = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const statusLabel      = (s) => s === 'in-progress' ? 'In Progress' : capitalize(s)
const formatDate       = (d) => d
  ? new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  : '—'

// ── FORM MODAL ────────────────────────────────────────────────────────────────
const showFormModal = ref(false)
const editingId     = ref(null)

const defaultForm = () => ({
  lead_id: '', title: '', type: 'Plumbing', status: 'Scheduled',
  assigned_to: '', startDate: new Date().toISOString().split('T')[0],
  endDate: '', value: 0, progress: 0, address: '', notes: '',
})
const form = ref(defaultForm())

const openAddModal = () => { editingId.value = null; form.value = defaultForm(); showFormModal.value = true }

const openEdit = (job) => {
  editingId.value = job.id
  form.value = { lead_id: job.lead_id || '', title: job.title, type: job.type,
    status: job.status, assigned_to: job.assigned, startDate: job.startDate,
    endDate: job.endDate, value: job.value, progress: job.progress,
    address: job.address || '', notes: job.notes || '' }
  showFormModal.value = true
  showDetailModal.value = false
}

const openEditFromDetail = () => openEdit(selectedJob.value)
const closeFormModal = () => { showFormModal.value = false; editingId.value = null }

const saveJob = async () => {
  if (!form.value.title.trim()) { showToast('Job title is required', 'error'); return }
  if (!form.value.lead_id)      { showToast('Please select a customer', 'error'); return }

  const statusMap = { 'scheduled': 'Scheduled', 'in-progress': 'In Progress', 'completed': 'Completed', 'cancelled': 'Cancelled' }
  const payload = {
    lead_id: form.value.lead_id, title: form.value.title.trim(),
    type: form.value.type, status: form.value.status || 'Scheduled',
    assigned_to: form.value.assigned_to,
    scheduled_date: form.value.startDate || null, due_date: form.value.endDate || null,
    value: Number(form.value.value) || 0, progress: Number(form.value.progress) || 0,
    address: form.value.address, notes: form.value.notes,
  }

  isSaving.value = true
  try {
    if (editingId.value) {
      const { data } = await api.put(`/jobs/${editingId.value}`, payload)
      const idx = jobs.value.findIndex(j => j.id === editingId.value)
      if (idx !== -1) jobs.value[idx] = normalise(data)
      showToast('Job updated')
    } else {
      const { data } = await api.post('/jobs', payload)
      jobs.value.unshift(normalise(data))
      showToast('Job added')
    }
    closeFormModal()
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not save job', 'error')
    console.error(err)
  } finally { isSaving.value = false }
}

// ── DETAIL / DELETE ───────────────────────────────────────────────────────────
const showDetailModal  = ref(false)
const selectedJob      = ref(null)
const showConfirmModal = ref(false)
const deletingId       = ref(null)

const openDetail = (job) => { selectedJob.value = job; showDetailModal.value = true }
const askDelete  = (id)  => { deletingId.value = id;   showConfirmModal.value = true }

const confirmDelete = async () => {
  try {
    await api.delete(`/jobs/${deletingId.value}`)
    jobs.value = jobs.value.filter(j => j.id !== deletingId.value)
    showConfirmModal.value = false
    showToast('Job deleted')
  } catch (err) { showToast('Could not delete job', 'error') }
}

// ── EXPORT ────────────────────────────────────────────────────────────────────
const exportJobs = () => {
  const rows = jobs.value.map(j => ({ Title: j.title, Customer: j.customer,
    Type: j.type, Status: j.status, Assigned: j.assigned, Value: j.value,
    Start: j.startDate, End: j.endDate, Progress: j.progress + '%' }))
  if (!rows.length) return showToast('No data', 'error')
  const headers = Object.keys(rows[0])
  const csv = [headers.join(','), ...rows.map(r => headers.map(h => `"${r[h] ?? ''}"`).join(','))].join('\n')
  const a = document.createElement('a')
  a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  a.download = 'jobs.csv'; a.click()
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
