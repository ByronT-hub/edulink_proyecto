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
  alert(`🚧 Próximamente: Ver perfil del estudiante ${id}`)
}

const enviarContacto = async () => {
  if (!contactForm.asunto || !contactForm.mensaje) {
    alert('Por favor completa todos los campos')
    return
  }

  try {
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
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.buscar-estudiantes {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 18px;

  min-height: 100vh;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  padding: 3rem 0 3.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

/* ===========================
   CONTENEDOR
   =========================== */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* ===========================
   HEADER
   =========================== */
.header {
  text-align: center;
  margin-bottom: 2.8rem;
}

.header h1 {
  color: #12222b;
  margin-bottom: 0.4rem;
  font-size: 2.1rem;
  letter-spacing: 0.03em;
}

.header p {
  color: #6d7a86;
  margin-bottom: 1.2rem;
  font-size: 0.98rem;
}

/* Botón volver */
.btn-back {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  padding: 0.55rem 1.4rem;
  background: rgba(255, 255, 255, 0.82);
  color: var(--emerald-dark);
  text-decoration: none;
  border-radius: 999px;
  border: 1px solid rgba(163, 216, 195, 0.6);
  font-size: 0.88rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  box-shadow: 0 10px 24px rgba(15, 35, 34, 0.18);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    border-color 0.22s ease,
    color 0.22s ease;
}

.btn-back:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
  color: var(--emerald-dark);
  transform: translateY(-1px);
  box-shadow: 0 16px 34px rgba(8, 32, 26, 0.25);
}

/* ===========================
   BUSCADOR
   =========================== */
.search-container {
  max-width: 680px;
  margin: 0 auto 3rem;
}

.search-box {
  display: flex;
  gap: 0.9rem;
  padding: 0.9rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 999px;
  box-shadow:
    0 18px 40px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(163, 216, 195, 0.6);
}

.search-input {
  flex: 1;
  padding: 0.5rem 0.3rem 0.5rem 0.8rem;
  border: none;
  border-radius: 999px;
  font-size: 0.98rem;
  background: transparent;
  color: var(--neutral-dark);
}

.search-input::placeholder {
  color: #9aa7b2;
}

.search-input:focus {
  outline: none;
}

/* Botón buscar */
.search-btn {
  padding: 0.6rem 1.4rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 24px rgba(8, 32, 26, 0.55);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease;
}

.search-btn:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(5, 22, 18, 0.65);
}

/* ===========================
   ESTADOS (loading / empty / initial)
   =========================== */
.loading,
.empty-state,
.initial-state {
  text-align: center;
  padding: 4rem 0;
  color: #6d7a86;
}

.empty-content h2,
.initial-content h2 {
  color: #12222b;
  margin-bottom: 0.6rem;
}

.empty-content p,
.initial-content p {
  max-width: 480px;
  margin: 0 auto 1.4rem;
}

/* ===========================
   GRID ESTUDIANTES
   =========================== */
.estudiantes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

/* CARD ESTUDIANTE */
.estudiante-card {
  background: #ffffff;
  border-radius: var(--border-radius-primary);
  padding: 1.7rem 1.5rem 1.6rem;
  box-shadow:
    0 22px 55px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.5);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
  position: relative;
  overflow: hidden;
}

.estudiante-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 144, 133, 0.06), transparent 65%);
  opacity: 0;
  transition: opacity 0.22s ease;
}

.estudiante-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 65px rgba(8, 32, 26, 0.32);
  border-color: var(--emerald-primary);
}

.estudiante-card:hover::before {
  opacity: 1;
}

.estudiante-header,
.estudiante-details,
.estudiante-stats,
.estudiante-actions {
  position: relative;
  z-index: 1;
}

/* HEADER ESTUDIANTE */
.estudiante-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.4rem;
}

