


<template>
  <header :class="{ scrolled: scrolled }" id="site-header">
    <router-link to="/" class="logo">
      <div class="logo-mark">🏥</div>
      <div class="logo-words">
        <span class="logo-main">Meridian Health</span>
        <span class="logo-sub">Virtual Clinic</span>
      </div>
    </router-link>

    <nav class="desktop-nav">
      <router-link to="/" active-class="active">Home</router-link>
      <router-link to="/services" active-class="active">Services</router-link>
      <router-link to="/book" active-class="active">Book</router-link>
      <router-link to="/doctor" active-class="active">Doctor</router-link>
      <router-link to="/book" class="dn-cta">Get Started</router-link>
    </nav>

    <button 
      class="burger" 
      @click="$emit('toggle-nav')" 
      :class="{ open: navOpen }"
      aria-label="Toggle navigation"
    >
      <span></span><span></span><span></span>
    </button>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// Define the props coming from the parent (Home.vue)
defineProps({
  navOpen: Boolean
})

// Define the events this component can send to the parent
defineEmits(['toggle-nav'])

const scrolled = ref(false)

const handleScroll = () => {
  scrolled.value = window.scrollY > 55
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>