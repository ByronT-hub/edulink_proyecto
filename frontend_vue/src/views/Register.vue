<template>
  <div class="register">
    <div class="container">
      <div class="register-form-container">
        <div class="register-header">
          <h1>Crear Cuenta</h1>
          <p>Únete a miles de estudiantes y comienza tu aprendizaje</p>
        </div>

        <form @submit.prevent="handleSubmit" class="register-form">
          <div class="form-group">
            <label for="role">Tipo de Usuario</label>
            <select
              id="role"
              v-model="form.role"
              required
              class="form-input"
              :disabled="loading"
            >
              <option value="">Selecciona tu rol</option>
              <option value="estudiante">Estudiante</option>
              <option value="maestro">Maestro</option>
              <option value="admin">Administrador</option>
            </select>
          </div>

          <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input
              id="nombre"
              v-model="form.nombre"
              type="text"
              required
              class="form-input"
              placeholder="Ej: Juan Pérez"
              :disabled="loading"
            />
          </div>

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

          <!-- Campos específicos para maestros -->
          <div v-if="form.role === 'maestro'" class="form-group">
            <label for="especialidad">Especialidad</label>
            <input
              id="especialidad"
              v-model="form.especialidad"
              type="text"
              required
              class="form-input"
              placeholder="Ej: Programación Web, Diseño Gráfico"
              :disabled="loading"
            />
          </div>

          <div v-if="form.role === 'maestro'" class="form-group">
            <label for="biografia">Biografía (Opcional)</label>
            <textarea
              id="biografia"
              v-model="form.biografia"
              class="form-input"
              placeholder="Cuéntanos sobre tu experiencia..."
              rows="3"
              :disabled="loading"
            ></textarea>
          </div>

          <div v-if="form.role === 'maestro'" class="form-group">
            <label for="telefono">Teléfono (Opcional)</label>
            <input
              id="telefono"
              v-model="form.telefono"
              type="tel"
              class="form-input"
              placeholder="Ej: +502 1234-5678"
              :disabled="loading"
            />
          </div>

          <!-- Campos específicos para estudiantes -->
          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="carnet">Número de Carnet *</label>
            <input
              id="carnet"
              v-model="form.carnet"
              type="text"
              required
              class="form-input"
              placeholder="Ej: 2024-12345"
              :disabled="loading"
            />
          </div>

          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="telefono_estudiante">Teléfono *</label>
            <input
              id="telefono_estudiante"
              v-model="form.telefono"
              type="tel"
              required
              class="form-input"
              placeholder="Ej: +502 1234-5678"
              :disabled="loading"
            />
          </div>

          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="carrera">Carrera *</label>
            <input
              id="carrera"
              v-model="form.carrera"
              type="text"
              required
              class="form-input"
              placeholder="Ej: Ingeniería de Sistemas"
              :disabled="loading"
            />
          </div>

          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="universidad">Universidad *</label>
            <input
              id="universidad"
              v-model="form.universidad"
              type="text"
              required
              class="form-input"
              placeholder="Ej: Universidad de San Carlos"
              :disabled="loading"
            />
          </div>

          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="nivel_estudio">Nivel de Estudio *</label>
            <select
              id="nivel_estudio"
              v-model="form.nivel_estudio"
              required
              class="form-input"
              :disabled="loading"
            >
              <option value="">Selecciona tu nivel</option>
              <option value="Técnico">Técnico</option>
              <option value="Licenciatura">Licenciatura</option>
              <option value="Maestría">Maestría</option>
              <option value="Doctorado">Doctorado</option>
              <option value="Otro">Otro</option>
            </select>
          </div>

          <div v-if="form.role === 'estudiante'" class="form-group">
            <label for="intereses">Intereses (Opcional)</label>
            <textarea
              id="intereses"
              v-model="form.intereses"
              class="form-input"
              placeholder="Ej: Programación, Diseño Web, Marketing Digital..."
              rows="2"
              :disabled="loading"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="contrasena">Contraseña</label>
            <input
              id="contrasena"
              v-model="form.contrasena"
              type="password"
              required
              minlength="6"
              class="form-input"
              placeholder="Mínimo 6 caracteres"
              :disabled="loading"
            />
          </div>

          <div class="form-group">
            <label for="confirmar_contrasena">Confirmar Contraseña</label>
            <input
              id="confirmar_contrasena"
              v-model="form.confirmar_contrasena"
              type="password"
              required
              class="form-input"
              placeholder="Repite tu contraseña"
              :disabled="loading"
            />
          </div>

          <div v-if="validationError" class="error-message">
            {{ validationError }}
          </div>

          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <div v-if="successMessage" class="success-message">
            {{ successMessage }}
          </div>

          <button 
            type="submit" 
            :disabled="loading || !isFormValid" 
            class="btn btn-primary btn-full"
          >
            {{ loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
          </button>
        </form>

        <div class="register-footer">
          <p>
            ¿Ya tienes cuenta? 
            <router-link to="/login" class="link">Inicia sesión aquí</router-link>
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

interface RegisterFormData {
  nombre: string
  correo: string
  contrasena: string
  confirmar_contrasena: string
  role: string
  especialidad?: string
  biografia?: string
  telefono?: string
  carnet?: string
  carrera?: string
  universidad?: string
  nivel_estudio?: string
  intereses?: string
}

const form = ref<RegisterFormData>({
  nombre: '',
  correo: '',
  contrasena: '',
  confirmar_contrasena: '',
  role: ''
})

const successMessage = ref('')

const loading = computed(() => authStore.loading)
const error = computed(() => authStore.error)

const validationError = computed(() => {
  if (form.value.contrasena && form.value.confirmar_contrasena) {
    if (form.value.contrasena !== form.value.confirmar_contrasena) {
      return 'Las contraseñas no coinciden'
    }
  }
  if (form.value.contrasena && form.value.contrasena.length < 6) {
    return 'La contraseña debe tener al menos 6 caracteres'
  }
  if (form.value.role === 'maestro' && !form.value.especialidad) {
    return 'La especialidad es requerida para maestros'
  }
  if (form.value.role === 'estudiante') {
    if (!form.value.carnet) return 'El número de carnet es requerido'
    if (!form.value.telefono) return 'El teléfono es requerido'
    if (!form.value.carrera) return 'La carrera es requerida'
    if (!form.value.universidad) return 'La universidad es requerida'
    if (!form.value.nivel_estudio) return 'El nivel de estudio es requerido'
  }
  return null
})

const isFormValid = computed(() => {
  const baseValid = form.value.nombre && 
         form.value.correo && 
         form.value.contrasena && 
         form.value.confirmar_contrasena &&
         form.value.role &&
         !validationError.value

  // Para maestros, también validar especialidad
  if (form.value.role === 'maestro') {
    return baseValid && form.value.especialidad
  }
  
  // Para estudiantes, validar campos obligatorios
  if (form.value.role === 'estudiante') {
    return baseValid && 
           form.value.carnet && 
           form.value.telefono && 
           form.value.carrera && 
           form.value.universidad && 
           form.value.nivel_estudio
  }
  
  return baseValid
})

const handleSubmit = async () => {
  if (!isFormValid.value) return

  try {
    // Preparar datos según el rol
    const { confirmar_contrasena, ...baseData } = form.value
    const userData: any = {
      nombre: baseData.nombre,
      correo: baseData.correo,
      contrasena: baseData.contrasena,
      role: baseData.role
    }
    
    // Solo agregar campos específicos de maestro si el rol es maestro
    if (baseData.role === 'maestro') {
      userData.especialidad = baseData.especialidad || ''
      userData.biografia = baseData.biografia || ''
      userData.telefono = baseData.telefono || ''
    }
    
    // Agregar campos específicos de estudiante si el rol es estudiante
    if (baseData.role === 'estudiante') {
      userData.carnet = baseData.carnet || ''
      userData.telefono = baseData.telefono || ''
      userData.carrera = baseData.carrera || ''
      userData.universidad = baseData.universidad || ''
      userData.nivel_estudio = baseData.nivel_estudio || ''
      userData.intereses = baseData.intereses || ''
    }
    
    console.log('Datos a enviar:', userData)
    const result = await authStore.register(userData)
    
    if (result && result.access_token) {
      successMessage.value = 'Cuenta creada exitosamente. Redirigiendo...'
      
      // Esperar un momento para mostrar el mensaje y luego redirigir
      setTimeout(() => {
        router.push('/dashboard')
      }, 2000)
    }
  } catch (err) {
    console.error('Error en registro:', err)
    // El error ya se maneja en el store
  }
}

onMounted(() => {
  console.log('Register onMounted - iniciando')
  
  // Inicializar el store desde localStorage
  authStore.initializeFromStorage()
  
  console.log('Auth state after init:', {
    hasToken: !!authStore.token,
    hasUser: !!authStore.user,
    isAuthenticated: authStore.isAuthenticated(),
    user: authStore.user
  })
  
  // Limpiar mensajes y errores previos
  authStore.clearError()
  successMessage.value = ''
  
  // Solo redirigir si ya está autenticado Y tiene un usuario válido
  if (authStore.isAuthenticated() && authStore.user) {
    console.log('Redirecting to dashboard - user is authenticated')
    router.push('/dashboard')
  } else {
    console.log('User is not authenticated, staying on register page')
  }
})
</script>

<style scoped>
.register {
  min-height: calc(100vh - 140px);
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: center;
}

.register-form-container {
  background: white;
  padding: 3rem;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-header h1 {
  font-size: 2rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.register-header p {
  color: #6c757d;
  font-size: 1rem;
}

.register-form {
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
  border-color: #ff6b6b;
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
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

.success-message {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
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
  background: #ff6b6b;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #ee5a5a;
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

.register-footer {
  text-align: center;
}

.register-footer p {
  color: #6c757d;
  margin: 0;
}

.link {
  color: #ff6b6b;
  text-decoration: none;
  font-weight: 500;
}

.link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .register-form-container {
    margin: 1rem;
    padding: 2rem;
  }
  
  .register-header h1 {
    font-size: 1.75rem;
  }
}
</style>