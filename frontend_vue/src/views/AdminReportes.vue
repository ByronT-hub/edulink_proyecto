<template>
  <div>
    <h1>Reportes y Métricas</h1>
    <div>
      <button @click="fetchInscripciones" class="btn btn-primary">Ver Reporte de Inscripciones</button>
      <button @click="fetchPagos" class="btn btn-primary">Ver Reporte de Pagos</button>
      <button @click="fetchMetricas" class="btn btn-primary">Ver Métricas de Cursos</button>
    </div>
    <div v-if="loading">Cargando...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="report">
      <pre>{{ report }}</pre>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

const loading = ref(false)
const error = ref('')
const report = ref('')

const fetchInscripciones = async () => {
  loading.value = true
  error.value = ''
  report.value = ''
  try {
    const token = localStorage.getItem('edulink_token')
    const res = await axios.get('/api/reportes/inscripciones', {
      headers: { Authorization: `Bearer ${token}` }
    })
    report.value = JSON.stringify(res.data, null, 2)
  } catch (e) {
    error.value = 'Error al obtener reporte de inscripciones.'
  } finally {
    loading.value = false
  }
}

const fetchPagos = async () => {
  loading.value = true
  error.value = ''
  report.value = ''
  try {
    const token = localStorage.getItem('edulink_token')
    const res = await axios.get('/api/reportes/pagos', {
      headers: { Authorization: `Bearer ${token}` }
    })
    report.value = JSON.stringify(res.data, null, 2)
  } catch (e) {
    error.value = 'Error al obtener reporte de pagos.'
  } finally {
    loading.value = false
  }
}

const fetchMetricas = async () => {
  loading.value = true
  error.value = ''
  report.value = ''
  try {
    const token = localStorage.getItem('edulink_token')
    const res = await axios.get('/api/metricas/cursos', {
      headers: { Authorization: `Bearer ${token}` }
    })
    report.value = JSON.stringify(res.data, null, 2)
  } catch (e) {
    error.value = 'Error al obtener métricas de cursos.'
  } finally {
    loading.value = false
  }
}
</script>
