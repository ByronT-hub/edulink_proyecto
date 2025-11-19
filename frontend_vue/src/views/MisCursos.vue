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
            <h3>{{ curso.titulo }}</h3>
            <span class="precio">${{ curso.precio }}</span>
          </div>
          
          <div class="curso-info">
            <p class="descripcion">{{ curso.descripcion }}</p>
            <div class="stats">
              <span class="stat">👥 {{ curso.estudiantes_inscritos || 0 }} estudiantes</span>
              <span class="stat">⏱️ {{ curso.duracion }} horas</span>
            </div>
          </div>

          <div class="curso-actions">
            <button class="btn btn-secondary" @click="editarCurso(curso.id)">
              ✏️ Editar
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
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header {
  text-align: center;
  margin-bottom: 3rem;
}

.header h1 {
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.header p {
  color: #718096;
  margin-bottom: 1rem;
}

.btn-back {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #e2e8f0;
  color: #2d3748;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.btn-back:hover {
  background: #cbd5e0;
}

.loading {
  text-align: center;
  padding: 4rem 0;
  color: #718096;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
}

.empty-content h2 {
  color: #2d3748;
  margin-bottom: 1rem;
}

.empty-content p {
  color: #718096;
  margin-bottom: 2rem;
}

.cursos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.curso-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.curso-card:hover {
  transform: translateY(-4px);
}

.curso-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.curso-header h3 {
  color: #2d3748;
  margin: 0;
}

.precio {
  background: #48bb78;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.descripcion {
  color: #718096;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat {
  color: #718096;
  font-size: 0.875rem;
}

.curso-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  flex: 1;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover {
  background: #3182ce;
}

.btn-secondary {
  background: #e2e8f0;
  color: #2d3748;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

.btn-danger {
  background: #f56565;
  color: white;
}

.btn-danger:hover {
  background: #e53e3e;
}

.fab {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 56px;
  height: 56px;
  background: #48bb78;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 1.5rem;
  box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
  transition: all 0.3s ease;
}

.fab:hover {
  transform: scale(1.1);
  background: #38a169;
}
</style>