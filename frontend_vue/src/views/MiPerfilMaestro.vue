<template>
  <div class="mi-perfil-maestro">
    <div class="container">
      <div class="header">
        <h1>👤 Mi Perfil</h1>
        <p>Actualiza tu información profesional para atraer más estudiantes</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <div class="profile-container">
        <!-- Profile summary -->
        <div class="profile-summary">
          <div class="avatar-section">
            <div class="avatar-large">
              {{ user?.nombre?.charAt(0).toUpperCase() || 'M' }}
            </div>
            <h2>{{ user?.nombre }}</h2>
            <p class="especialidad">{{ user?.especialidad || 'Especialidad no definida' }}</p>
            <p class="email">{{ user?.correo }}</p>
          </div>
          
          <div class="stats">
            <div class="stat-item">
              <span class="number">{{ stats.cursos }}</span>
              <span class="label">Cursos</span>
            </div>
            <div class="stat-item">
              <span class="number">{{ stats.estudiantes }}</span>
              <span class="label">Estudiantes</span>
            </div>
            <div class="stat-item">
              <span class="number">{{ stats.calificacion.toFixed(1) }}</span>
              <span class="label">⭐ Rating</span>
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
            </div>

            <div class="form-section">
              <h3>🎓 Información Profesional</h3>
              
              <div class="form-group">
                <label for="especialidad">Especialidad *</label>
                <input 
                  type="text" 
                  id="especialidad"
                  v-model="form.especialidad"
                  placeholder="Ej: Programación Web, Diseño Gráfico, Marketing Digital"
                  required
                >
              </div>

              <div class="form-group">
                <label for="biografia">Biografía Profesional</label>
                <textarea 
                  id="biografia"
                  v-model="form.biografia"
                  placeholder="Cuéntanos sobre tu experiencia, logros y por qué eres un gran maestro..."
                  rows="6"
                ></textarea>
                <small class="hint">Esta información aparecerá en tus cursos y ayudará a los estudiantes a conocerte mejor.</small>
              </div>

              <div class="form-group">
                <label for="experiencia">Años de Experiencia</label>
                <select id="experiencia" v-model="form.experiencia">
                  <option value="">Seleccionar</option>
                  <option value="1-2">1-2 años</option>
                  <option value="3-5">3-5 años</option>
                  <option value="6-10">6-10 años</option>
                  <option value="10+">Más de 10 años</option>
                </select>
              </div>

              <div class="form-group">
                <label for="certificaciones">Certificaciones</label>
                <input 
                  type="text" 
                  id="certificaciones"
                  v-model="form.certificaciones"
                  placeholder="Ej: AWS Certified, Google Analytics, Adobe Certified Expert"
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

const user = computed(() => authStore.user)

const stats = ref({
  cursos: 0,
  estudiantes: 0,
  calificacion: 5.0
})

const form = reactive({
  nombre: '',
  correo: '',
  telefono: '',
  especialidad: '',
  biografia: '',
  experiencia: '',
  certificaciones: '',
  nueva_contrasena: '',
  confirmar_contrasena: ''
})

const cargarDatosPerfil = () => {
  if (user.value) {
    form.nombre = user.value.nombre || ''
    form.correo = user.value.correo || ''
    form.telefono = user.value.telefono || ''
    form.especialidad = user.value.especialidad || ''
    form.biografia = user.value.biografia || ''
    // Los campos de contraseña se mantienen vacíos
  }
}

const cargarEstadisticas = async () => {
  try {
    // Simular carga de estadísticas - después conectar con backend real
    await new Promise(resolve => setTimeout(resolve, 500))
    
    stats.value = {
      cursos: 3,
      estudiantes: 45,
      calificacion: 4.8
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}

const actualizarPerfil = async () => {
  // Validar contraseñas si se están cambiando
  if (form.nueva_contrasena && form.nueva_contrasena !== form.confirmar_contrasena) {
    alert('❌ Las contraseñas no coinciden')
    return
  }

  loading.value = true

  try {
    const updateData = {
      nombre: form.nombre,
      correo: form.correo,
      telefono: form.telefono,
      especialidad: form.especialidad,
      biografia: form.biografia,
      experiencia: form.experiencia,
      certificaciones: form.certificaciones
    }

    // Solo incluir contraseña si se está cambiando
    if (form.nueva_contrasena) {
      updateData.contrasena = form.nueva_contrasena
    }

    const response = await fetch(`http://localhost:8000/api/maestros/${user.value?.id}/perfil`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(updateData)
    })

    if (response.ok) {
      const updatedUser = await response.json()
      
      // Actualizar datos en el store
      authStore.user = { ...authStore.user, ...updatedUser.user }
      localStorage.setItem('edulink_user', JSON.stringify(authStore.user))
      
      // Limpiar campos de contraseña
      form.nueva_contrasena = ''
      form.confirmar_contrasena = ''
      
      alert('✅ ¡Perfil actualizado exitosamente!')
    } else {
      const error = await response.json()
      alert(`❌ Error: ${error.message || 'No se pudo actualizar el perfil'}`)
    }
  } catch (error) {
    console.error('Error:', error)
    alert('❌ Error al actualizar el perfil')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  cargarDatosPerfil()
  form.nueva_contrasena = ''
  form.confirmar_contrasena = ''
}

onMounted(() => {
  if (user.value?.role !== 'maestro') {
    router.push('/dashboard')
    return
  }
  
  cargarDatosPerfil()
  cargarEstadisticas()
})
</script>

<style scoped>
.mi-perfil-maestro {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header {
  text-align: center;
  margin-bottom: 3rem;
}

.header h1 {
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.header p {
  color: #718096;
  margin-bottom: 1rem;
}

.btn-back {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #e2e8f0;
  color: #2d3748;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.btn-back:hover {
  background: #cbd5e0;
}

.profile-container {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 3rem;
  align-items: start;
}

.profile-summary {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 2rem;
}

.avatar-section {
  text-align: center;
  margin-bottom: 2rem;
}

.avatar-large {
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: 600;
  margin: 0 auto 1rem;
}

.avatar-section h2 {
  color: #2d3748;
  margin: 0 0 0.5rem;
}

.especialidad {
  color: #667eea;
  font-weight: 600;
  margin: 0 0 0.5rem;
}

.email {
  color: #718096;
  margin: 0;
  font-size: 0.875rem;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.stat-item {
  text-align: center;
}

.stat-item .number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
}

.stat-item .label {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.edit-form {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.form-section h3 {
  color: #2d3748;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e2e8f0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2d3748;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.hint {
  color: #718096;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #3182ce;
}

.btn-secondary {
  background: #e2e8f0;
  color: #2d3748;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

@media (max-width: 768px) {
  .profile-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .profile-summary {
    position: static;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .stats {
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
  }
}
</style>