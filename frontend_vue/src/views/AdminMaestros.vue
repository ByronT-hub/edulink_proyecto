<template>
  <div class="admin-maestros">
    <div class="container">
      <!-- Header -->
      <div class="header">
        <button class="btn btn-outline volver-btn" @click="router.push('/dashboard')" style="margin-bottom: 1.2rem; float:left;">
          ← Volver
        </button>
        <h1>👨‍🏫 Gestión de Maestros</h1>
        <p>Administra los maestros registrados en la plataforma EduLink</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Cargando maestros...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="state-wrapper">
        <div class="state-card error-state">
          <h2>❌ Error al cargar maestros</h2>
          <p>{{ error }}</p>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="maestros.length === 0" class="state-wrapper">
        <div class="state-card empty-state">
          <h2>🙈 No hay maestros registrados</h2>
          <p>Cuando registres maestros, aparecerán listados en esta vista.</p>
        </div>
      </div>

      <!-- Grid de maestros -->
      <div v-else class="maestros-grid">
        <div
          v-for="maestro in maestros"
          :key="maestro.id"
          class="maestro-card"
        >
          <div class="maestro-header">
            <div class="avatar">
              {{ maestro.nombre ? maestro.nombre.charAt(0).toUpperCase() : '?' }}
            </div>
            <div class="info">
              <h3>{{ maestro.nombre }}</h3>
              <p class="email">📧 {{ maestro.correo }}</p>
              <p v-if="maestro.telefono" class="telefono">📱 {{ maestro.telefono }}</p>
            </div>
          </div>

          <div class="maestro-details">
            <div v-if="maestro.especialidad" class="detail">
              <span class="label">🏅 Especialidad:</span>
              <span class="value">{{ maestro.especialidad }}</span>
            </div>
            <div v-if="maestro.biografia" class="detail">
              <span class="label">📝 Bio:</span>
              <span class="value">{{ maestro.biografia }}</span>
            </div>
          </div>

          <div class="maestro-actions">
            <button class="btn btn-primary" @click="verPerfil(maestro)">
              👁 Ver Perfil
            </button>
            <button class="btn btn-secondary" @click="editarMaestro(maestro)">
              ✏️ Editar
            </button>
            <button class="btn btn-danger" @click="eliminarMaestro(maestro)">
              🗑 Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de perfil -->
    <div v-if="showProfileModal" class="modal-overlay" @click="cerrarModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>👁 Resumen del Maestro</h3>
          <button class="close-btn" @click="cerrarModal">×</button>
        </div>
        <div class="modal-body profile-modal-body">
          <div
            v-for="(valor, clave) in profileData"
            :key="clave"
            class="form-group inline-row"
          >
            <span class="field-label">{{ formatearClave(clave) }}:</span>
            <span class="field-value">{{ valor }}</span>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="cerrarModal">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- Modal de edición -->
    <div v-if="showEditModal" class="modal-overlay" @click="cerrarModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>✏️ Editar Maestro</h3>
          <button class="close-btn" @click="cerrarModal">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre:</label>
            <input v-model="editForm.nombre" type="text" />
          </div>
          <div class="form-group">
            <label>Correo:</label>
            <input v-model="editForm.correo" type="email" />
          </div>
          <div class="form-group">
            <label>Especialidad:</label>
            <input v-model="editForm.especialidad" type="text" />

          </div>
          <div class="form-group">
            <label>Teléfono:</label>
            <input v-model="editForm.telefono" type="text" />
          </div>
          <div class="form-group">
            <label>Biografía:</label>
            <textarea v-model="editForm.biografia" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
          <button class="btn btn-primary" @click="guardarEdicion">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const maestros = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const showEditModal = ref(false)
const showProfileModal = ref(false)
const profileData = reactive<any>({})
const editForm = reactive({
  id: null as number | null,
  nombre: '',
  correo: '',
  especialidad: '',
  telefono: '',
  biografia: ''
})

