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

  if (form.value.role === 'maestro') {
    return baseValid && form.value.especialidad
  }
  
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
    const { confirmar_contrasena, ...baseData } = form.value
    const userData: any = {
      nombre: baseData.nombre,
      correo: baseData.correo,
      contrasena: baseData.contrasena,
      role: baseData.role
    }
    
    if (baseData.role === 'maestro') {
      userData.especialidad = baseData.especialidad || ''
      userData.biografia = baseData.biografia || ''
      userData.telefono = baseData.telefono || ''
    }
    
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
      
      setTimeout(() => {
        router.push('/dashboard')
      }, 2000)
    }
  } catch (err) {
    console.error('Error en registro:', err)
  }
}

onMounted(() => {
  console.log('Register onMounted - iniciando')
  authStore.initializeFromStorage()
  console.log('Auth state after init:', {
    hasToken: !!authStore.token,
    hasUser: !!authStore.user,
    isAuthenticated: authStore.isAuthenticated(),
    user: authStore.user
  })
  authStore.clearError()
  successMessage.value = ''
  
  if (authStore.isAuthenticated() && authStore.user) {
    console.log('Redirecting to dashboard - user is authenticated')
    router.push('/dashboard')
  } else {
    console.log('User is not authenticated, staying on register page')
  }
})
</script>

<style scoped>
/* ===========================
   PALETA & BASE (match Home/Login)
   =========================== */
.register {
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

.register::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 12% 20%, rgba(79, 144, 133, 0.22), transparent 55%),
              radial-gradient(circle at 80% 82%, rgba(163, 216, 195, 0.4), transparent 60%);
  opacity: 0.9;
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
.register-form-container {
  background: #ffffff;
  width: 100%;
  max-width: 520px;
  padding: 2.7rem 2.6rem;
  border-radius: var(--border-radius-primary);
  box-shadow:
    0 24px 65px rgba(15, 35, 34, 0.24),
    0 0 0 1px rgba(255, 255, 255, 0.8);
  position: relative;
  overflow: hidden;
}

/* Overlay sutil */
.register-form-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 144, 133, 0.07), transparent 65%);
  opacity: 1;
  pointer-events: none;
}

/* Barra/acento lateral */
.register-form-container::after {
  content: '';
  position: absolute;
  left: 0;
  top: 15%;
  width: 4px;
  height: 70%;
  border-radius: 0 999px 999px 0;
  background: linear-gradient(to bottom, var(--emerald-dark), var(--emerald-primary));
}

/* Contenido encima del overlay */
.register-header,
.register-form,
.register-footer {
  position: relative;
  z-index: 1;
}

/* ===========================
   HEADER
   =========================== */
.register-header {
  text-align: center;
  margin-bottom: 2.2rem;
}

.register-header h1 {
  font-size: 2rem;
  color: #12222b;
  margin-bottom: 0.5rem;
  font-weight: 700;
  letter-spacing: 0.03em;
}

.register-header p {
  color: #6d7a86;
  font-size: 0.98rem;
}

/* ===========================
   FORM
   =========================== */
.register-form {
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.4rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.45rem;
  color: var(--neutral-dark);
  font-weight: 600;
  font-size: 0.95rem;
}

/* INPUTS, SELECT, TEXTAREA */
.form-input {
  width: 100%;
  padding: 0.9rem 1.1rem;
  border-radius: 12px;
  border: 1px solid #dde3ea;
  font-size: 0.96rem;
  background: #fdfefe;
  transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
  box-sizing: border-box;
  resize: vertical;
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
   MENSAJES DE ESTADO
   =========================== */
.error-message {
  background: #fff5f2;
  border: 1px solid #f3c3b4;
  color: #953125;
  padding: 0.7rem 1rem;
  border-radius: 12px;
  margin-bottom: 0.9rem;
  font-size: 0.9rem;
}

.success-message {
  background: #e6f7ef;
  border: 1px solid #b9e3cc;
  color: #226644;
  padding: 0.7rem 1rem;
  border-radius: 12px;
  margin-bottom: 0.9rem;
  font-size: 0.9rem;
}

/* ===========================
   BOTONES (match Home/Login)
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
.register-footer {
  text-align: center;
}

.register-footer p {
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
  .register {
    padding: 2.5rem 1.2rem;
  }

  .register-form-container {
    padding: 2.3rem 1.8rem;
  }

  .register-header h1 {
    font-size: 1.8rem;
  }
}
</style>
