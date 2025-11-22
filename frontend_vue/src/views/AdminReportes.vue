<template>
  <div class="admin-reportes">
    <div class="container">
      <!-- Header -->
      <div class="header">
        <button class="btn btn-outline volver-btn" @click="router.push('/dashboard')" style="margin-bottom: 1.2rem; float:left;">
          ← Volver
        </button>
        <h1>📊 Reportes y Métricas</h1>
        <p>Visión general del comportamiento de la plataforma EduLink</p>

        <div class="header-meta">
          <span class="badge badge-soft">Resumen ejecutivo</span>
          <span class="badge badge-pill">Actualizado automáticamente</span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="state-card loading-state">
        <div class="spinner"></div>
        <p>Cargando métricas...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="state-card error-state">
        <h2>❌ No se pudieron obtener las métricas</h2>
        <p>{{ error }}</p>
      </div>

      <!-- Métricas -->
      <div v-else class="metricas-resumen">
        <div
          class="metrica-card"
          v-for="(valor, clave) in metricas"
          :key="clave"
        >
          <div class="metrica-icon">
            {{ obtenerIcono(clave) }}
          </div>

          <div class="metrica-content">
            <div class="metrica-titulo">
              {{ formatearClave(clave) }}
            </div>

            <div class="metrica-valor">
              {{ valor }}
            </div>

            <div class="metrica-footer">
              <span class="pill pill-muted">Métrica clave</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Nota al pie -->
      <div class="footer-note">
        <span>ℹ️ Los valores se basan en la información actual registrada en el sistema.</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'


const router = useRouter()
const loading = ref(false)
const error = ref('')
const metricas = ref<any>({})

function formatearClave(clave: string) {
  const map: Record<string, string> = {
    total_usuarios: 'Total de Usuarios',
    total_maestros: 'Total de Maestros',
    total_estudiantes: 'Total de Estudiantes',
    total_cursos: 'Total de Cursos',
    total_inscripciones: 'Total de Inscripciones',
    total_pagos: 'Total de Pagos',
    monto_total: 'Monto Total Recaudado',
    cursos_activos: 'Cursos Activos',
    ultimo_usuario: 'Último Usuario Registrado',
    ultimo_curso: 'Último Curso Creado',
  }
  return map[clave] || clave.charAt(0).toUpperCase() + clave.slice(1)
}

function obtenerIcono(clave: string) {
  const iconMap: Record<string, string> = {
    total_usuarios: '👥',
    total_maestros: '👨‍🏫',
    total_estudiantes: '👨‍🎓',
    total_cursos: '📚',
    cursos_activos: '✅',
    total_inscripciones: '📝',
    total_pagos: '💳',
    monto_total: '💰',
    ultimo_usuario: '🧑',
    ultimo_curso: '✨',
  }
  return iconMap[clave] || '📈'
}

const fetchMetricasResumen = async () => {
  loading.value = true
  error.value = ''
  try {
    const token = localStorage.getItem('edulink_token')
    const res = await axios.get('/api/metricas/resumen', {
      headers: { Authorization: `Bearer ${token}` }
    })
    metricas.value = res.data
  } catch (e: any) {
    error.value = 'No se pudieron obtener las métricas.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchMetricasResumen)
</script>

<style scoped>
.admin-reportes {
  --emerald-primary: #4f9085;
  --emerald-dark: #173a38;
  --emerald-soft: #e4f1ed;
  --card-bg: #061516;
  --card-border: #12423b;

  min-height: 100vh;
  background: radial-gradient(circle at top left, #062121 0, #021314 40%, #010a0b 80%);
  padding: 3rem 0 3.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
  color: #f9fafb;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.6rem;
}

/* Header */
.header {
  text-align: center;
  margin-bottom: 2.8rem;
}

.header h1 {
  font-size: 2.2rem;
  margin-bottom: 0.4rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.header p {
  color: #a0b3b8;
  margin-bottom: 1.2rem;
  font-size: 0.98rem;
}

.header-meta {
  display: flex;
  justify-content: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.badge {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgba(163, 216, 195, 0.6);
}

.badge-soft {
  background: rgba(79, 144, 133, 0.16);
  color: #d5f4eb;
}

.badge-pill {
  background: rgba(13, 148, 136, 0.12);
  color: #9be7d6;
}

/* Grid de métricas */
.metricas-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 1.9rem;
  margin-bottom: 2.4rem;
}

.metrica-card {
  background: radial-gradient(circle at top left, #0c2527 0, var(--card-bg) 60%);
  border-radius: 22px;
  border: 1px solid rgba(18, 66, 59, 0.8);
  padding: 1.4rem 1.5rem;
  display: flex;
  gap: 1rem;
  box-shadow:
    0 26px 70px rgba(0, 0, 0, 0.68),
    0 0 0 1px rgba(255, 255, 255, 0.03);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.metrica-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 30px 80px rgba(0, 0, 0, 0.85),
    0 0 0 1px rgba(163, 216, 195, 0.5);
  border-color: var(--emerald-primary);
}

.metrica-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 18px;
  background: radial-gradient(circle at 20% 0, #4f9085 0, #193936 70%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.7rem;
  flex-shrink: 0;
}

.metrica-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.metrica-titulo {
  font-size: 0.88rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #9fd9c6;
}

.metrica-valor {
  font-size: 2rem;
  font-weight: 700;
  color: #f9fafb;
}

.metrica-footer {
  margin-top: 0.35rem;
}

.pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  border-radius: 999px;
  padding: 0.2rem 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.pill-muted {
  background: rgba(148, 163, 184, 0.12);
  color: #9ca3af;
}

/* Estados (loading / error) */
.state-card {
  max-width: 520px;
  margin: 0 auto 2.6rem;
  background: rgba(3, 15, 16, 0.96);
  border-radius: 22px;
  border: 1px solid rgba(18, 66, 59, 0.85);
  padding: 1.9rem 1.6rem;
  text-align: center;
  box-shadow:
    0 26px 70px rgba(0, 0, 0, 0.8),
    0 0 0 1px rgba(255, 255, 255, 0.03);
}

.loading-state p {
  margin: 0.4rem 0 0;
  color: #9fb6ba;
}

.error-state h2 {
  margin: 0 0 0.6rem;
  color: #fecaca;
  font-size: 1.2rem;
}

.error-state p {
  margin: 0;
  color: #fca5a5;
}

/* Spinner */
.spinner {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  border: 3px solid rgba(148, 163, 184, 0.4);
  border-top-color: #4f9085;
  margin: 0 auto 0.7rem;
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Nota al pie */
.footer-note {
  text-align: center;
  font-size: 0.8rem;
  color: #6b7f84;
  opacity: 0.85;
}

/* Responsive */
@media (max-width: 768px) {
  .header h1 {
    font-size: 1.9rem;
  }

  .metricas-resumen {
    grid-template-columns: 1fr;
  }

  .metrica-card {
    padding: 1.3rem 1.25rem;
  }
}
</style>
