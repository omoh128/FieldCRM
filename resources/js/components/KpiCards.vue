<template>
  <section class="cards">
    <div
      v-for="(card, index) in cards"
      :key="card.label"
      class="card"
      :class="`card-${index + 1}`"
    >
      <div class="card-header">
        <span class="card-label">{{ card.label }}</span>
        <div class="card-icon">{{ card.icon }}</div>
      </div>
      <div class="card-value">{{ card.value }}</div>
      <div class="card-delta">
        <span :class="card.deltaType === 'up' ? 'delta-up' : 'delta-down'">
          {{ card.deltaType === 'up' ? '↑' : '↓' }} {{ card.delta }}
        </span>
        {{ card.deltaLabel }}
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  cards: {
    type: Array,
    default: () => [
      {
        label: 'Total Leads',
        icon: '🎯',
        value: '48',
        delta: '12%',
        deltaType: 'up',
        deltaLabel: 'vs last month',
      },
      {
        label: 'Revenue Won',
        icon: '💵',
        value: '$24.3k',
        delta: '8%',
        deltaType: 'up',
        deltaLabel: 'vs last month',
      },
      {
        label: 'Jobs Scheduled',
        icon: '🔧',
        value: '18',
        delta: '2',
        deltaType: 'down',
        deltaLabel: 'vs last week',
      },
      {
        label: 'Conversion Rate',
        icon: '📈',
        value: '32%',
        delta: '4%',
        deltaType: 'up',
        deltaLabel: 'this quarter',
      },
    ],
  },
})
</script>