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
    const updateData: any = {
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

/* HEADER */
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

/* Botón volver */
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

/* LAYOUT */
.profile-container {
  display: grid;
  grid-template-columns: 310px minmax(0, 1fr);
  gap: 2rem;
  align-items: flex-start;
}

/* PANEL IZQUIERDO */
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

.especialidad {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--emerald-dark);
  margin-bottom: 0.15rem;
}

.email {
  opacity: 0.8;
  font-size: 0.83rem;
  margin: 0.1rem 0 0.35rem;
  color: #6d7a86;
}

/* Stats maestro */
.stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.6rem;
}

.stat-item {
  padding: 0.6rem 0.3rem;
  border-radius: 14px;
  background: #f6f9f8;
}

.stat-item .number {
  display: block;
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 0.15rem;
  color: var(--emerald-dark);
}

.stat-item .label {
  font-size: 0.75rem;
  color: #6d7a86;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

/* FORMULARIO (DERECHA) */
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
.form-group textarea,
.form-group select {
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
  min-height: 90px;
}

/* Focus */
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.16);
  background: #ffffff;
}

/* Hint */
.hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #8092a0;
  font-style: italic;
}

/* ACCIONES */
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

/* RESPONSIVE */
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
