<template>
  <div class="mis-cursos-estudiante">
    <div class="container">
      <div class="header">
        <h1>📚 Mis Cursos</h1>
        <p>Aquí puedes ver todos los cursos en los que te has inscrito</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <!-- Estado de carga -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Cargando tus cursos...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <div class="error-content">
          <h2>❌ Error al cargar</h2>
          <p>{{ error }}</p>
          <button @click="cargarMisCursos" class="btn btn-primary">Reintentar</button>
        </div>
      </div>

      <!-- Sin cursos -->
      <div v-else-if="misCursos.length === 0" class="empty-state">
        <div class="empty-content">
          <h2>📖 No tienes cursos todavía</h2>
          <p>Explora nuestros cursos disponibles y comienza a aprender</p>
          <router-link to="/courses" class="btn btn-primary">
            🔍 Ver Cursos Disponibles
          </router-link>
        </div>
      </div>

      <!-- Lista de cursos -->
      <div v-else class="cursos-grid">
        <div v-for="inscripcion in misCursos" :key="inscripcion.id" class="curso-card">
          <div class="curso-header">
            <div class="curso-info">
              <h3>{{ inscripcion.curso.titulo }}</h3>
              <p class="maestro">👨‍🏫 {{ inscripcion.curso.maestro?.nombre || 'Instructor no disponible' }}</p>
            </div>
            <div class="estado-badge" :class="estadoClass(inscripcion.estado)">
              {{ estadoTexto(inscripcion.estado) }}
            </div>
          </div>
          
          <div class="curso-content">
            <p class="descripcion">{{ inscripcion.curso.descripcion }}</p>
            
            <div class="curso-meta">
              <span class="duracion">
                ⏱️ {{ inscripcion.curso.duracion }} horas
              </span>
              <span class="nivel">
                🎯 {{ inscripcion.curso.nivel }}
              </span>
              <span class="categoria">
                📂 {{ inscripcion.curso.categoria }}
              </span>
            </div>

            <!-- Información de progreso -->
            <div class="progreso-info">
              <div class="progreso-bar">
                <div class="progreso-fill" :style="{ width: calcularProgreso(inscripcion) + '%' }"></div>
              </div>
              <span class="progreso-text">{{ calcularProgreso(inscripcion) }}% completado</span>
            </div>

            <!-- Información del pago -->
            <div v-if="inscripcion.pagos && inscripcion.pagos.length > 0" class="pago-info">
              <div class="pago-detalle">
                <span class="monto">💰 Pagado: Q{{ (inscripcion.pagos[0].monto_centavos / 100).toFixed(2) }}</span>
                <span class="fecha">📅 {{ formatearFecha(inscripcion.created_at) }}</span>
              </div>
            </div>
          </div>

          <div class="curso-actions">
            <button 
              class="btn btn-primary" 
              @click="accederCurso(inscripcion.curso.id)"
              :disabled="inscripcion.estado !== 'pagado'"
            >
              🎓 {{ inscripcion.estado === 'pagado' ? 'Acceder al Curso' : 'Pago Pendiente' }}
            </button>
            <button class="btn btn-outline" @click="verDetalles(inscripcion.id)">
              📋 Ver Detalles
            </button>
          </div>
        </div>
      </div>

      <!-- Estadísticas del estudiante -->
      <div v-if="misCursos.length > 0" class="estadisticas">
        <h2>📊 Mis Estadísticas</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-number">{{ misCursos.length }}</span>
            <span class="stat-label">Cursos Inscritos</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">{{ cursosActivos }}</span>
            <span class="stat-label">Cursos Activos</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">{{ horasTotales }}</span>
            <span class="stat-label">Horas de Estudio</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">Q{{ totalInvertido }}</span>
            <span class="stat-label">Total Invertido</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

interface Curso {
  id: number
  titulo: string
  descripcion: string
  precio: number
  duracion: number
  nivel: string
  categoria: string
  maestro?: {
    nombre: string
  }
}

interface Pago {
  id: number
  monto_centavos: number
  metodo_pago: string
  created_at: string
}

interface Inscripcion {
  id: number
  estado: string
  created_at: string
  curso: Curso
  pagos?: Pago[]
}

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const error = ref('')
const misCursos = ref<Inscripcion[]>([])

