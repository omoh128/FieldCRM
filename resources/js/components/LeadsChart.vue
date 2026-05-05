<template>
  <div class="chart-section">
    <div class="section-header">
      <h2>Leads by Status</h2>
      <a href="#" class="view-all">This month ↓</a>
    </div>
    <div class="chart-body">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  chartData: {
    type: Object,
    default: () => ({
      labels: ['New', 'Quoted', 'Won', 'Lost'],
      values: [12, 18, 11, 7],
    }),
  },
})

const chartCanvas = ref(null)
let chartInstance = null

const buildChart = () => {
  if (chartInstance) chartInstance.destroy()

  const ctx = chartCanvas.value.getContext('2d')
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.chartData.labels,
      datasets: [
        {
          data: props.chartData.values,
          backgroundColor: [
            'rgba(59,130,246,0.7)',
            'rgba(245,166,35,0.7)',
            'rgba(34,197,94,0.7)',
            'rgba(239,68,68,0.7)',
          ],
          borderColor: ['#3b82f6', '#f5a623', '#22c55e', '#ef4444'],
          borderWidth: 1.5,
          borderRadius: 6,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: { color: '#6b7280', font: { family: 'DM Sans' } },
        },
        y: {
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: { color: '#6b7280', font: { family: 'DM Sans' } },
        },
      },
    },
  })
}

onMounted(() => buildChart())
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy() })

watch(() => props.chartData, () => buildChart(), { deep: true })
</script>