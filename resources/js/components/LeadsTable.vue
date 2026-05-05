<template>
  <section class="table-section">
    <div class="section-header">
      <h2>Recent Leads</h2>
      <a href="#" class="view-all">View all →</a>
    </div>

    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Value</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="lead in leads"
          :key="lead.name"
          @click="$emit('row-click', lead)"
        >
          <td>
            <div class="customer-cell">
              <div
                class="c-avatar"
                :style="{ background: lead.color + '22', color: lead.color }"
              >
                {{ initials(lead.name) }}
              </div>
              <div>
                <div class="c-name">{{ lead.name }}</div>
                <div class="c-sub">{{ lead.company }}</div>
              </div>
            </div>
          </td>
          <td>
            <span :class="['badge', `badge-${lead.status}`]">
              {{ capitalize(lead.status) }}
            </span>
          </td>
          <td class="text-muted">{{ lead.assigned }}</td>
          <td class="value-cell">${{ lead.value.toLocaleString() }}</td>
          <td class="text-muted">{{ lead.date }}</td>
        </tr>

        <tr v-if="leads.length === 0">
          <td colspan="5" class="empty-state">No leads found.</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup>
defineProps({
  leads: {
    type: Array,
    default: () => [
      { name: 'Marcus Webb',  company: 'Webb Builders',    status: 'won',    assigned: 'Jake R.',  value: 7200, date: 'Feb 22', color: '#f97316' },
      
    ],
  },
})

defineEmits(['row-click'])

const initials = (name) => name.split(' ').map(w => w[0]).join('')
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
</script>