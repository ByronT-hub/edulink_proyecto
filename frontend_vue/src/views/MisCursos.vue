<template>
  <div class="mis-cursos">
    <div class="container">
      <div class="header">
        <h1>📚 Mis Cursos</h1>
        <p>Gestiona todos los cursos que has creado</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="loading">
        <p>Cargando cursos...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="cursos.length === 0" class="empty-state">
        <div class="empty-content">
          <h2>📖 No tienes cursos creados</h2>
          <p>¡Crea tu primer curso y comienza a enseñar!</p>
          <router-link to="/maestro/crear-curso" class="btn btn-primary">
            ➕ Crear Mi Primer Curso
          </router-link>
        </div>
      </div>

      <!-- Cursos list -->
      <div v-else class="cursos-grid">
        <div v-for="curso in cursos" :key="curso.id" class="curso-card">
          <div class="curso-header">
            <div class="curso-title-block">
              <h3>{{ curso.titulo }}</h3>
              <p class="created-at">
                Creado el {{ new Date(curso.created_at).toLocaleDateString('es-ES') }}
              </p>
            </div>
            <span class="precio">Q{{ curso.precio }}</span>
          </div>
          
          <div class="curso-info">
            <p class="descripcion">{{ curso.descripcion }}</p>
            <div class="stats">
              <span class="stat">
                👥 <strong>{{ curso.estudiantes_inscritos || 0 }}</strong> estudiantes
              </span>
              <span class="stat">
                ⏱️ <strong>{{ curso.duracion }}</strong> horas
              </span>
            </div>
          </div>

          <div class="curso-actions">
            <button class="btn btn-secondary" @click="editarCurso(curso.id)">
              ✏️ Editar
            </button>
            <button class="btn btn-accent" @click="agregarTareas(curso.id)">
              📋 Agregar Tareas
            </button>
            <button class="btn btn-danger" @click="eliminarCurso(curso.id)">
              🗑️ Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Floating action button -->
      <router-link to="/maestro/crear-curso" class="fab">
        ➕
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
// Navegar a la vista para agregar/editar módulos/lecciones/tareas
const agregarTareas = (id: number) => {
  router.push(`/maestro/agregar-tareas/${id}`)
}
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

interface Curso {
  id: number
  titulo: string
  descripcion: string
  precio: number
  duracion: number
  estudiantes_inscritos?: number
  created_at: string
}

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(true)
const cursos = ref<Curso[]>([])

