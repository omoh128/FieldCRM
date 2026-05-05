<template>
  <div class="invoices-page">
    <Sidebar />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Invoices</h1>
          <p>Track payments and outstanding balances</p>
        </div>
        <div class="topbar-right">
          <button class="btn btn-ghost" @click="exportInvoices">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
          </button>
        </div>
      </header>

      <div class="content">

        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card stat-blue">
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value">${{ fmt(stats.total_value) }}</div>
            <div class="stat-sub">All invoices</div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-label">Collected</div>
            <div class="stat-value">${{ fmt(stats.paid_value) }}</div>
            <div class="stat-sub">Paid invoices</div>
          </div>
          <div class="stat-card stat-amber">
            <div class="stat-label">Outstanding</div>
            <div class="stat-value">${{ fmt(stats.outstanding) }}</div>
            <div class="stat-sub">Awaiting payment</div>
          </div>
          <div class="stat-card stat-red">
            <div class="stat-label">Overdue</div>
            <div class="stat-value">{{ stats.overdue }}</div>
            <div class="stat-sub">Past due date</div>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
          <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input v-model="searchQuery" class="search-input" placeholder="Search invoices…" />
          </div>
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Statuses</option>
            <option value="unpaid">Unpaid</option>
            <option value="partial">Partial</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
          </select>
          <div class="toolbar-spacer"></div>
          <span class="count-label">{{ filteredInvoices.length }} invoice{{ filteredInvoices.length !== 1 ? 's' : '' }}</span>
        </div>

        <!-- Table -->
        <div class="table-card">
          <table>
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Job</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Due Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7"><div class="empty-state"><p>Loading invoices…</p></div></td>
              </tr>
              <tr v-else-if="pagedInvoices.length === 0">
                <td colspan="7">
                  <div class="empty-state">
                    <p>No invoices yet</p>
                    <span>Invoices are created automatically when a job is completed</span>
                  </div>
                </td>
              </tr>
              <tr v-for="inv in pagedInvoices" :key="inv.id" @click="openDetail(inv)">
                <td>
                  <div class="inv-number">{{ inv.invoice_number }}</div>
                </td>
                <td>
                  <div class="entity-cell">
                    <div class="e-avatar" :style="{ background: invColor(inv) + '22', color: invColor(inv) }">
                      {{ customerInitials(inv) }}
                    </div>
                    <div>
                      <div class="e-name">{{ customerName(inv) }}</div>
                      <div class="e-sub">{{ inv.lead?.company || '—' }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-muted">{{ inv.job?.title || '—' }}</td>
                <td class="value-cell">${{ fmt(inv.amount) }}</td>
                <td><span :class="['badge', `badge-${inv.status}`]">{{ capitalize(inv.status) }}</span></td>
                <td :class="['text-muted', { 'text-red': isOverdue(inv) }]">{{ formatDate(inv.due_date) }}</td>
                <td>
                  <div class="row-actions" @click.stop>
                    <button
                      v-if="inv.status !== 'paid'"
                      class="action-btn pay"
                      title="Send payment link"
                      @click="sendPaymentLink(inv)"
                      :disabled="sendingId === inv.id"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                      </svg>
                    </button>
                    <button class="action-btn del" title="Delete" @click="askDelete(inv.id)">
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
              Showing {{ Math.min((currentPage-1)*PAGE_SIZE+1, filteredInvoices.length) }}–{{ Math.min(currentPage*PAGE_SIZE, filteredInvoices.length) }} of {{ filteredInvoices.length }}
            </span>
            <div class="page-btns">
              <button class="page-btn" :disabled="currentPage===1" @click="currentPage--">‹</button>
              <button v-for="p in totalPages" :key="p" :class="['page-btn',{active:p===currentPage}]" @click="currentPage=p">{{ p }}</button>
              <button class="page-btn" :disabled="currentPage===totalPages" @click="currentPage++">›</button>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- ── DETAIL MODAL ── -->
    <transition name="fade">
      <div v-if="showDetailModal && selectedInvoice" class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal modal-wide">
          <div class="modal-header">
            <div>
              <span class="modal-title">{{ selectedInvoice.invoice_number }}</span>
              <span :class="['badge', `badge-${selectedInvoice.status}`]" style="margin-left:10px">{{ capitalize(selectedInvoice.status) }}</span>
            </div>
            <button class="modal-close" @click="showDetailModal = false">✕</button>
          </div>
          <div class="modal-body">
            <div class="detail-grid">
              <div class="detail-item"><div class="detail-label">Customer</div><div class="detail-value">{{ customerName(selectedInvoice) }}</div></div>
              <div class="detail-item"><div class="detail-label">Job</div><div class="detail-value">{{ selectedInvoice.job?.title || '—' }}</div></div>
              <div class="detail-item"><div class="detail-label">Amount</div><div class="detail-value value-cell">${{ fmt(selectedInvoice.amount) }}</div></div>
              <div class="detail-item"><div class="detail-label">Paid</div><div class="detail-value value-cell">${{ fmt(selectedInvoice.paid_amount) }}</div></div>
              <div class="detail-item"><div class="detail-label">Balance</div><div class="detail-value value-cell" :class="{ 'text-red': selectedInvoice.amount - selectedInvoice.paid_amount > 0 }">${{ fmt(selectedInvoice.amount - selectedInvoice.paid_amount) }}</div></div>
              <div class="detail-item"><div class="detail-label">Due Date</div><div class="detail-value" :class="{ 'text-red': isOverdue(selectedInvoice) }">{{ formatDate(selectedInvoice.due_date) }}</div></div>
            </div>

            <!-- Status update -->
            <div class="detail-section" style="margin-top:20px">
              <div class="form-group">
                <label class="form-label">Update Status</label>
                <select v-model="editStatus" class="form-select">
                  <option value="unpaid">Unpaid</option>
                  <option value="partial">Partial</option>
                  <option value="paid">Paid</option>
                  <option value="overdue">Overdue</option>
                </select>
              </div>
              <div class="form-group" style="margin-top:12px">
                <label class="form-label">Due Date</label>
                <input v-model="editDueDate" type="date" class="form-input" />
              </div>
              <div class="form-group" style="margin-top:12px">
                <label class="form-label">Notes</label>
                <textarea v-model="editNotes" class="form-textarea" rows="2" placeholder="Optional notes…"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="showDetailModal = false">Close</button>
            <button
              v-if="selectedInvoice.status !== 'paid'"
              class="btn btn-stripe"
              @click="sendPaymentLink(selectedInvoice)"
              :disabled="sendingId === selectedInvoice.id"
            >
              {{ sendingId === selectedInvoice.id ? 'Creating link…' : '💳 Send Stripe Link' }}
            </button>
            <button class="btn btn-primary" @click="saveInvoice" :disabled="isSaving">
              {{ isSaving ? 'Saving…' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── CONFIRM DELETE ── -->
    <transition name="fade">
      <div v-if="showConfirmModal" class="modal-overlay" @click.self="showConfirmModal = false">
        <div class="confirm-box">
          <div class="confirm-icon">🗑️</div>
          <h3>Delete Invoice?</h3>
          <p>This action cannot be undone.</p>
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

const PAGE_SIZE = 10
const COLORS = ['#f97316','#8b5cf6','#06b6d4','#22c55e','#ef4444','#f59e0b','#3b82f6','#ec4899']

// ── DATA ──────────────────────────────────────────────────────────────────────
const invoices  = ref([])
const isLoading = ref(false)
const isSaving  = ref(false)
const sendingId = ref(null)
const stats     = ref({ total_value: 0, paid_value: 0, outstanding: 0, overdue: 0, total: 0, paid: 0, unpaid: 0 })

const fetchInvoices = async () => {
  isLoading.value = true
  try {
    const [listRes, statsRes] = await Promise.all([
      api.get('/invoices', { params: { per_page: 200 } }),
      api.get('/invoices/stats'),
    ])
    invoices.value = listRes.data.data ?? listRes.data
    stats.value    = statsRes.data
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchInvoices)

// ── FILTERS ───────────────────────────────────────────────────────────────────
const searchQuery  = ref('')
const statusFilter = ref('')
const currentPage  = ref(1)

watch([searchQuery, statusFilter], () => { currentPage.value = 1 })

const filteredInvoices = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return invoices.value.filter(inv => {
    const name = customerName(inv).toLowerCase()
    const matchQ = !q || name.includes(q) || (inv.invoice_number || '').toLowerCase().includes(q) || (inv.job?.title || '').toLowerCase().includes(q)
    const matchS = !statusFilter.value || inv.status === statusFilter.value
    return matchQ && matchS
  })
})

const totalPages   = computed(() => Math.max(1, Math.ceil(filteredInvoices.value.length / PAGE_SIZE)))
const pagedInvoices = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return filteredInvoices.value.slice(start, start + PAGE_SIZE)
})

