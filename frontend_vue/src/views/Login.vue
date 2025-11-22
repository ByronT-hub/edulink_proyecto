<template>
  <div class="login">
    <div class="container">
      <div class="login-form-container">
        <div class="login-header">
          <h1>Iniciar Sesión</h1>
          <p>Accede a tu cuenta para continuar aprendiendo</p>
        </div>

        <form @submit.prevent="handleSubmit" class="login-form">
          <div class="form-group">
            <label for="correo">Correo Electrónico</label>
            <input
              id="correo"
              v-model="form.correo"
              type="email"
              required
              class="form-input"
              placeholder="tu@email.com"
              :disabled="loading"
            />
          </div>

          <div class="form-group">
            <label for="contrasena">Contraseña</label>
            <input
              id="contrasena"
              v-model="form.contrasena"
              type="password"
              required
              class="form-input"
              placeholder="••••••••"
              :disabled="loading"
            />
          </div>

          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <button 
            type="submit" 
            :disabled="loading" 
            class="btn btn-primary btn-full"
          >
            {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
          </button>
        </form>

        <div class="login-footer">
          <p>
            ¿No tienes cuenta? 
            <router-link to="/register" class="link">Regístrate aquí</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  correo: '',
  contrasena: ''
})

const loading = computed(() => authStore.loading)
const error = computed(() => authStore.error)

const handleSubmit = async () => {
  try {
    const result = await authStore.login(form.value.correo, form.value.contrasena)
    
    if (result && result.access_token) {
      // Redirigir al dashboard después del login exitoso
      router.push('/dashboard')
    }
  } catch (err) {
    // El error se maneja en el store
    console.error('Error en login:', err)
  }
}

onMounted(() => {
  // Inicializar el store desde localStorage
  authStore.initializeFromStorage()
  
  // Limpiar cualquier error previo
  authStore.clearError()
  
  // Si ya está autenticado, redirigir al dashboard
  if (authStore.isAuthenticated()) {
    router.push('/dashboard')
  }
})
</script>

<style scoped>
/* ===========================
   PALETA & BASE (match Home)
   =========================== */
.login {
  --emerald-primary: #4f9085;      /* Verde esmeralda sutil */
  --emerald-dark: #3a6f66;         /* Versión profunda para botones */
  --emerald-soft: #e4f1ed;         /* Fondo muy suave */
  --neutral-background: #f6f8fa;   /* Fondo general */
  --neutral-dark: #23313f;         /* Texto principal */
  --accent-highlight: #a3d8c3;     /* Acento claro */
  --border-radius-primary: 18px;

  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 1.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  position: relative;
  overflow: hidden;
}

.login::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 10% 20%, rgba(79, 144, 133, 0.22), transparent 55%),
              radial-gradient(circle at 80% 80%, rgba(163, 216, 195, 0.4), transparent 60%);
  opacity: 0.85;
  pointer-events: none;
}

.container {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  width: 100%;
  display: flex;
  justify-content: center;
}

/* ===========================
   CARD DEL FORMULARIO
   =========================== */
.login-form-container {
  background: #ffffff;
  width: 100%;
  max-width: 440px;
  padding: 2.8rem 2.6rem;
  border-radius: var(--border-radius-primary);
  box-shadow:
    0 22px 60px rgba(15, 35, 34, 0.22),
    0 0 0 1px rgba(255, 255, 255, 0.75);
  position: relative;
  overflow: hidden;
}

/* detalle lateral esmeralda suave */
.login-form-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 144, 133, 0.08), transparent 60%);
  opacity: 0.9;
  pointer-events: none;
}

/* pequeña barra/acento a la izquierda */
.login-form-container::after {
  content: '';
  position: absolute;
  left: 0;
  top: 18%;
  width: 4px;
  height: 64%;
  border-radius: 0 999px 999px 0;
  background: linear-gradient(to bottom, var(--emerald-dark), var(--emerald-primary));
}

/* el contenido real encima de los overlays */
.login-header,
.login-form,
.login-footer {
  position: relative;
  z-index: 1;
}

/* ===========================
   HEADER
   =========================== */
.login-header {
  text-align: center;
  margin-bottom: 2.4rem;
}

.login-header h1 {
  font-size: 2.1rem;
  color: #12222b;
  margin-bottom: 0.6rem;
  font-weight: 700;
  letter-spacing: 0.03em;
}

.login-header p {
  color: #6d7a86;
  font-size: 0.98rem;
}

/* ===========================
   FORM
   =========================== */
.login-form {
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.7rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--neutral-dark);
  font-weight: 600;
  font-size: 0.95rem;
}

/* INPUTS */
.form-input {
  width: 100%;
  padding: 0.9rem 1.1rem;
  border-radius: 12px;
  border: 1px solid #dde3ea;
  font-size: 0.98rem;
  background: #fdfefe;
  transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
  box-sizing: border-box;
}

.form-input::placeholder {
  color: #a0acb7;
}

.form-input:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
  background: #ffffff;
}

.form-input:disabled {
  background-color: var(--neutral-background);
  cursor: not-allowed;
}

/* ===========================
   MENSAJE DE ERROR
   =========================== */
.error-message {
  background: #fff5f2;
  border: 1px solid #f3c3b4;
  color: #953125;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  margin-bottom: 1.6rem;
  font-size: 0.9rem;
}

/* ===========================
   BOTONES (match Home)
   =========================== */
.btn {
  padding: 0.9rem 2rem;
  border-radius: 999px;
  text-decoration: none;
  cursor: pointer;
  font-size: 0.92rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  font-weight: 600;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}

.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 25px rgba(16, 52, 46, 0.3);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 14px 30px rgba(8, 32, 26, 0.4);
}

.btn-primary:disabled {
  background: #9fb4c1;
  box-shadow: none;
  cursor: not-allowed;
  transform: none;
}

.btn-full {
  width: 100%;
}

/* ===========================
   FOOTER
   =========================== */
.login-footer {
  text-align: center;
}

.login-footer p {
  color: #6d7a86;
  font-size: 0.95rem;
  margin: 0;
}

.link {
  color: var(--emerald-dark);
  text-decoration: none;
  font-weight: 600;
  margin-left: 0.2rem;
}

.link:hover {
  text-decoration: underline;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .login {
    padding: 2.5rem 1.2rem;
  }

  .login-form-container {
    padding: 2.3rem 1.8rem;
  }

  .login-header h1 {
    font-size: 1.8rem;
  }
}
</style>