const cargarCursos = async () => {
  try {
    const user = authStore.user
    if (!user || user.role !== 'maestro') {
      router.push('/dashboard')
      return
    }

    const response = await fetch(`http://localhost:8000/api/maestros/${user.id}/cursos`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      cursos.value = data.cursos || data
    } else {
      console.error('Error al cargar cursos')
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const editarCurso = (id: number) => {
  router.push(`/maestro/editar-curso/${id}`)
}

const eliminarCurso = async (id: number) => {
  if (!confirm('¿Estás seguro de eliminar este curso?')) return
  
  try {
    const response = await fetch(`http://localhost:8000/api/maestros/cursos/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      cursos.value = cursos.value.filter(c => c.id !== id)
    } else {
      alert('Error al eliminar curso')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error al eliminar curso')
  }
}

onMounted(() => {
  cargarCursos()
})
</script>

<style scoped>
.mis-cursos {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --danger: #e05252;
  --danger-dark: #c43f3f;

  min-height: calc(100vh - 80px);
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  padding: 3rem 0 3.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 2.4rem;
  color: #12222b;
}

.header h1 {
  font-size: 2rem;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
}

.header p {
  font-size: 0.95rem;
  opacity: 0.85;
  margin-bottom: 1rem;
}

/* Botón volver */
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.55rem 1.1rem;
  background: rgba(255, 255, 255, 0.9);
  color: var(--emerald-dark);
  text-decoration: none;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  box-shadow:
    0 12px 28px rgba(15, 35, 34, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.7);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease;
}

.btn-back:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
  transform: translateY(-1px);
}

/* ESTADOS */
.loading {
  text-align: center;
  padding: 4rem 0;
  color: #5c6a74;
  font-size: 0.95rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
}

.empty-content {
  max-width: 460px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.96);
  border-radius: 26px;
  padding: 2.5rem 2rem;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
}

.empty-content h2 {
  color: #12222b;
  margin-bottom: 0.6rem;
}

.empty-content p {
  color: #5c6a74;
  margin-bottom: 1.6rem;
}

/* GRID DE CURSOS */
.cursos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 1.8rem;
}

/* CARD DE CURSO */
.curso-card {
  background: rgba(255, 255, 255, 0.97);
  border-radius: 22px;
  padding: 1.7rem 1.5rem 1.5rem;
  box-shadow:
    0 22px 60px rgba(15, 35, 34, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
}

.curso-card:hover {
  transform: translateY(-4px);
  box-shadow:
    0 26px 70px rgba(5, 22, 18, 0.7),
    0 0 0 1px rgba(228, 241, 237, 0.9);
  border-color: var(--emerald-primary);
}

.curso-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.curso-title-block h3 {
  color: #12222b;
  margin: 0 0 0.15rem;
  font-size: 1.05rem;
}

.created-at {
  margin: 0;
  font-size: 0.8rem;
  color: #8a96a1;
}

.precio {
  background: linear-gradient(135deg, #4f9085, #6fb4a8);
  color: #ffffff;
  padding: 0.25rem 0.8rem;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 10px 24px rgba(6, 29, 24, 0.45);
}

/* INFO */
.curso-info {
  margin-top: 0.15rem;
}

.descripcion {
  color: #5c6a74;
  margin-bottom: 0.9rem;
  font-size: 0.9rem;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.stats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.7rem;
}

.stat {
  font-size: 0.83rem;
  color: #6b7780;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: #f4f7f6;
}

.stat strong {
  color: #23313f;
}

/* ACCIONES */
.curso-actions {
  display: flex;
  gap: 0.7rem;
  margin-top: 0.8rem;
}

/* BOTONES */
.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Primary (crear curso) */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 14px 32px rgba(8, 32, 26, 0.55);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 18px 40px rgba(5, 22, 18, 0.65);
}

/* Secondary (editar) */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.8);
  box-shadow: 0 10px 24px rgba(15, 35, 34, 0.18);
}

.btn-secondary:hover {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* Danger (eliminar) */
.btn-danger {
  background: var(--danger);
  color: #ffffff;
  box-shadow: 0 12px 28px rgba(88, 20, 20, 0.55);
}

/* Botón Agregar Tareas */
.btn-accent {
  background: var(--emerald-primary);
  color: #fff;
  box-shadow: 0 10px 24px rgba(79, 144, 133, 0.18);
}
.btn-accent:hover {
  background: var(--emerald-dark);
  color: #fff;
  transform: translateY(-1px);
}

.btn-danger:hover {
  background: var(--danger-dark);
  transform: translateY(-1px);
}

/* FAB */
.fab {
  position: fixed;
  bottom: 2.2rem;
  right: 2.2rem;
  width: 58px;
  height: 58px;
  background: var(--emerald-dark);
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 1.6rem;
  box-shadow:
    0 20px 40px rgba(5, 22, 18, 0.8),
    0 0 0 3px rgba(228, 241, 237, 0.95);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease;
  z-index: 20;
}

.fab:hover {
  transform: translateY(-2px) scale(1.03);
  background: var(--emerald-primary);
  box-shadow:
    0 24px 56px rgba(3, 14, 11, 0.95),
    0 0 0 3px rgba(228, 241, 237, 0.95);
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
    font-size: 1.8rem;
  }

  .curso-card {
    padding: 1.5rem 1.4rem 1.3rem;
  }

  .fab {
    bottom: 1.6rem;
    right: 1.6rem;
  }
}
</style>
