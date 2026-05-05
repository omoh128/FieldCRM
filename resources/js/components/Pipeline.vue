<template>
  <div class="pipeline-section">
    <div class="section-header">
      <h2>Pipeline Stages</h2>
    </div>
    <div class="pipeline-body">
      <div v-for="stage in stages" :key="stage.label" class="pipe-item">
        <div class="pipe-info">
          <span class="pipe-label">{{ stage.label }}</span>
          <span class="pipe-count">{{ stage.count }}</span>
        </div>
        <div class="pipe-bar">
          <div
            class="pipe-fill"
            :style="{
              width: animated ? stage.pct + '%' : '0%',
              background: stage.color,
            }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

defineProps({
  stages: {
    type: Array,
    default: () => [
      { label: 'New Leads',   count: '12 leads', pct: 100, color: '#3b82f6' },
      { label: 'Contacted',   count: '9 leads',  pct: 75,  color: '#8b5cf6' },
      { label: 'Quoted',      count: '6 leads',  pct: 50,  color: '#f5a623' },
      { label: 'Negotiation', count: '4 leads',  pct: 33,  color: '#f97316' },
      { label: 'Won',         count: '3 leads',  pct: 25,  color: '#22c55e' },
    ],
  },
})

const animated = ref(false)

onMounted(() => {
  setTimeout(() => { animated.value = true }, 100)
})
</script>