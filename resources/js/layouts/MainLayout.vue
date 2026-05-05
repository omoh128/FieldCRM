<template>
<div class="dashboard">
  <Sidebar />

  <main class="main">
    <slot />
  </main>
</div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue'
</script>