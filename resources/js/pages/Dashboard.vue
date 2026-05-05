<template>
  <div class="dashboard">
    <Sidebar />

    <main class="main">
      <!-- Topbar -->
      <Topbar
        title="Dashboard Overview"
        @filter-change="handleFilterChange"
        @new-lead="openNewLeadModal"
      />

      <div class="content">

        <!-- KPI Cards -->
        <KpiCards :cards="kpiCards" />

        <!-- Chart + Pipeline -->
        <div class="bottom-grid">
          <LeadsChart :chart-data="chartData" />
          <Pipeline :stages="pipelineStages" />
        </div>

        <!-- Leads Table -->
        <LeadsTable
          :leads="filteredLeads"
          @row-click="handleRowClick"
        />

      </div>
    </main>

    <!-- New Lead Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h2>New Lead</h2>
            <button class="modal-close" @click="closeModal">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>First Name</label>
                <input v-model="newLead.first_name" type="text" placeholder="e.g. Marcus" />
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input v-model="newLead.last_name" type="text" placeholder="e.g. Webb" />
              </div>
            </div>
            <div class="form-group">
              <label>Company</label>
              <input v-model="newLead.company" type="text" placeholder="e.g. Webb Builders" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Status</label>
                <select v-model="newLead.status">
                  <option value="New">New</option>
                  <option value="Quoted">Quoted</option>
                  <option value="Won">Won</option>
                  <option value="Lost">Lost</option>
                </select>
              </div>
              <div class="form-group">
                <label>Value ($)</label>
                <input v-model.number="newLead.value" type="number" placeholder="e.g. 5000" />
              </div>
            </div>
            <div class="form-group">
              <label>Assigned To</label>
              <input v-model="newLead.assigned_to" type="text" placeholder="e.g. Jake R." />
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-cancel" @click="closeModal">Cancel</button>
            <button class="btn-save" :disabled="isSubmitting" @click="submitNewLead">
              <span v-if="isSubmitting">Saving…</span>
              <span v-else>Save Lead</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api';

import Sidebar    from '@/components/Sidebar.vue'
import Topbar     from '@/components/Topbar.vue'
import KpiCards   from '@/components/KpiCards.vue'
import LeadsChart from '@/components/LeadsChart.vue'
import Pipeline   from '@/components/Pipeline.vue'
import LeadsTable from '@/components/LeadsTable.vue'

// ── HELPERS ────────────────────────────────────────────────────────────────
// Normalise API lead → shape the template expects
const normalise = (l) => ({
  ...l,
  name:     `${l.first_name} ${l.last_name}`.trim(),
  assigned: l.assigned_to || '',
  date:     (l.created_at || '').split('T')[0],
  value:    parseFloat(l.value) || 0,
  status:   (l.status || 'New').toLowerCase(),
})

// ── LEADS DATA ─────────────────────────────────────────────────────────────
const leads = ref([])
const statusFilter = ref('all')
const isLoading = ref(false)

const fetchLeads = async () => {
  isLoading.value = true
  try {
    const { data } = await api.get('/leads')
    // Laravel paginator returns { data: [...] }, plain array also handled
    leads.value = (data.data ?? data).map(normalise)
  } catch (error) {
    console.error('Failed to fetch leads:', error)
    leads.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchLeads())

// ── FILTER ─────────────────────────────────────────────────────────────────
const filteredLeads = computed(() => {
  if (statusFilter.value === 'all') return leads.value
  return leads.value.filter(l => l.status === statusFilter.value)
})

const handleFilterChange = (status) => {
  statusFilter.value = status
}

// ── ROW CLICK ──────────────────────────────────────────────────────────────
const handleRowClick = (lead) => {
  console.log('Lead clicked:', lead)
}

// ── KPI CARDS ──────────────────────────────────────────────────────────────
const kpiCards = computed(() => {
  const wonLeads = leads.value.filter(l => l.status === 'won')
  const revenueWon = wonLeads.reduce((sum, l) => sum + l.value, 0)
  const conversionRate = leads.value.length
    ? Math.round((wonLeads.length / leads.value.length) * 100)
    : 0

  return [
    { label: 'Total Leads',     icon: '🎯', value: leads.value.length,                        delta: '12%', deltaType: 'up',   deltaLabel: 'vs last month' },
    { label: 'Revenue Won',     icon: '💵', value: '$' + (revenueWon / 1000).toFixed(1) + 'k', delta: '8%',  deltaType: 'up',   deltaLabel: 'vs last month' },
    { label: 'Jobs Scheduled',  icon: '🔧', value: '—',                                        delta: '',    deltaType: 'up',   deltaLabel: ''              },
    { label: 'Conversion Rate', icon: '📈', value: conversionRate + '%',                        delta: '4%',  deltaType: 'up',   deltaLabel: 'this quarter'  },
  ]
})

// ── CHART DATA ─────────────────────────────────────────────────────────────
const chartData = computed(() => {
  const count = (s) => leads.value.filter(l => l.status === s).length
  return {
    labels: ['New', 'Quoted', 'Won', 'Lost'],
    values: [count('new'), count('quoted'), count('won'), count('lost')],
  }
})

// ── PIPELINE ───────────────────────────────────────────────────────────────
const pipelineStages = computed(() => {
  const total = leads.value.length || 1
  const count = (s) => leads.value.filter(l => l.status === s).length
  return [
    { label: 'New',    count: count('new')    + ' leads', pct: Math.round((count('new')    / total) * 100), color: '#3b82f6' },
    { label: 'Quoted', count: count('quoted') + ' leads', pct: Math.round((count('quoted') / total) * 100), color: '#f5a623' },
    { label: 'Won',    count: count('won')    + ' leads', pct: Math.round((count('won')    / total) * 100), color: '#22c55e' },
    { label: 'Lost',   count: count('lost')   + ' leads', pct: Math.round((count('lost')   / total) * 100), color: '#ef4444' },
  ]
})

// ── NEW LEAD MODAL ─────────────────────────────────────────────────────────
const showModal    = ref(false)
const isSubmitting = ref(false)

const defaultLead = () => ({
  first_name: '', last_name: '', company: '',
  status: 'New', value: '', assigned_to: '',
})
const newLead = ref(defaultLead())

const openNewLeadModal = () => { showModal.value = true }

const closeModal = () => {
  showModal.value = false
  newLead.value = defaultLead()
}

const submitNewLead = async () => {
  if (!newLead.value.first_name.trim() || !newLead.value.last_name.trim()) return

  isSubmitting.value = true
  try {
    const { data } = await api.post('/leads', newLead.value)
    leads.value.unshift(normalise(data))
    closeModal()
  } catch (error) {
    console.error('Failed to create lead:', error)
  } finally {
    isSubmitting.value = false
  }
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