/* Avatar circular con gradient esmeralda */
.avatar {
  width: 54px;
  height: 54px;
  background: conic-gradient(
    from 160deg,
    #4f9085,
    #a3d8c3,
    #4f9085
  );
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  font-weight: 700;
  box-shadow: 0 10px 22px rgba(15, 35, 34, 0.4);
}

.info h3 {
  margin: 0 0 0.2rem;
  color: #12222b;
  font-size: 1.05rem;
}

.email,
.carnet,
.telefono {
  margin: 0.12rem 0;
  color: #6d7a86;
  font-size: 0.85rem;
}

/* DETALLES */
.estudiante-details {
  margin: 0.8rem 0 1.1rem;
}

.detail {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin: 0.35rem 0;
  padding: 0.25rem 0;
  border-bottom: 1px solid #f1f5f7;
}

.detail.interests {
  flex-direction: column;
  align-items: flex-start;
}

.detail .label {
  font-weight: 600;
  color: #4a5a68;
  font-size: 0.84rem;
  min-width: fit-content;
  margin-right: 0.5rem;
}

.detail .value {
  color: #23313f;
  font-size: 0.84rem;
  text-align: right;
  flex: 1;
}

.detail.interests .value {
  text-align: left;
  margin-top: 0.25rem;
  font-style: italic;
  color: #6d7a86;
}

/* STATS */
.estudiante-stats {
  margin-bottom: 1.4rem;
}

.stat {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.4rem;
}

.stat .label {
  color: #7a8a98;
  font-size: 0.84rem;
}

.stat .value {
  color: #23313f;
  font-weight: 600;
  font-size: 0.84rem;
}

/* ===========================
   BOTONES REUTILIZADOS (btn, primary, secondary)
   =========================== */
.btn {
  padding: 0.65rem 1.4rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}

/* Botón principal */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 22px rgba(8, 32, 26, 0.45);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 14px 30px rgba(5, 22, 18, 0.6);
}

/* Botón secundario */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.5);
  box-shadow: 0 6px 16px rgba(15, 35, 34, 0.16);
}

.btn-secondary:hover {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* Acciones dentro de la card */
.estudiante-actions {
  display: flex;
  gap: 0.6rem;
}

/* ===========================
   PAGINACIÓN
   =========================== */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.6rem;
}

.page-info {
  color: #6d7a86;
  font-size: 0.9rem;
}

/* ===========================
   MODAL DE CONTACTO
   =========================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(4, 16, 14, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal {
  background: #ffffff;
  border-radius: 20px;
  padding: 1.7rem 1.5rem;
  max-width: 520px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow:
    0 26px 70px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(163, 216, 195, 0.55);
}

/* Header modal */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.3rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid #e4ebf0;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  color: #12222b;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7a8a98;
  transition: color 0.22s ease, transform 0.22s ease;
}

.close-btn:hover {
  color: var(--emerald-dark);
  transform: scale(1.05);
}

/* Body modal */
.modal-body p {
  margin: 0.3rem 0 0.6rem;
  font-size: 0.92rem;
  color: #23313f;
}

.modal-body .form-group {
  margin-top: 0.9rem;
}

.modal-body label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #23313f;
  font-size: 0.9rem;
}

.modal-body input,
.modal-body textarea {
  width: 100%;
  padding: 0.7rem 0.8rem;
  border: 1px solid #dde3ea;
  border-radius: 12px;
  font-size: 0.9rem;
  resize: vertical;
  box-sizing: border-box;
  background: #fdfefe;
  transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
}

.modal-body input:focus,
.modal-body textarea:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
  background: #ffffff;
}

/* Acciones modal */
.modal-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.6rem;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .buscar-estudiantes {
    padding: 2.3rem 0 2.8rem;
  }

  .header h1 {
    font-size: 1.7rem;
  }

  .search-box {
    flex-direction: column;
    border-radius: 20px;
  }

  .search-btn {
    width: 100%;
  }

  .estudiantes-grid {
    grid-template-columns: 1fr;
  }

  .modal {
    padding: 1.4rem 1.2rem;
  }
}
</style>
