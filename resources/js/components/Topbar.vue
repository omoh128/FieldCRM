<template>
  <header class="topbar">
    <div class="topbar-left">
      <h1>{{ title }}</h1>
      <p>{{ formattedDate }}</p>
    </div>

    <div class="topbar-right">
      <select v-model="selectedStatus" @change="$emit('filter-change', selectedStatus)">
        <option value="all">All Leads</option>
        <option value="new">New</option>
        <option value="quoted">Quoted</option>
        <option value="won">Won</option>
        <option value="lost">Lost</option>
      </select>

      <button class="btn-add" @click="$emit('new-lead')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        New Lead
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard Overview',
  },
})

const emit = defineEmits(['new-lead', 'filter-change'])

const selectedStatus = ref('all')

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})
</script>