const cargarMaestros = async () => {
  loading.value = true
  error.value = null
  const token = localStorage.getItem('edulink_token')
  try {
    const res = await axios.get('/api/admin/maestros', {
      headers: { Authorization: `Bearer ${token}` }
    })
    maestros.value = res.data
  } catch (e: any) {
    maestros.value = []
    error.value =
      e?.response?.data?.message ||
      e?.message ||
      'Error desconocido al cargar maestros.'
  } finally {
    loading.value = false
  }
}

onMounted(cargarMaestros)

function verPerfil(maestro: any) {
  Object.keys(profileData).forEach(k => delete profileData[k])
  for (const key in maestro) {
    profileData[key] = maestro[key]
  }
  showProfileModal.value = true
}

function formatearClave(clave: string) {
  const map: Record<string, string> = {
    nombre: 'Nombre',
    correo: 'Correo',
    especialidad: 'Especialidad',
    telefono: 'Teléfono',
    biografia: 'Biografía',
    id: 'ID',
    created_at: 'Fecha de registro',
    updated_at: 'Última actualización'
  }
  return map[clave] || clave.charAt(0).toUpperCase() + clave.slice(1)
}

function editarMaestro(maestro: any) {
  editForm.id = maestro.id
  editForm.nombre = maestro.nombre
  editForm.correo = maestro.correo
  editForm.especialidad = maestro.especialidad || ''
  editForm.telefono = maestro.telefono || ''
  editForm.biografia = maestro.biografia || ''
  showEditModal.value = true
}

function cerrarModal() {
  showEditModal.value = false
  showProfileModal.value = false
  editForm.id = null
  editForm.nombre = ''
  editForm.correo = ''
  editForm.especialidad = ''
  editForm.telefono = ''
  editForm.biografia = ''
  profileData.nombre = ''
  profileData.correo = ''
  profileData.especialidad = ''
  profileData.telefono = ''
  profileData.biografia = ''
}