// ── HELPERS ───────────────────────────────────────────────────────────────────
const fmt             = (n) => (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const capitalize      = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const formatDate      = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
const isOverdue       = (inv) => inv.due_date && new Date(inv.due_date) < new Date() && inv.status !== 'paid'
const customerName    = (inv) => inv.lead ? `${inv.lead.first_name} ${inv.lead.last_name}`.trim() : '—'
const customerInitials = (inv) => inv.lead ? `${(inv.lead.first_name||'')[0]}${(inv.lead.last_name||'')[0]}`.toUpperCase() : '?'
const invColor        = (inv) => COLORS[(inv.id - 1) % COLORS.length]

// ── DETAIL MODAL ──────────────────────────────────────────────────────────────
const showDetailModal = ref(false)
const selectedInvoice = ref(null)
const editStatus      = ref('')
const editDueDate     = ref('')
const editNotes       = ref('')

const openDetail = (inv) => {
  selectedInvoice.value = inv
  editStatus.value      = inv.status
  editDueDate.value     = inv.due_date || ''
  editNotes.value       = inv.notes   || ''
  showDetailModal.value = true
}

const saveInvoice = async () => {
  isSaving.value = true
  try {
    const { data } = await api.put(`/invoices/${selectedInvoice.value.id}`, {
      status:   editStatus.value,
      due_date: editDueDate.value || null,
      notes:    editNotes.value,
    })
    const idx = invoices.value.findIndex(i => i.id === data.id)
    if (idx !== -1) invoices.value[idx] = { ...invoices.value[idx], ...data }
    selectedInvoice.value = { ...selectedInvoice.value, ...data }
    showToast('Invoice updated')
    await fetchInvoices() // refresh stats
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not save', 'error')
  } finally { isSaving.value = false }
}

// ── STRIPE PAYMENT LINK ───────────────────────────────────────────────────────
const sendPaymentLink = async (inv) => {
  sendingId.value = inv.id
  try {
    const { data } = await api.post(`/invoices/${inv.id}/checkout`)
    // Open Stripe checkout in new tab
    window.open(data.checkout_url, '_blank')
    showToast('Stripe checkout opened')
  } catch (err) {
    showToast(err.response?.data?.message || 'Could not create payment link', 'error')
  } finally { sendingId.value = null }
}

// ── DELETE ────────────────────────────────────────────────────────────────────
const showConfirmModal = ref(false)
const deletingId       = ref(null)
const askDelete        = (id) => { deletingId.value = id; showConfirmModal.value = true }

const confirmDelete = async () => {
  try {
    await api.delete(`/invoices/${deletingId.value}`)
    invoices.value = invoices.value.filter(i => i.id !== deletingId.value)
    showConfirmModal.value = false
    showToast('Invoice deleted')
    await fetchInvoices()
  } catch (err) {
    showToast('Could not delete invoice', 'error')
  }
}

// ── EXPORT ────────────────────────────────────────────────────────────────────
const exportInvoices = () => {
  const rows = filteredInvoices.value.map(inv => ({
    Invoice:  inv.invoice_number,
    Customer: customerName(inv),
    Job:      inv.job?.title || '',
    Amount:   inv.amount,
    Paid:     inv.paid_amount,
    Status:   inv.status,
    Due:      inv.due_date || '',
  }))
  if (!rows.length) return showToast('No data to export', 'error')
  const headers = Object.keys(rows[0])
  const csv = [headers.join(','), ...rows.map(r => headers.map(h => `"${r[h] ?? ''}"`).join(','))].join('\n')
  const a = document.createElement('a')
  a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  a.download = 'invoices.csv'; a.click()
  showToast(`Exported ${rows.length} invoices`)
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
.invoices-page { display: flex; min-height: 100vh; background: #0c0f1a; }
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
.btn-ghost   { background: transparent; color: #6b7280; border: 1px solid rgba(255,255,255,0.1); }
.btn-ghost:hover { color: #e8eaf2; }
.btn-danger  { background: #ef4444; color: #fff; }
.btn-danger:hover { opacity: 0.88; }
.btn-stripe  { background: #635bff; color: #fff; }
.btn-stripe:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-stripe:disabled { opacity: 0.5; cursor: not-allowed; }

.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
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
.stat-amber::before { background: #f5a623; }
.stat-red::before   { background: #ef4444; }
.stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; font-weight: 500; margin-bottom: 10px; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 700; color: #e8eaf2; line-height: 1; margin-bottom: 6px; }
.stat-sub   { font-size: 12px; color: #6b7280; }

.toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.search-wrap {
  display: flex; align-items: center; gap: 8px;
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 8px 12px; min-width: 240px;
}
.search-wrap svg { width: 14px; height: 14px; color: #6b7280; flex-shrink: 0; }
.search-input { background: none; border: none; outline: none; color: #e8eaf2; font-size: 13px; font-family: 'DM Sans', sans-serif; width: 100%; }
.filter-select {
  background: #131726; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 8px 12px; color: #e8eaf2;
  font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none; cursor: pointer;
}
.toolbar-spacer { flex: 1; }
.count-label { font-size: 12px; color: #6b7280; }

.table-card { background: #131726; border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
th { padding: 11px 18px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; text-align: left; }
tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; cursor: pointer; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #1a1f35; }
td { padding: 13px 18px; font-size: 13px; color: #e8eaf2; }
.text-muted { color: #6b7280; }
.text-red   { color: #f87171; }
.value-cell { font-family: 'Syne', sans-serif; font-weight: 600; color: #22c55e; }

.inv-number { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: #f5a623; letter-spacing: 0.05em; }

.entity-cell { display: flex; align-items: center; gap: 10px; }
.e-avatar { width: 32px; height: 32px; border-radius: 50%; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.e-name { font-weight: 500; font-size: 13px; }
.e-sub  { font-size: 11px; color: #6b7280; }

.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-unpaid  { background: rgba(59,130,246,0.15);  color: #60a5fa; }
.badge-partial { background: rgba(245,166,35,0.15);  color: #f5a623; }
.badge-paid    { background: rgba(34,197,94,0.15);   color: #4ade80; }
.badge-overdue { background: rgba(239,68,68,0.15);   color: #f87171; }

.row-actions { display: flex; align-items: center; gap: 4px; opacity: 0; transition: opacity 0.2s; }
tbody tr:hover .row-actions { opacity: 1; }
.action-btn { width: 28px; height: 28px; border-radius: 6px; background: rgba(255,255,255,0.05); border: none; color: #6b7280; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.action-btn svg { width: 13px; height: 13px; }
.action-btn.pay:hover { background: rgba(99,91,255,0.15); color: #818cf8; }
.action-btn.del:hover { background: rgba(239,68,68,0.15); color: #f87171; }
.action-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.07); }
.page-info  { font-size: 12px; color: #6b7280; }
.page-btns  { display: flex; gap: 4px; }
.page-btn   { width: 28px; height: 28px; border-radius: 6px; background: rgba(255,255,255,0.05); border: none; color: #6b7280; cursor: pointer; font-size: 13px; transition: all 0.2s; }
.page-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); color: #e8eaf2; }
.page-btn.active { background: #f5a623; color: #000; font-weight: 700; }
.page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.empty-state { text-align: center; padding: 48px 0; }
.empty-state p    { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 600; color: #e8eaf2; margin-bottom: 4px; }
.empty-state span { font-size: 12px; color: #6b7280; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #131726; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 24px 64px rgba(0,0,0,0.5); }
.modal-wide { max-width: 560px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.modal-title  { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #e8eaf2; }
.modal-close  { background: none; border: none; color: #6b7280; cursor: pointer; font-size: 16px; padding: 4px 8px; border-radius: 6px; }
.modal-close:hover { color: #e8eaf2; background: rgba(255,255,255,0.06); }
.modal-body   { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.07); }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-item { display: flex; flex-direction: column; gap: 4px; }
.detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; }
.detail-value { font-size: 14px; color: #e8eaf2; font-weight: 500; }
.detail-section { border-top: 1px solid rgba(255,255,255,0.07); padding-top: 16px; }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; }
.form-input, .form-select, .form-textarea {
  background: #1a1f35; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px; padding: 9px 12px; color: #e8eaf2;
  font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none;
  transition: border-color 0.2s; width: 100%; box-sizing: border-box;
}
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #f5a623; }
.form-textarea { resize: vertical; min-height: 60px; }

.confirm-box { background: #131726; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; max-width: 360px; text-align: center; box-shadow: 0 24px 64px rgba(0,0,0,0.5); }
.confirm-icon { font-size: 32px; margin-bottom: 12px; }
.confirm-box h3 { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: #e8eaf2; margin-bottom: 8px; }
.confirm-box p  { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
.confirm-btns   { display: flex; gap: 10px; justify-content: center; }

.toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; z-index: 200; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
.toast-success { background: #166534; color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
.toast-error   { background: #7f1d1d; color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to       { opacity: 0; transform: translateY(12px); }
</style>