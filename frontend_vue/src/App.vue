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

onMounted(async () => {
  authStore.initializeFromStorage()
  // Validar sesión con backend
  if (authStore.token) {
    const valid = await authStore.getCurrentUser()
    if (!valid) {
      // Token inválido o backend caído
      await authStore.logout()
      router.push('/login')
    }
  }
})
</script>

<style scoped>
/* ===========================
   PALETA & BASE GLOBAL APP
   =========================== */
#app {
  --emerald-primary: #4f9085;      /* Verde esmeralda sutil */
  --emerald-dark: #3a6f66;         /* Versión profunda para botones/nav */
  --emerald-soft: #e4f1ed;         /* Fondo muy suave */
  --neutral-background: #f6f8fa;   /* Fondo general */
  --neutral-dark: #23313f;         /* Texto principal */
  --accent-highlight: #a3d8c3;     /* Acento claro */
  --border-radius-primary: 18px;

  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
  background: var(--neutral-background);
}

/* ===========================
   NAVBAR (estilo esmeralda)
   =========================== */
.navbar {
  background:
    linear-gradient(120deg, rgba(10, 28, 26, 0.98), rgba(23, 61, 55, 0.96));
  color: #f8fbff;
  padding: 0.85rem 0;
  box-shadow: 0 14px 30px rgba(0, 0, 0, 0.28);
  border-bottom: 1px solid rgba(163, 216, 195, 0.35);
  position: sticky;
  top: 0;
  z-index: 40;
  backdrop-filter: blur(14px);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Logo */
.nav-logo {
  font-size: 1.5rem;
  font-weight: 800;
  text-decoration: none;
  color: #fdfefe;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  position: relative;
}

.nav-logo::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -0.3rem;
  width: 38%;
  height: 2px;
  border-radius: 999px;
  background: linear-gradient(to right, var(--emerald-primary), var(--accent-highlight));
}

/* Menú */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

/* Links */
.nav-link {
  color: #e9f2f0;
  text-decoration: none;
  padding: 0.45rem 0.9rem;
  border-radius: 999px;
  font-size: 0.92rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  transition:
    background-color 0.22s ease,
    color 0.22s ease,
    box-shadow 0.22s ease,
    transform 0.22s ease;
}

.nav-link:hover {
  background-color: rgba(163, 216, 195, 0.16);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
  transform: translateY(-1px);
}

.nav-link.router-link-active {
  background-color: rgba(163, 216, 195, 0.28);
  color: #ffffff;
}

/* Secciones usuario/auth */
.nav-user,
.nav-auth {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-welcome {
  font-size: 0.86rem;
  opacity: 0.9;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  background: rgba(10, 36, 30, 0.8);
  border: 1px solid rgba(163, 216, 195, 0.35);
}

/* ===========================
   BOTONES (mismo estilo global)
   =========================== */
.btn {
  padding: 0.6rem 1.4rem;
  border-radius: 999px;
  text-decoration: none;
  cursor: pointer;
  font-size: 0.9rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  font-weight: 600;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}

/* CTA principal (Registrarse) */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 24px rgba(8, 32, 26, 0.55);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(5, 22, 18, 0.65);
}

/* Botón outline (Cerrar sesión) */
.btn-outline {
  background: transparent;
  color: #f5fbff;
  border: 1.3px solid rgba(245, 251, 255, 0.85);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
}

.btn-outline:hover {
  background: #ffffff;
  color: var(--emerald-dark);
  transform: translateY(-1px);
  border-color: transparent;
}

/* ===========================
   MAIN CONTENT
   =========================== */
.main-content {
  flex: 1;
  min-height: calc(100vh - 160px);
  padding: 2.5rem 0;
}

/* ===========================
   FOOTER (oscuro elegante)
   =========================== */
.footer {
  background: radial-gradient(circle at top, #2c3e50 0, #182531 55%, #101820 100%);
  color: #f5f8ff;
  padding: 1.8rem 0;
  margin-top: auto;
  border-top: 1px solid rgba(163, 216, 195, 0.25);
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.2rem;
}

.footer-content p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.9;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-link {
  color: var(--accent-highlight);
  text-decoration: none;
  font-size: 0.9rem;
  opacity: 0.85;
  position: relative;
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.footer-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -0.2rem;
  width: 0;
  height: 2px;
  border-radius: 999px;
  background: var(--accent-highlight);
  transition: width 0.22s ease;
}

.footer-link:hover {
  opacity: 1;
  transform: translateY(-1px);
}

.footer-link:hover::after {
  width: 100%;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.8rem;
  }
  
  .nav-menu {
    flex-wrap: wrap;
    row-gap: 0.7rem;
  }

  .footer-content {
    flex-direction: column;
    gap: 0.8rem;
    text-align: center;
  }

  .footer-links {
    justify-content: center;
  }
}
</style>
