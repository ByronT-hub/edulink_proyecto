<template>
  <div class="buscar-estudiantes">
    <div class="container">
      <div class="header">
        <h1>👨‍🎓 Buscar Estudiantes</h1>
        <p>Encuentra y conecta con estudiantes interesados en tus cursos</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <!-- Search bar -->
      <div class="search-container">
        <div class="search-box">
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Buscar por nombre, correo o curso de interés..."
            @input="buscarEstudiantes"
            class="search-input"
          >
          <button class="search-btn" @click="buscarEstudiantes">
            🔍 Buscar
          </button>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="loading">
        <p>Buscando estudiantes...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="estudiantes.length === 0 && searchQuery" class="empty-state">
        <div class="empty-content">
          <h2>🔍 No se encontraron estudiantes</h2>
          <p>Intenta con otros términos de búsqueda o revisa la ortografía</p>
          <button class="btn btn-secondary" @click="limpiarBusqueda">Ver todos los estudiantes</button>
        </div>
      </div>

      <!-- Initial state - muestra estudiantes directamente -->
      <div v-else-if="estudiantes.length === 0" class="initial-state">
        <div class="initial-content">
          <h2>👥 Estudiantes Registrados</h2>
          <p>Aquí puedes ver todos los estudiantes de la plataforma y contactar con ellos</p>
        </div>
      </div>

      <!-- Students list -->
      <div v-else class="estudiantes-grid">
        <div v-for="estudiante in estudiantes" :key="estudiante.id" class="estudiante-card">
          <div class="estudiante-header">
            <div class="avatar">{{ estudiante.nombre.charAt(0).toUpperCase() }}</div>
            <div class="info">
              <h3>{{ estudiante.nombre }}</h3>
              <p class="email">📧 {{ estudiante.correo }}</p>
              <p v-if="estudiante.carnet" class="carnet">🆔 {{ estudiante.carnet }}</p>
              <p v-if="estudiante.telefono" class="telefono">📱 {{ estudiante.telefono }}</p>
            </div>
          </div>
          
          <div class="estudiante-details">
            <div v-if="estudiante.carrera" class="detail">
              <span class="label">🎓 Carrera:</span>
              <span class="value">{{ estudiante.carrera }}</span>
            </div>
            <div v-if="estudiante.universidad" class="detail">
              <span class="label">🏫 Universidad:</span>
              <span class="value">{{ estudiante.universidad }}</span>
            </div>
            <div v-if="estudiante.nivel_estudio" class="detail">
              <span class="label">📚 Nivel:</span>
              <span class="value">{{ estudiante.nivel_estudio }}</span>
            </div>
            <div v-if="estudiante.intereses" class="detail interests">
              <span class="label">💡 Intereses:</span>
              <span class="value">{{ estudiante.intereses }}</span>
            </div>
          </div>
          
          <div class="estudiante-stats">
            <div class="stat">
              <span class="label">📚 Cursos Inscritos:</span>
              <span class="value">{{ estudiante.cursos_count || 0 }}</span>
            </div>
            <div class="stat">
              <span class="label">📅 Miembro desde:</span>
              <span class="value">{{ formatDate(estudiante.created_at) }}</span>
            </div>
          </div>

          <div class="estudiante-actions">
            <button class="btn btn-primary" @click="contactarEstudiante(estudiante)">
              📧 Contactar
            </button>
            <button class="btn btn-secondary" @click="verPerfil(estudiante.id)">
              👤 Ver Perfil
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          class="btn btn-secondary"
          @click="cambiarPagina(currentPage - 1)"
          :disabled="currentPage === 1"
        >
          ← Anterior
        </button>
        
        <span class="page-info">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        
        <button 
          class="btn btn-secondary"
          @click="cambiarPagina(currentPage + 1)"
          :disabled="currentPage === totalPages"
        >
          Siguiente →
        </button>
      </div>
    </div>

    <!-- Contact Modal -->
    <div v-if="showContactModal" class="modal-overlay" @click="cerrarModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>📧 Contactar Estudiante</h3>
          <button class="close-btn" @click="cerrarModal">×</button>
        </div>
        
        <div class="modal-body">
          <p><strong>Estudiante:</strong> {{ estudianteSeleccionado?.nombre }}</p>
          <p><strong>Correo:</strong> {{ estudianteSeleccionado?.correo }}</p>
          
          <div class="form-group">
            <label>Asunto:</label>
            <input type="text" v-model="contactForm.asunto" placeholder="Invitación a mi curso...">
          </div>
          
          <div class="form-group">
            <label>Mensaje:</label>
            <textarea 
              v-model="contactForm.mensaje" 
              placeholder="Hola! Te invito a revisar mi curso..."
              rows="4"
            ></textarea>
          </div>
        </div>
        
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
          <button class="btn btn-primary" @click="enviarContacto">📤 Enviar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

