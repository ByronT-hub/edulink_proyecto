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
                <label for="precio">Precio (USD) *</label>
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
.crear-curso {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 800px;
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

.form-container {
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
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
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>
