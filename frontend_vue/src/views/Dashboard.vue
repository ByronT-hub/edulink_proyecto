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
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.dashboard {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 22px;

  min-height: calc(100vh - 80px);
  padding: 3rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* ===========================
   HEADER GENERAL
   =========================== */
.dashboard-header {
  text-align: center;
  margin-bottom: 2.4rem;
}

.dashboard-header h1 {
  font-size: 2.1rem;
  color: #12222b;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
}

.dashboard-header p {
  font-size: 0.98rem;
  color: #6d7a86;
  margin: 0.2rem 0;
}

/* Especialidad maestro */
.especialidad {
  font-style: italic;
  color: var(--emerald-dark) !important;
  font-weight: 600;
}

/* ===========================
   GRID DE CARDS
   =========================== */
.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.8rem;
}

/* CARD */
.stat-card {
  background: rgba(255, 255, 255, 0.98);
  padding: 1.8rem 1.6rem;
  border-radius: var(--border-radius-primary);
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  display: flex;
  align-items: flex-start;
  gap: 1.1rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease,
    background 0.22s ease;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 30px 80px rgba(8, 32, 26, 0.35);
  border-color: var(--emerald-primary);
}

/* Icono */
.stat-icon {
  font-size: 2.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 3rem;
}

/* Texto */
.stat-info h3 {
  font-size: 1.1rem;
  color: #12222b;
  margin-bottom: 0.3rem;
}

.stat-info p {
  color: #6d7a86;
  margin-bottom: 1.1rem;
  font-size: 0.9rem;
}

/* ===========================
   BOTONES REUTILIZADOS
   =========================== */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.7rem 1.4rem;
  border-radius: 999px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.86rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border: none;
  cursor: pointer;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}

/* Principal */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 12px 28px rgba(8, 32, 26, 0.55);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 16px 36px rgba(5, 22, 18, 0.65);
}

/* Success (pero en esmeralda, no verde chillón) */
.btn-success {
  background: linear-gradient(135deg, #4f9085 0%, #5ca598 100%);
  color: #ffffff;
  box-shadow: 0 12px 28px rgba(8, 32, 26, 0.5);
}

.btn-success:hover {
  background: linear-gradient(135deg, #458378 0%, #4f9386 100%);
  transform: translateY(-1px);
  box-shadow: 0 16px 38px rgba(5, 22, 18, 0.65);
}

/* Outline */
.btn-outline {
  background: #ffffff;
  border: 1px solid rgba(163, 216, 195, 0.9);
  color: var(--emerald-dark);
  box-shadow: 0 8px 22px rgba(15, 35, 34, 0.2);
}

.btn-outline:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
  transform: translateY(-1px);
}

/* ===========================
   ESTADO LOADING / FALLBACK
   =========================== */
.dashboard-loading {
  margin-top: 2.5rem;
  display: flex;
  justify-content: center;
}

.loading-content {
  max-width: 480px;
  background: rgba(255, 255, 255, 0.98);
  padding: 2.2rem 2rem;
  border-radius: 22px;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  text-align: center;
}

.loading-content h1 {
  color: #12222b;
  margin-bottom: 0.8rem;
  font-size: 1.4rem;
}

.loading-content p {
  color: #6d7a86;
  margin: 0.25rem 0;
  font-size: 0.9rem;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .dashboard-header h1 {
    font-size: 1.8rem;
  }

  .stat-card {
    padding: 1.5rem 1.3rem;
    border-radius: 20px;
  }

  .dashboard-stats {
    grid-template-columns: 1fr;
  }

  .loading-content {
    padding: 1.8rem 1.5rem;
  }
}
</style>
