<template>
  <div class="crear-curso">
    <div class="container">
      <div class="header">
        <h1>➕ Crear Nuevo Curso</h1>
        <p>Comparte tu conocimiento creando un curso increíble</p>
        <router-link to="/maestro/mis-cursos" class="btn-back">← Volver a Mis Cursos</router-link>
      </div>

      <div class="form-container">
        <form @submit.prevent="crearCurso" class="curso-form">
          <div class="form-section">
            <h3>📖 Información Básica</h3>
            
            <div class="form-group">
              <label for="titulo">Título del Curso *</label>
              <input 
                type="text" 
                id="titulo"
                v-model="form.titulo"
                placeholder="Ej: Programación Web con Vue.js"
                required
              >
            </div>

            <div class="form-group">
              <label for="descripcion">Descripción *</label>
              <textarea 
                id="descripcion"
                v-model="form.descripcion"
                placeholder="Describe qué aprenderán los estudiantes..."
                rows="4"
                required
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="precio">Precio (Q) *</label>
                <input 
                  type="number" 
                  id="precio"
                  v-model="form.precio"
                  placeholder="0.00"
                  min="0"
                  step="0.01"
                  required
                >
              </div>

              <div class="form-group">
                <label for="duracion">Duración (horas) *</label>
                <input 
                  type="number" 
                  id="duracion"
                  v-model="form.duracion"
                  placeholder="0"
                  min="1"
                  required
                >
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3>📚 Contenido del Curso</h3>
            
            <div class="form-group">
              <label for="categoria">Categoría</label>
              <select id="categoria" v-model="form.categoria">
                <option value="">Seleccionar categoría</option>
                <option value="programacion">Programación</option>
                <option value="diseno">Diseño</option>
                <option value="marketing">Marketing</option>
                <option value="negocios">Negocios</option>
                <option value="idiomas">Idiomas</option>
                <option value="musica">Música</option>
                <option value="otros">Otros</option>
              </select>
            </div>

            <div class="form-group">
              <label for="nivel">Nivel</label>
              <select id="nivel" v-model="form.nivel">
                <option value="principiante">Principiante</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
              </select>
            </div>

            <div class="form-group">
              <label for="requisitos">Requisitos Previos</label>
              <textarea 
                id="requisitos"
                v-model="form.requisitos"
                placeholder="Conocimientos básicos necesarios..."
                rows="3"
              ></textarea>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="cancelar">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading">⏳ Creando...</span>
              <span v-else>🚀 Crear Curso</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)

const form = reactive({
  titulo: '',
  descripcion: '',
  precio: 0,
  duracion: 1,
  categoria: '',
  nivel: 'principiante',
  requisitos: ''
})

const crearCurso = async () => {
  loading.value = true

  try {
    const user = authStore.user
    if (!user || user.role !== 'maestro') {
      alert('Solo los maestros pueden crear cursos')
      return
    }

    const response = await fetch('http://localhost:8000/api/maestros/cursos', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form)
    })

    if (response.ok) {
      alert('✅ ¡Curso creado exitosamente!')
      router.push('/maestro/mis-cursos')
    } else {
      const error = await response.json()
      alert(`❌ Error: ${error.message || 'No se pudo crear el curso'}`)
    }
  } catch (error) {
    console.error('Error:', error)
    alert('❌ Error al crear el curso')
  } finally {
    loading.value = false
  }
}

const cancelar = () => {
  if (confirm('¿Estás seguro? Se perderán los datos del formulario')) {
    router.push('/maestro/mis-cursos')
  }
}
</script>

<style scoped>
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.crear-curso {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 20px;

  min-height: calc(100vh - 80px);
  padding: 3rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* ===========================
   HEADER
   =========================== */
.header {
  text-align: center;
  margin-bottom: 2.4rem;
}

.header h1 {
  color: #12222b;
  margin-bottom: 0.5rem;
  font-size: 2rem;
  letter-spacing: 0.03em;
}

.header p {
  color: #6d7a86;
  margin-bottom: 1rem;
  font-size: 0.98rem;
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

/* ===========================
   CONTENEDOR DEL FORMULARIO
   =========================== */
.form-container {
  background: rgba(255, 255, 255, 0.97);
  border-radius: 26px;
  padding: 2.2rem 2rem;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.7);
}

.curso-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* ===========================
   SECCIONES DEL FORM
   =========================== */
.form-section {
  padding: 1.4rem 1.3rem 1.5rem;
  border-radius: 20px;
  background: #f7fafb;
  border: 1px solid rgba(163, 216, 195, 0.6);
  box-shadow: 0 14px 36px rgba(15, 35, 34, 0.16);
}

.form-section h3 {
  color: #12222b;
  margin-bottom: 1.2rem;
  font-size: 1.1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding-bottom: 0.4rem;
  border-bottom: 2px solid rgba(163, 216, 195, 0.8);
}

/* ===========================
   CAMPOS
   =========================== */
.form-group {
  margin-bottom: 1.3rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #23313f;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.7rem 0.8rem;
  border-radius: 12px;
  border: 1px solid #dde3ea;
  font-size: 0.95rem;
  box-sizing: border-box;
  background: #ffffff;
  color: #2f3c49;
  transition:
    border-color 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease;
}

.form-group textarea {
  resize: vertical;
  min-height: 110px;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9aa7b2;
}

/* Focus */
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
  background: #ffffff;
}

/* ===========================
   ACCIONES
   =========================== */
.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 0.5rem;
}

/* Botones base */
.btn {
  padding: 0.8rem 1.6rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
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

/* Primario (CTA crear curso) */
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

/* Secundario (cancelar) */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.7);
  box-shadow: 0 10px 24px rgba(15, 35, 34, 0.18);
}

.btn-secondary:hover {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .form-container {
    padding: 1.7rem 1.4rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
  }

  .header h1 {
    font-size: 1.7rem;
  }
}
</style>
