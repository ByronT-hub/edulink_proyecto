<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-container">
        <router-link to="/" class="nav-logo">
          EduLink
        </router-link>
        
        <div class="nav-menu">
          <router-link to="/" class="nav-link">Inicio</router-link>
          <router-link to="/courses" class="nav-link">Cursos</router-link>
          
          <div v-if="isAuthenticated" class="nav-user">
            <router-link to="/dashboard" class="nav-link">Dashboard</router-link>
            <router-link to="/certificates" class="nav-link">Certificados</router-link>
            <span class="nav-welcome">Hola, {{ student?.nombre }}</span>
            <button @click="handleLogout" class="btn btn-outline">Cerrar Sesión</button>
          </div>
          
          <div v-else class="nav-auth">
            <router-link to="/login" class="nav-link">Iniciar Sesión</router-link>
            <router-link to="/register" class="btn btn-primary">Registrarse</router-link>
          </div>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <router-view />
    </main>

    <footer class="footer">
      <div class="footer-content">
        <p>&copy; 2024 EduLink - Plataforma de cursos online</p>
        <div class="footer-links">
          <a href="#" class="footer-link">Términos</a>
          <a href="#" class="footer-link">Privacidad</a>
          <a href="#" class="footer-link">Soporte</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated())
const student = computed(() => authStore.user)

const handleLogout = async () => {
  try {
    console.log('Initiating logout...')
    await authStore.logout()
    console.log('Logout completed, redirecting to home')
    router.push('/')
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
  }
}

onMounted(() => {
  authStore.initializeFromStorage()
})
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-logo {
  font-size: 1.5rem;
  font-weight: bold;
  text-decoration: none;
  color: white;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
}

.nav-user, .nav-auth {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-welcome {
  font-size: 0.9rem;
  opacity: 0.9;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  text-decoration: none;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s;
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: #ff6b6b;
  color: white;
}

.btn-primary:hover {
  background: #ee5a5a;
}

.btn-outline {
  background: transparent;
  color: white;
  border: 1px solid white;
}

.btn-outline:hover {
  background: white;
  color: #667eea;
}

.main-content {
  min-height: calc(100vh - 140px);
  padding: 2rem 0;
}

.footer {
  background: #2c3e50;
  color: white;
  padding: 2rem 0;
  margin-top: auto;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-links {
  display: flex;
  gap: 2rem;
}

.footer-link {
  color: white;
  text-decoration: none;
  opacity: 0.8;
  transition: opacity 0.3s;
}

.footer-link:hover {
  opacity: 1;
}

@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-menu {
    flex-direction: column;
    gap: 1rem;
  }
  
  .footer-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
}
</style>