// Computed properties para estadísticas
const cursosActivos = computed(() => 
  misCursos.value.filter(i => i.estado === 'pagado').length
)

const horasTotales = computed(() =>
  misCursos.value.reduce((total, i) => 
    i.estado === 'pagado' ? total + (i.curso.duracion || 0) : total, 0
  )
)

const totalInvertido = computed(() =>
  misCursos.value.reduce((total, i) => {
    if (i.pagos && i.pagos.length > 0) {
      return total + (i.pagos[0].monto_centavos / 100)
    }
    return total
  }, 0).toFixed(2)
)

const estadoClass = (estado: string) => {
  switch (estado) {
    case 'pagado':
      return 'success'
    case 'pendiente':
      return 'warning'
    case 'cancelado':
      return 'danger'
    default:
      return 'secondary'
  }
}

const estadoTexto = (estado: string) => {
  switch (estado) {
    case 'pagado':
      return '✅ Activo'
    case 'pendiente':
      return '⏳ Pendiente'
    case 'cancelado':
      return '❌ Cancelado'
    default:
      return estado
  }
}

const formatearFecha = (fecha: string) => {
  return new Date(fecha).toLocaleDateString('es-GT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const calcularProgreso = (inscripcion: Inscripcion) => {
  // Por ahora retornamos un progreso simulado
  if (inscripcion.estado === 'pagado') {
    return Math.floor(Math.random() * 100)
  }
  return 0
}

const cargarMisCursos = async () => {
  if (!authStore.isAuthenticated()) {
    router.push('/login')
    return
  }

  loading.value = true
  error.value = ''

  try {
    const response = await fetch('http://localhost:8000/api/mis-cursos', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    const data = await response.json()

    if (response.ok) {
      misCursos.value = data.inscripciones || []
    } else {
      throw new Error(data.error || 'Error al cargar los cursos')
    }
  } catch (err: any) {
    error.value = err.message || 'Error al cargar los cursos'
    console.error('Error:', err)
  } finally {
    loading.value = false
  }
}

const accederCurso = (cursoId: number) => {
  router.push(`/curso/${cursoId}/contenido`)
}

const verDetalles = (inscripcionId: number) => {
  router.push(`/inscripcion/${inscripcionId}/detalles`)
}

onMounted(() => {
  cargarMisCursos()
})
</script>

<style scoped>
.mis-cursos-estudiante {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --danger: #e05252;
  --warning: #f7b267;

  min-height: 100vh;
  padding: 2.5rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 3rem;
  color: #12222b;
}

.header h1 {
  font-size: 2.2rem;
  margin-bottom: 0.6rem;
  letter-spacing: 0.04em;
}

.header p {
  font-size: 0.98rem;
  opacity: 0.9;
  margin-bottom: 1.1rem;
}

.btn-back {
  display: inline-block;
  padding: 0.65rem 1.4rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  color: #12222b;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  transition:
    background 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.22);
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(10, 28, 24, 0.35);
}

/* ESTADOS GENERALES */
.loading {
  text-align: center;
  padding: 4rem 0;
  color: #1f2e3a;
}

.spinner {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 4px solid rgba(79, 144, 133, 0.2);
  border-top-color: var(--emerald-dark);
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state,
.empty-state {
  text-align: center;
  padding: 3.5rem 0 1rem;
}

.error-content,
.empty-content {
  max-width: 520px;
  margin: 0 auto;
  padding: 2rem 1.8rem;
  border-radius: 20px;
  background: rgba(249, 252, 251, 0.96);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(163, 216, 195, 0.9);
  box-shadow:
    0 22px 60px rgba(10, 28, 24, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  color: #1f2e3a;
}

.error-content h2,
.empty-content h2 {
  margin-bottom: 0.7rem;
  font-size: 1.4rem;
}

.error-content p,
.empty-content p {
  font-size: 0.95rem;
  opacity: 0.9;
  margin-bottom: 1.3rem;
}

/* GRID DE CURSOS */
.cursos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1.9rem;
  margin-bottom: 3rem;
}

.curso-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 22px;
  padding: 1.8rem 1.6rem 1.6rem;
  box-shadow:
    0 24px 65px rgba(10, 28, 24, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(175, 219, 203, 0.9);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
}

.curso-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 30px 80px rgba(5, 15, 13, 0.9),
    0 0 0 1px rgba(255, 255, 255, 1);
}

.curso-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.3rem;
}

