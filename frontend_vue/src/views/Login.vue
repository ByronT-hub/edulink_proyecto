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
.login {
  min-height: calc(100vh - 140px);
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: center;
}

.login-form-container {
  background: white;
  padding: 3rem;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 2rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.login-header p {
  color: #6c757d;
  font-size: 1rem;
}

.login-form {
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2c3e50;
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.error-message {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a6fd8;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
}

.btn-full {
  width: 100%;
}

.login-footer {
  text-align: center;
}

.login-footer p {
  color: #6c757d;
  margin: 0;
}

.link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.link:hover {
  text-decoration: underline;
}

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