interface Estudiante {
  id: number
  nombre: string
  correo: string
  carnet?: string
  telefono?: string
  carrera?: string
  universidad?: string
  nivel_estudio?: string
  intereses?: string
  cursos_count?: number
  created_at: string
}

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const searchQuery = ref('')
const estudiantes = ref<Estudiante[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const showContactModal = ref(false)
const estudianteSeleccionado = ref<Estudiante | null>(null)

const contactForm = reactive({
  asunto: '',
  mensaje: ''
})

const buscarEstudiantes = async () => {
  loading.value = true
  
  try {
    let url = `http://localhost:8000/api/maestros/estudiantes/buscar?page=${currentPage.value}`
    
    // Solo agregar el parámetro de búsqueda si hay texto
    if (searchQuery.value.trim()) {
      url += `&buscar=${encodeURIComponent(searchQuery.value)}`
    }

    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      estudiantes.value = data.data || []
      totalPages.value = data.last_page || 1
      currentPage.value = data.current_page || 1
    } else {
      console.error('Error al buscar estudiantes')
      estudiantes.value = []
    }
  } catch (error) {
    console.error('Error:', error)
    estudiantes.value = []
  } finally {
    loading.value = false
  }
}

// Cargar todos los estudiantes al iniciar
const cargarTodosLosEstudiantes = async () => {
  loading.value = true
  
  try {
    const response = await fetch(`http://localhost:8000/api/maestros/estudiantes/buscar?page=1`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      estudiantes.value = data.data || []
      totalPages.value = data.last_page || 1
      currentPage.value = 1
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const limpiarBusqueda = () => {
  searchQuery.value = ''
  currentPage.value = 1
  cargarTodosLosEstudiantes()
}

// Llamar al cargar el componente
cargarTodosLosEstudiantes()

const cambiarPagina = (page: number) => {
  currentPage.value = page
  buscarEstudiantes()
}

const contactarEstudiante = (estudiante: Estudiante) => {
  estudianteSeleccionado.value = estudiante
  contactForm.asunto = `Invitación a mis cursos - ${authStore.user?.nombre}`
  contactForm.mensaje = `Hola ${estudiante.nombre}!\n\nSoy ${authStore.user?.nombre}, maestro especializado en ${authStore.user?.especialidad || 'educación online'}.\n\nMe gustaría invitarte a revisar mis cursos, creo que podrían ser de tu interés.\n\n¡Espero que podamos conectar pronto!\n\nSaludos cordiales.`
  showContactModal.value = true
}

const verPerfil = (id: number) => {
  // Por ahora mostrar alerta, después se puede implementar vista de perfil
  alert(`🚧 Próximamente: Ver perfil del estudiante ${id}`)
}

const enviarContacto = async () => {
  if (!contactForm.asunto || !contactForm.mensaje) {
    alert('Por favor completa todos los campos')
    return
  }

  try {
    // Simular envío de contacto (implementar backend después)
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert(`✅ ¡Mensaje enviado a ${estudianteSeleccionado.value?.nombre}!`)
    cerrarModal()
  } catch (error) {
    alert('❌ Error al enviar mensaje')
  }
}

const cerrarModal = () => {
  showContactModal.value = false
  estudianteSeleccionado.value = null
  contactForm.asunto = ''
  contactForm.mensaje = ''
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', { 
    year: 'numeric', 
    month: 'long' 
  })
}
</script>

<style scoped>
.buscar-estudiantes {
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

.search-container {
  max-width: 600px;
  margin: 0 auto 3rem;
}

.search-box {
  display: flex;
  gap: 1rem;
}

.search-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
}

.search-input:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.search-btn {
  padding: 0.75rem 1.5rem;
  background: #4299e1;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.search-btn:hover {
  background: #3182ce;
}

.loading, .empty-state, .initial-state {
  text-align: center;
  padding: 4rem 0;
  color: #718096;
}

.estudiantes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.estudiante-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.estudiante-card:hover {
  transform: translateY(-4px);
}

.estudiante-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #4299e1, #667eea);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 600;
}

.info h3 {
  margin: 0 0 0.25rem;
  color: #2d3748;
}

.email, .carnet, .telefono {
  margin: 0.25rem 0;
  color: #718096;
  font-size: 0.875rem;
}

.email, .telefono {
  font-size: 0.875rem;
  color: #718096;
  margin: 0.25rem 0;
}

.estudiante-details {
  margin: 1rem 0;
}

.detail {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin: 0.5rem 0;
  padding: 0.25rem 0;
  border-bottom: 1px solid #f7fafc;
}

.detail.interests {
  flex-direction: column;
  align-items: flex-start;
}

.detail .label {
  font-weight: 600;
  color: #4a5568;
  font-size: 0.875rem;
  min-width: fit-content;
  margin-right: 0.5rem;
}

.detail .value {
  color: #2d3748;
  font-size: 0.875rem;
  text-align: right;
  flex: 1;
}

.detail.interests .value {
  text-align: left;
  margin-top: 0.25rem;
  font-style: italic;
  color: #718096;
}

.estudiante-stats {
  margin-bottom: 1.5rem;
}

.stat {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.label {
  color: #718096;
  font-size: 0.875rem;
}

.value {
  color: #2d3748;
  font-weight: 600;
  font-size: 0.875rem;
}

.estudiante-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  flex: 1;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover {
  background: #3182ce;
}

.btn-secondary {
  background: #e2e8f0;
  color: #2d3748;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
}

.page-info {
  color: #718096;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #718096;
}

.modal-body .form-group {
  margin-bottom: 1rem;
}

.modal-body label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2d3748;
}

.modal-body input,
.modal-body textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}
</style>