<template>
  <div class="mi-perfil-estudiante">
    <div class="container">
      <div class="header">
        <h1>👤 Mi Perfil</h1>
        <p>Actualiza tu información personal</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <div class="profile-container">
        <!-- Profile summary -->
        <div class="profile-summary">
          <div class="avatar-section">
            <div class="avatar-large">
              {{ user?.nombre?.charAt(0).toUpperCase() || 'E' }}
            </div>
            <h2>{{ user?.nombre }}</h2>
            <p class="email">{{ user?.correo }}</p>
            <p class="carnet" v-if="user?.carnet">Carnet: {{ user?.carnet }}</p>
          </div>
          
          <div class="stats">
            <div class="stat-item">
              <span class="number">{{ stats.cursosInscritos }}</span>
              <span class="label">Cursos Inscritos</span>
            </div>
            <div class="stat-item">
              <span class="number">{{ stats.cursosCompletados }}</span>
              <span class="label">Completados</span>
            </div>
            <div class="stat-item">
              <span class="number">{{ stats.certificados }}</span>
              <span class="label">🏆 Certificados</span>
            </div>
          </div>
        </div>

        <!-- Edit form -->
        <div class="edit-form">
          <form @submit.prevent="actualizarPerfil">
            <div class="form-section">
              <h3>📝 Información Personal</h3>
              
              <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input 
                  type="text" 
                  id="nombre"
                  v-model="form.nombre"
                  required
                >
              </div>

              <div class="form-group">
                <label for="correo">Correo Electrónico *</label>
                <input 
                  type="email" 
                  id="correo"
                  v-model="form.correo"
                  required
                >
              </div>

              <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input 
                  type="tel" 
                  id="telefono"
                  v-model="form.telefono"
                  placeholder="+502 1234-5678"
                >
              </div>

              <div class="form-group">
                <label for="carnet">Carnet Estudiantil</label>
                <input 
                  type="text" 
                  id="carnet"
                  v-model="form.carnet"
                  placeholder="Número de carnet"
                >
              </div>
            </div>

            <div class="form-section">
              <h3>🔐 Seguridad</h3>
              
              <div class="form-group">
                <label for="nueva-contrasena">Nueva Contraseña</label>
                <input 
                  type="password" 
                  id="nueva-contrasena"
                  v-model="form.nueva_contrasena"
                  placeholder="Dejar en blanco si no quieres cambiarla"
                >
              </div>

              <div class="form-group">
                <label for="confirmar-contrasena">Confirmar Contraseña</label>
                <input 
                  type="password" 
                  id="confirmar-contrasena"
                  v-model="form.confirmar_contrasena"
                  placeholder="Confirma la nueva contraseña"
                >
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn-secondary" @click="resetForm">
                🔄 Restaurar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading">⏳ Guardando...</span>
                <span v-else>💾 Guardar Cambios</span>
              </button>
            </div>
          </form>

          <!-- Loading state -->
          <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
            <p>Actualizando perfil...</p>
          </div>

          <!-- Success/Error messages -->
          <div v-if="message" class="message" :class="messageType">
            {{ message }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')

const user = computed(() => authStore.user)

const stats = ref({
  cursosInscritos: 0,
  cursosCompletados: 0,
  certificados: 0
})

const form = reactive({
  nombre: '',
  correo: '',
  carnet: '',
  telefono: '',
  nueva_contrasena: '',
  confirmar_contrasena: ''
})

const cargarDatosPerfil = async () => {
  if (user.value) {
    form.nombre = user.value.nombre || ''
    form.correo = user.value.correo || ''
    form.carnet = user.value.carnet || ''
    form.telefono = user.value.telefono || ''
    // Los campos de contraseña se mantienen vacíos
  }

  // Cargar estadísticas del estudiante
  await cargarEstadisticas()
}

const cargarEstadisticas = async () => {
  if (!authStore.isAuthenticated()) return

  try {
    const response = await fetch('http://localhost:8000/api/mis-cursos', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      stats.value = {
        cursosInscritos: data.total || 0,
        cursosCompletados: data.activos || 0,
        certificados: 0 // Por ahora, se puede implementar después
      }
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}

const actualizarPerfil = async () => {
  // Validaciones
  if (form.nueva_contrasena && form.nueva_contrasena !== form.confirmar_contrasena) {
    mostrarMensaje('Las contraseñas no coinciden', 'error')
    return
  }

  if (form.nueva_contrasena && form.nueva_contrasena.length < 6) {
    mostrarMensaje('La contraseña debe tener al menos 6 caracteres', 'error')
    return
  }

  loading.value = true
  message.value = ''

  try {
    const updateData: any = {
      nombre: form.nombre,
      correo: form.correo,
      carnet: form.carnet,
      telefono: form.telefono
    }

    if (form.nueva_contrasena) {
      updateData.password = form.nueva_contrasena
    }

    const response = await fetch(`http://localhost:8000/api/estudiantes/${user.value?.id}/perfil`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(updateData)
    })

    const data = await response.json()

    if (response.ok) {
      // Actualizar datos en el store
      await authStore.getCurrentUser()
      mostrarMensaje('Perfil actualizado exitosamente', 'success')
      
      // Limpiar campos de contraseña
      form.nueva_contrasena = ''
      form.confirmar_contrasena = ''
    } else {
      throw new Error(data.error || 'Error al actualizar perfil')
    }
  } catch (error: any) {
    mostrarMensaje(error.message || 'Error al actualizar perfil', 'error')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  cargarDatosPerfil()
  message.value = ''
}

const mostrarMensaje = (msg: string, tipo: 'success' | 'error') => {
  message.value = msg
  messageType.value = tipo
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

onMounted(() => {
  if (!authStore.isAuthenticated() || authStore.user?.role !== 'estudiante') {
    router.push('/dashboard')
    return
  }
  cargarDatosPerfil()
})
</script>

<style scoped>
/* ===========================
   PALETA & LAYOUT BASE
   =========================== */
.mi-perfil-estudiante {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;

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
   HEADER
   =========================== */
.header {
  text-align: center;
  margin-bottom: 2.4rem;
  color: #12222b;
}

.header h1 {
  font-size: 2rem;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
}

.header p {
  font-size: 0.95rem;
  opacity: 0.85;
  margin-bottom: 1rem;
}

/* Volver al dashboard */
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.55rem 1.1rem;
  background: rgba(255, 255, 255, 0.9);
  color: var(--emerald-dark);
  text-decoration: none;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  box-shadow:
    0 12px 28px rgba(15, 35, 34, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.7);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease;
}

.btn-back:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
  transform: translateY(-1px);
}

/* ===========================
   LAYOUT PERFIL
   =========================== */
.profile-container {
  display: grid;
  grid-template-columns: 310px minmax(0, 1fr);
  gap: 2rem;
  align-items: flex-start;
}

/* ===========================
   PANEL IZQUIERDO (RESUMEN)
   =========================== */
.profile-summary {
  background: rgba(255, 255, 255, 0.97);
  padding: 1.9rem 1.6rem;
  border-radius: 24px;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  text-align: center;
  position: sticky;
  top: 2rem;
}

.avatar-section {
  margin-bottom: 1.8rem;
}

.avatar-large {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f9085, #6fb4a8);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.3rem;
  font-weight: 700;
  margin: 0 auto 0.9rem;
  box-shadow:
    0 18px 40px rgba(5, 22, 18, 0.7),
    0 0 0 3px rgba(228, 241, 237, 0.9);
}

.avatar-section h2 {
  margin: 0 0 0.2rem;
  font-size: 1.25rem;
  color: #12222b;
}

.email {
  opacity: 0.8;
  font-size: 0.83rem;
  margin: 0.1rem 0 0.35rem;
  color: #6d7a86;
}

.carnet {
  font-size: 0.85rem;
  color: var(--emerald-dark);
  font-weight: 600;
}

/* Stats */
.stats {
  display: flex;
  justify-content: space-between;
  text-align: center;
  gap: 0.6rem;
}

.stat-item {
  flex: 1;
  padding: 0.6rem 0.3rem;
  border-radius: 14px;
  background: #f6f9f8;
}

.stat-item .number {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 0.15rem;
  color: var(--emerald-dark);
}

.stat-item .label {
  font-size: 0.75rem;
  color: #6d7a86;
}

/* ===========================
   FORMULARIO DERECHA
   =========================== */
.edit-form {
  background: rgba(255, 255, 255, 0.97);
  padding: 2.1rem 1.9rem;
  border-radius: 26px;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  position: relative;
}

.form-section {
  margin-bottom: 2.2rem;
}

.form-section h3 {
  color: #12222b;
  margin-bottom: 1.1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid rgba(163, 216, 195, 0.9);
  font-size: 1.05rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.form-group {
  margin-bottom: 1.3rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.35rem;
  color: #23313f;
  font-weight: 600;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.7rem 0.85rem;
  border: 1px solid #dde3ea;
  border-radius: 12px;
  font-size: 0.95rem;
  background: #ffffff;
  color: #2f3c49;
  box-sizing: border-box;
  transition:
    border-color 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9aa7b2;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

/* Focus */
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.16);
  background: #ffffff;
}

/* ===========================
   ACCIONES
   =========================== */
.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1.7rem;
  border-top: 1px solid #e0e6ec;
}

.btn {
  padding: 0.75rem 1.7rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Primary */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 14px 32px rgba(8, 32, 26, 0.55);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 18px 40px rgba(5, 22, 18, 0.65);
}

/* Secondary */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.8);
  box-shadow: 0 10px 24px rgba(15, 35, 34, 0.18);
}

.btn-secondary:hover {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* ===========================
   LOADING OVERLAY
   =========================== */
.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(246, 248, 250, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 26px;
  z-index: 10;
}

.spinner {
  width: 38px;
  height: 38px;
  border: 3px solid #dde3ea;
  border-top: 3px solid var(--emerald-primary);
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
  margin-bottom: 0.8rem;
}

.loading-overlay p {
  color: #23313f;
  font-size: 0.9rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ===========================
   MENSAJES
   =========================== */
.message {
  margin-top: 1.1rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  text-align: center;
  font-weight: 600;
  font-size: 0.9rem;
}

.message.success {
  background: linear-gradient(135deg, #c9f1dd, #a3d8c3);
  color: #1f5f3f;
  border: 1px solid #7bc19e;
}

.message.error {
  background: linear-gradient(135deg, #fde1e1, #f6bcbc);
  color: #8f2626;
  border: 1px solid #f28a8a;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 900px) {
  .profile-container {
    grid-template-columns: 1fr;
  }

  .profile-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .header h1 {
    font-size: 1.8rem;
  }

  .edit-form {
    padding: 1.7rem 1.5rem;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
  }
}
</style>