.curso-info h3 {
  margin: 0 0 0.4rem;
  color: #12222b;
  font-size: 1.25rem;
}

.maestro {
  margin: 0;
  color: #60707e;
  font-size: 0.9rem;
}

/* BADGES DE ESTADO */
.estado-badge {
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}

.estado-badge.success {
  background: rgba(79, 144, 133, 0.12);
  color: var(--emerald-dark);
  border: 1px solid rgba(79, 144, 133, 0.7);
}

.estado-badge.warning {
  background: rgba(247, 178, 103, 0.12);
  color: #ad651e;
  border: 1px solid rgba(247, 178, 103, 0.8);
}

.estado-badge.danger {
  background: rgba(224, 82, 82, 0.09);
  color: #b02e2e;
  border: 1px solid rgba(224, 82, 82, 0.85);
}

.estado-badge.secondary {
  background: rgba(163, 177, 193, 0.2);
  color: #435161;
  border: 1px solid rgba(163, 177, 193, 0.8);
}

/* CONTENIDO DEL CURSO */
.descripcion {
  color: #4d5a65;
  margin-bottom: 1.1rem;
  font-size: 0.9rem;
  line-height: 1.6;
}

.curso-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.2rem;
}

.curso-meta span {
  font-size: 0.78rem;
  color: #455463;
  background: #edf3f1;
  padding: 0.35rem 0.8rem;
  border-radius: 999px;
}

/* PROGRESO */
.progreso-info {
  margin-bottom: 1.1rem;
}

.progreso-bar {
  width: 100%;
  height: 8px;
  border-radius: 999px;
  background: #e0e7eb;
  overflow: hidden;
  margin-bottom: 0.35rem;
}

.progreso-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--emerald-dark), var(--emerald-primary));
  transition: width 0.3s ease;
}

.progreso-text {
  font-size: 0.78rem;
  color: #60707e;
}

/* PAGO */
.pago-info {
  background: linear-gradient(120deg, #e5f7ef, #f0faf6);
  border-radius: 14px;
  padding: 0.75rem 0.9rem;
  border-left: 4px solid var(--emerald-primary);
  margin-bottom: 1.25rem;
}

.pago-detalle {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #32414d;
}

.monto {
  font-weight: 600;
}

.fecha {
  color: #7a8a95;
}

/* ACCIONES */
.curso-actions {
  display: flex;
  gap: 0.7rem;
}

.btn {
  flex: 1;
  padding: 0.7rem 1.4rem;
  border-radius: 999px;
  border: none;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  text-decoration: none;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    background 0.18s ease,
    color 0.18s ease,
    border-color 0.18s ease;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 16px 35px rgba(6, 22, 18, 0.7);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 20px 55px rgba(5, 15, 13, 0.9);
}

.btn-outline {
  background: transparent;
  color: var(--emerald-dark);
  border: 2px solid rgba(79, 144, 133, 0.8);
}

.btn-outline:hover {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 16px 35px rgba(6, 22, 18, 0.7);
}

/* ESTADÍSTICAS */
.estadisticas {
  background: rgba(249, 252, 251, 0.96);
  border-radius: 22px;
  padding: 1.9rem 1.7rem;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(163, 216, 195, 0.9);
  box-shadow:
    0 24px 65px rgba(10, 28, 24, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.95);
  color: #12222b;
}

.estadisticas h2 {
  text-align: center;
  margin-bottom: 1.7rem;
  font-size: 1.4rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.3rem;
}

.stat-card {
  padding: 1.2rem 1rem;
  border-radius: 18px;
  text-align: center;
  background: radial-gradient(circle at top left, #f9fdfa 0, #e7f2ec 45%, #d8e7e0 100%);
  box-shadow: 0 18px 40px rgba(10, 28, 24, 0.35);
}

.stat-number {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 0.3rem;
}

.stat-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: #5b6a76;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .cursos-grid {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .header h1 {
    font-size: 1.9rem;
  }

  .curso-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .curso-actions {
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .curso-card {
    padding: 1.5rem 1.3rem;
  }

  .estadisticas {
    padding: 1.6rem 1.3rem;
  }
}
</style>