async function guardarEdicion() {
  const token = localStorage.getItem('edulink_token')
  try {
    await axios.put(
      `/api/admin/maestros/${editForm.id}`,
      {
        nombre: editForm.nombre,
        correo: editForm.correo,
        especialidad: editForm.especialidad,
        telefono: editForm.telefono,
        biografia: editForm.biografia
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    )
    await cargarMaestros()
    cerrarModal()
  } catch (e) {
    alert('Error al guardar cambios')
  }
}

async function eliminarMaestro(maestro: any) {
  if (!confirm(`¿Seguro que deseas eliminar a ${maestro.nombre}?`)) return
  const token = localStorage.getItem('edulink_token')
  try {
    await axios.delete(`/api/admin/maestros/${maestro.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    await cargarMaestros()
  } catch (e) {
    alert('Error al eliminar maestro')
  }
}
</script>

<style scoped>
.admin-maestros {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  min-height: 100vh;
  background: radial-gradient(
    circle at top left,
    #eaf6f3 0,
    #d7ece6 40%,
    #c7e2dc 75%,
    #b9d8d2 100%
  );
  padding: 3rem 0 3.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* HEADER */
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

/* LOADING */
.loading {
  text-align: center;
  color: #1f2e3a;
  padding: 3rem 0;
}

.spinner {
  width: 38px;
  height: 38px;
  border-radius: 999px;
  border: 3px solid rgba(63, 109, 99, 0.24);
  border-top-color: var(--emerald-primary);
  margin: 0 auto 1rem;
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* EMPTY / ERROR STATES */
.state-wrapper {
  display: flex;
  justify-content: center;
  padding: 3rem 0;
}

.state-card {
  max-width: 520px;
  width: 100%;
  padding: 2rem;
  border-radius: 20px;
  backdrop-filter: blur(18px);
  box-shadow: 0 18px 40px rgba(10, 28, 24, 0.18);
  border: 1px solid rgba(163, 216, 195, 0.9);
}

.empty-state {
  background: rgba(249, 252, 251, 0.96);
  color: #12222b;
}

.error-state {
  background: #fff5f5;
  border-color: #feb2b2;
  color: #9b2c2c;
}

.state-card h2 {
  margin-bottom: 0.75rem;
}

.state-card p {
  margin: 0;
  font-size: 0.95rem;
}

/* GRID */
.maestros-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 2rem;
}

/* CARD */
.maestro-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 18px;
  box-shadow: 0 18px 40px rgba(10, 28, 24, 0.18);
  border: 1px solid rgba(163, 216, 195, 0.7);
  padding: 1.7rem 1.4rem 1.2rem;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  transition: transform 0.18s, box-shadow 0.18s, border-color 0.18s;
}

.maestro-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 60px rgba(10, 28, 24, 0.25);
  border-color: rgba(115, 182, 163, 0.9);
}

.maestro-header {
  display: flex;
  align-items: flex-start;
  gap: 1.1rem;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--emerald-dark);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.7rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(79, 144, 133, 0.12);
}

.info h3 {
  margin: 0 0 0.2rem;
  color: #12222b;
  font-size: 1.15rem;
}

.email,
.telefono {
  color: #60707e;
  font-size: 0.92rem;
  margin-bottom: 0.1rem;
}

/* DETAILS */
.maestro-details {
  margin-bottom: 0.5rem;
}

.detail {
  font-size: 0.93rem;
  color: #32414d;
  margin-bottom: 0.2rem;
}

.label {
  font-weight: 600;
  color: #3a6f66;
  margin-right: 0.3rem;
}

.value {
  color: #455463;
}

/* ACTIONS */
.maestro-actions {
  display: flex;
  gap: 0.7rem;
}

.btn {
  flex: 1;
  padding: 0.6rem 1.2rem;
  border-radius: 999px;
  border: none;
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  text-decoration: none;
  transition:
    transform 0.18s,
    box-shadow 0.18s,
    background 0.18s,
    color 0.18s,
    border-color 0.18s;
}

.btn-primary {
  background: var(--emerald-dark);
  color: #fff;
  box-shadow: 0 8px 22px rgba(8, 32, 26, 0.18);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
}

.btn-secondary {
  background: #fff;
  border: 1.5px solid var(--emerald-dark);
  color: var(--emerald-dark);
}

.btn-secondary:hover {
  background: var(--emerald-soft);
}

.btn-danger {
  background: #e05252;
  color: #fff;
  border: none;
}

.btn-danger:hover {
  background: #b02e2e;
}

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8, 24, 21, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(6px);
}

.modal {
  background: #ffffff;
  border-radius: 18px;
  max-width: 460px;
  width: 100%;
  box-shadow: 0 26px 70px rgba(10, 28, 24, 0.35);
  padding: 1.7rem 1.4rem 1.2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modal-header h3 {
  margin: 0;
  color: #12222b;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #60707e;
  cursor: pointer;
}

.modal-body {
  max-height: 340px;
  overflow-y: auto;
  padding-right: 0.25rem;
}

.profile-modal-body .form-group {
  padding: 0.45rem 0;
  border-bottom: 1px solid #edf2f7;
}

.inline-row {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  font-size: 0.92rem;
}

.field-label {
  font-weight: 600;
  color: #3a6f66;
}

.field-value {
  color: #455463;
  text-align: right;
}

/* Form campos edición */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  font-weight: 600;
  color: #3a6f66;
  margin-bottom: 0.2rem;
  display: block;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.5rem 0.65rem;
  border-radius: 8px;
  border: 1px solid #c7e2dc;
  font-size: 0.96rem;
  margin-top: 0.2rem;
  outline: none;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 1px rgba(79, 144, 133, 0.25);
}

.modal-actions {
  display: flex;
  gap: 0.7rem;
  justify-content: flex-end;
  margin-top: 1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .maestros-grid {
    grid-template-columns: 1fr;
  }

  .modal {
    max-width: 92%;
  }
}
</style>
