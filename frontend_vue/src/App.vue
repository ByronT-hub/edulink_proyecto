<script setup lang="ts">
import { RouterView } from 'vue-router';
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Encabezado simple -->
    <header class="px-6 py-4 border-b border-slate-800 bg-slate-900/80">
      <h1 class="text-xl font-semibold">EduLink – Plataforma de Cursos</h1>
    </header>

    <!-- Aquí Vue Router va a dibujar las páginas -->
    <main class="p-6">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Puedes dejarlo vacío o agregar algo luego */
</style>
