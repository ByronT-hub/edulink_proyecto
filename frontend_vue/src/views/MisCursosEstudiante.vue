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
  // En el futuro se puede implementar el tracking real del progreso
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
  // Redirigir a la vista del curso o mostrar contenido
  router.push(`/curso/${cursoId}/contenido`)
}

const verDetalles = (inscripcionId: number) => {
  // Redirigir a una página de detalles de la inscripción
  router.push(`/inscripcion/${inscripcionId}/detalles`)
}

onMounted(() => {
  cargarMisCursos()
})
</script>

<style scoped>
.mis-cursos-estudiante {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.header {
  text-align: center;
  margin-bottom: 3rem;
  color: white;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.header p {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.btn-back {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  border-radius: 25px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.loading {
  text-align: center;
  padding: 4rem 0;
  color: white;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state, .empty-state {
  text-align: center;
  padding: 4rem 0;
}

.error-content, .empty-content {
  background: rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 16px;
  backdrop-filter: blur(10px);
  color: white;
  max-width: 500px;
  margin: 0 auto;
}

.empty-content h2, .error-content h2 {
  margin-bottom: 1rem;
}

.cursos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.curso-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}

.curso-card:hover {
  transform: translateY(-8px);
}

.curso-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.curso-info h3 {
  margin: 0 0 0.5rem;
  color: #2d3748;
  font-size: 1.5rem;
}

.maestro {
  margin: 0;
  color: #718096;
  font-size: 1rem;
}

.estado-badge {
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.875rem;
  font-weight: 600;
  text-align: center;
}

.estado-badge.success {
  background: linear-gradient(45deg, #48bb78, #38a169);
  color: white;
}

.estado-badge.warning {
  background: linear-gradient(45deg, #ed8936, #dd6b20);
  color: white;
}

.estado-badge.danger {
  background: linear-gradient(45deg, #f56565, #e53e3e);
  color: white;
}

.descripcion {
  color: #4a5568;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.curso-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.curso-meta span {
  font-size: 0.875rem;
  color: #718096;
  background: #f7fafc;
  padding: 0.5rem 1rem;
  border-radius: 20px;
}

.progreso-info {
  margin-bottom: 1.5rem;
}

.progreso-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progreso-fill {
  height: 100%;
  background: linear-gradient(90deg, #4299e1, #3182ce);
  transition: width 0.3s ease;
}

.progreso-text {
  font-size: 0.875rem;
  color: #718096;
}

.pago-info {
  background: linear-gradient(45deg, #f0fff4, #e6fffa);
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  border-left: 4px solid #48bb78;
}

.pago-detalle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
}

.monto {
  font-weight: 600;
  color: #2d3748;
}

.fecha {
  color: #718096;
}

.curso-actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  flex: 1;
  font-size: 0.875rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(45deg, #4299e1, #3182ce);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
}

.btn-outline {
  background: transparent;
  color: #4299e1;
  border: 2px solid #4299e1;
}

.btn-outline:hover {
  background: #4299e1;
  color: white;
}

.estadisticas {
  background: rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  color: white;
}

.estadisticas h2 {
  text-align: center;
  margin-bottom: 2rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  border-radius: 16px;
  text-align: center;
  backdrop-filter: blur(10px);
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.875rem;
  opacity: 0.9;
}

@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }
  
  .cursos-grid {
    grid-template-columns: 1fr;
  }
  
  .curso-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .curso-actions {
    flex-direction: column;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>