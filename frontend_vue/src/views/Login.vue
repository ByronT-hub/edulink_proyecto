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
/* Paleta de Colores Esmeralda Sutil y Apagado */
:root {
  /* Verde Esmeralda sutil */
  --emerald-primary: #558B84; 
  /* Tono más oscuro para hover y botones principales */
  --emerald-dark: #3E6C66;     
  /* Fondo muy claro, casi blanco */
  --neutral-background: #F8F9FB; 
  /* Texto principal/fondo oscuro (azul medianoche) */
  --neutral-dark: #34495e;     
  /* Acento muy claro y suave */
  --accent-highlight: #A3D8C3; 
  --border-radius-primary: 16px; 
}

/* --- ESTILO GLOBAL DE FUENTE --- */
.login {
  min-height: calc(100vh - 140px);
  display: flex;
  align-items: center;
  /* Fondo usando degradado sutil de esmeralda */
  background: linear-gradient(135deg, var(--emerald-primary) 0%, #7CB8B0 100%);
  padding: 2rem 0;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif; 
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: center;
}

/* --- CONTENEDOR DEL FORMULARIO --- */
.login-form-container {
  background: white;
  padding: 3rem;
  border-radius: var(--border-radius-primary); /* Bordes redondeados */
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 420px; /* Ligeramente más ancho */
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 2.2rem; /* Ligeramente más grande */
  color: var(--neutral-dark);
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.login-header p {
  color: #6c757d;
  font-size: 1.1rem;
}

.login-form {
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.8rem; /* Más espacio entre grupos */
}

.form-group label {
  display: block;
  margin-bottom: 0.6rem;
  color: var(--neutral-dark);
  font-weight: 600; /* Fuente más audaz */
}

/* --- INPUTS --- */
.form-input {
  width: 100%;
  padding: 1rem 1.25rem; /* Más padding horizontal */
  border: 1px solid #e9ecef;
  border-radius: 10px; /* Bordes suaves */
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--emerald-primary); /* Acento esmeralda en foco */
  box-shadow: 0 0 0 4px rgba(85, 139, 132, 0.15); /* Sombra suave de acento */
}

.form-input:disabled {
  background-color: var(--neutral-background);
  cursor: not-allowed;
}

/* --- MENSAJE DE ERROR --- */
.error-message {
  background: #fff3cd; /* Amarillo suave para error (neutral pero visible) */
  border: 1px solid #ffe0a3;
  color: #856404;
  padding: 0.85rem 1.2rem;
  border-radius: 10px;
  margin-bottom: 1.8rem;
  font-size: 0.95rem;
}

/* --- BOTONES --- */
.btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 50px; /* Estilo píldora para elegancia moderna */
  font-size: 1.1rem; /* Botón ligeramente más grande */
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: var(--emerald-dark); /* Color más fuerte para el CTA */
  color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary); /* Se aclara ligeramente en hover */
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.btn-primary:disabled {
  background: #b0c4de; /* Tono más neutro y suave cuando está deshabilitado */
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-full {
  width: 100%;
}

/* --- FOOTER --- */
.login-footer {
  text-align: center;
}

.login-footer p {
  color: #6c757d;
  margin: 0;
}

.link {
  color: var(--emerald-primary); /* Link usa el color principal esmeralda */
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

/* --- MEDIA QUERIES (Responsividad) --- */
@media (max-width: 768px) {
  .login-form-container {
    margin: 1rem;
    padding: 2rem;
  }
  
  .login-header h1 {
    font-size: 1.75rem;
  }
}
</style>