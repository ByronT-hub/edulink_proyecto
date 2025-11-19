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
.mi-perfil-estudiante {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
  color: white;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.header p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin-bottom: 1rem;
}

.btn-back {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  border-radius: 25px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.profile-container {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 2rem;
  align-items: start;
}

.profile-summary {
  background: rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  color: white;
  text-align: center;
  position: sticky;
  top: 2rem;
}

.avatar-section {
  margin-bottom: 2rem;
}

.avatar-large {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(45deg, #4299e1, #3182ce);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0 auto 1rem;
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.avatar-section h2 {
  margin: 0 0 0.5rem;
  font-size: 1.5rem;
}

.carrera {
  color: #a8d8ff;
  margin: 0 0 0.5rem;
  font-weight: 600;
}

.email {
  opacity: 0.8;
  font-size: 0.9rem;
  margin: 0;
}

.stats {
  display: flex;
  justify-content: space-between;
  text-align: center;
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-item .number {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 0.25rem;
}

.stat-item .label {
  font-size: 0.8rem;
  opacity: 0.8;
}

.edit-form {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  position: relative;
}

.form-section {
  margin-bottom: 2.5rem;
}

.form-section h3 {
  color: #2d3748;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e2e8f0;
  font-size: 1.3rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2d3748;
  font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-group .hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #718096;
  font-style: italic;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 2rem;
  border-top: 2px solid #e2e8f0;
}

.btn {
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(45deg, #4299e1, #3182ce);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
}

.btn-secondary {
  background: #e2e8f0;
  color: #2d3748;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  z-index: 10;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #4299e1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.message {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  font-weight: 600;
}

.message.success {
  background: linear-gradient(45deg, #c6f6d5, #9ae6b4);
  color: #2f855a;
  border: 2px solid #68d391;
}

.message.error {
  background: linear-gradient(45deg, #fed7d7, #feb2b2);
  color: #c53030;
  border: 2px solid #f56565;
}

@media (max-width: 768px) {
  .profile-container {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .profile-summary {
    position: static;
    margin-bottom: 1rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .stats {
    gap: 1rem;
  }
}
</style>