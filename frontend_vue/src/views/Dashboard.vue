<template>
  <div class="dashboard">
    <div class="container">
      <!-- Dashboard Admin -->
      <div v-if="user?.role === 'admin'" class="admin-dashboard">
        <div class="dashboard-header">
          <h1>Panel de Administración</h1>
          <p>Bienvenido, {{ user.nombre }}</p>
        </div>
        
        <div class="dashboard-stats">
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
              <h3>Gestión de Usuarios</h3>
              <p>Crear, editar y eliminar usuarios</p>
              <button class="btn btn-primary">Gestionar Usuarios</button>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
              <h3>Gestión de Cursos</h3>
              <p>Supervisar todos los cursos</p>
              <button class="btn btn-primary">Ver Cursos</button>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
              <h3>Reportes</h3>
              <p>Métricas y estadísticas</p>
              <button class="btn btn-primary">Ver Reportes</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Maestro -->
      <div v-else-if="user?.role === 'maestro'" class="maestro-dashboard">
        <div class="dashboard-header">
          <h1>Panel del Maestro</h1>
          <p>Bienvenido, {{ user.nombre }}</p>
          <p class="especialidad">Especialidad: {{ user.especialidad }}</p>
        </div>
        
        <div class="dashboard-stats">
          <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div class="stat-info">
              <h3>Mis Cursos</h3>
              <p>Gestiona tus cursos</p>
              <router-link to="/maestro/mis-cursos" class="btn btn-primary">Ver Mis Cursos</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">➕</div>
            <div class="stat-info">
              <h3>Crear Curso</h3>
              <p>Añadir nuevo curso</p>
              <router-link to="/maestro/crear-curso" class="btn btn-success">Crear Curso</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-info">
              <h3>Estudiantes</h3>
              <p>Buscar estudiantes</p>
              <router-link to="/maestro/buscar-estudiantes" class="btn btn-primary">Buscar Estudiantes</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-info">
              <h3>Mi Perfil</h3>
              <p>Actualizar información</p>
              <router-link to="/maestro/mi-perfil" class="btn btn-outline">Editar Perfil</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Estudiante -->
      <div v-else-if="user?.role === 'estudiante'" class="estudiante-dashboard">
        <div class="dashboard-header">
          <h1>Mi Dashboard</h1>
          <p>Bienvenido, {{ user.nombre }}</p>
        </div>
        
        <div class="dashboard-stats">
          <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
              <h3>Mis Cursos</h3>
              <p>Ver cursos inscritos</p>
              <router-link to="/estudiante/mis-cursos" class="btn btn-primary">Ver Mis Cursos</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">🔍</div>
            <div class="stat-info">
              <h3>Explorar</h3>
              <p>Buscar nuevos cursos</p>
              <router-link to="/courses" class="btn btn-success">Ver Cursos Disponibles</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">📜</div>
            <div class="stat-info">
              <h3>Certificados</h3>
              <p>Mis logros académicos</p>
              <router-link to="/certificates" class="btn btn-outline">Ver Certificados</router-link>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-info">
              <h3>Mi Perfil</h3>
              <p>Actualizar información</p>
              <router-link to="/estudiante/mi-perfil" class="btn btn-outline">Editar Perfil</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Fallback si no hay rol definido -->
      <div v-else class="dashboard-loading">
        <div class="loading-content">
          <h1>Cargando Dashboard...</h1>
          <p>Detectando tipo de usuario...</p>
          <p>Usuario actual: {{ user ? user.nombre : 'No definido' }}</p>
          <p>Rol: {{ user ? user.role : 'No definido' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)

onMounted(async () => {
  console.log('Dashboard mounted, user:', user.value)
  
  // Inicializar store
  authStore.initializeFromStorage()
  
  // Si no está autenticado, redirigir al login
  if (!authStore.isAuthenticated()) {
    console.log('User not authenticated, redirecting to login')
    router.push('/login')
    return
  }
  
  console.log('User authenticated:', user.value)
  
  // Solo obtener datos del servidor si no tenemos el rol definido
  if (!user.value?.role) {
    try {
      await authStore.getCurrentUser()
      console.log('User data updated from server:', user.value)
    } catch (error) {
      console.error('Error al obtener datos del usuario:', error)
      // No redirigir inmediatamente si tenemos datos básicos
      if (!user.value?.nombre) {
        router.push('/login')
      }
    }
  } else {
    console.log('User data already complete:', user.value)
  }
})
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.dashboard-header {
  text-align: center;
  margin-bottom: 3rem;
}

.dashboard-header h1 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.dashboard-header p {
  font-size: 1.2rem;
  color: #666;
  margin: 0.5rem 0;
}

.especialidad {
  font-style: italic;
  color: #667eea !important;
  font-weight: 600;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 2rem;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.stat-info h3 {
  font-size: 2rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.stat-info p {
  color: #666;
  margin-bottom: 1.5rem;
}

.btn {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-success {
  background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
  color: white;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.btn-outline {
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
}

.btn-outline:hover {
  background: #667eea;
  color: white;
}

.dashboard-loading {
  text-align: center;
  padding: 4rem 2rem;
}

.loading-content {
  background: white;
  padding: 3rem;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.loading-content h1 {
  color: #333;
  margin-bottom: 1rem;
}

.loading-content p {
  color: #666;
  margin: 0.5rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-stats {
    grid-template-columns: 1fr;
  }
  
  .dashboard-header h1 {
    font-size: 2rem;
  }
  
  .stat-card {
    padding: 1.5rem;
  }
}
</style>