button<template>
  <div class="curso-contenido-bg">
    <div class="container">
      <div class="header">
        <router-link to="/estudiante/mis-cursos" class="btn btn-back">← Volver a Mis Cursos</router-link>
        <h1>Contenido del Curso</h1>
      </div>

      <div v-if="loading" class="loading">Cargando contenido...</div>
      <div v-else-if="error" class="error">{{ error }}</div>

      <div v-else>
        <div v-if="estructura.length === 0" class="empty">
          Este curso aún no tiene módulos ni lecciones.
        </div>

        <div v-else>
          <!-- BARRA DE PROGRESO -->
          <div class="progress-bar-container" v-if="estructura.length > 0">
            <div class="progress-label">Progreso: {{ porcentaje }}%</div>
            <div class="progress-bar-bg">
              <div class="progress-bar-fill" :style="{ width: porcentaje + '%' }"></div>
            </div>
          </div>

          <!-- BOTÓN COMPLETAR CURSO -->
          <div v-if="porcentaje === 100" style="text-align:center; margin-bottom:1.5rem;">
            <button
              @click="completarCurso"
              style="
                background:#3a6f66;
                color:white;
                padding:0.7rem 1.5rem;
                border-radius:10px;
                font-weight:600;
                border:none;
                cursor:pointer;
                box-shadow:0 4px 10px rgba(0,0,0,0.15);
              "
            >
              ✔ Completar Curso
            </button>
          </div>

          <!-- LISTA DE MÓDULOS -->
          <div v-for="(modulo, mIdx) in estructura" :key="mIdx" class="modulo-card">
            <h2>{{ modulo.nombre }}</h2>
            <ul>
              <li v-for="(leccion, lIdx) in modulo.lecciones" :key="lIdx">
                <label class="leccion-checkbox">
                  <input
                    type="checkbox"
                    :value="leccionId(mIdx, lIdx)"
                    v-model="leccionesCompletadas"
                    @change="guardarProgreso"
                  />
                  <span>{{ leccion.nombre }}</span>
                </label>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useStudentStore, useEnrollmentStore, useProgressStore } from '@/stores'

const route = useRoute()
const cursoId = Number(route.params.id)

const estructura = ref<any[]>([])
const loading = ref(true)
const error = ref('')

const progressStore = useProgressStore()

const leccionesCompletadas = ref<number[]>([])
const porcentaje = ref(0)
let inscripcionId: number | null = null

// ID único por lección
const leccionId = (mIdx: number, lIdx: number) => mIdx * 1000 + lIdx

// Obtener inscripción real
const cargarInscripcionReal = async () => {
  const token = localStorage.getItem('edulink_token')

  const res = await fetch(`http://localhost:8000/api/inscripciones/curso/${cursoId}`, {
    headers: { Authorization: `Bearer ${token}` }
  })

  const data = await res.json()
  inscripcionId = data.inscripcion ? data.inscripcion.id : null
}

const calcularPorcentaje = () => {
  const total = estructura.value.reduce(
    (acc, m) => acc + (m.lecciones?.length || 0),
    0
  )
  if (total === 0) return 0
  return Math.round((leccionesCompletadas.value.length / total) * 100)
}

const cargarEstructura = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch(`http://localhost:8000/api/cursos/${cursoId}/estructura`)
    if (!res.ok) throw new Error('No se pudo cargar la estructura')
    const data = await res.json()
    estructura.value = data.estructura || []
  } catch (e: any) {
    error.value = e.message || 'Error desconocido'
  } finally {
    loading.value = false
  }
}

// Obtener progreso guardado
const cargarProgreso = async () => {
  if (!inscripcionId) return

  await progressStore.fetchProgress(inscripcionId)

  if (progressStore.progress) {
    leccionesCompletadas.value = progressStore.progress.lecciones_completadas || []
  }

  porcentaje.value = calcularPorcentaje()
}

// 🔥 CORREGIDO: Enviar payload correcto
const guardarProgreso = async () => {
  if (!inscripcionId) return

  const payload = {
    lecciones_completadas: [...leccionesCompletadas.value],
    porcentaje: porcentaje.value
  }

  console.log("ENVIANDO PROGRESO A API:", payload)

  await progressStore.updateProgress(inscripcionId, payload)
}

watch(leccionesCompletadas, () => {
  porcentaje.value = calcularPorcentaje()
})

// Descargar certificado
const completarCurso = async () => {
  if (!inscripcionId) {
    alert("Error: No se encontró la inscripción del curso.")
    return
  }

  try {
    const token = localStorage.getItem('edulink_token')

    const response = await fetch(
      `http://localhost:8000/api/certificados/${inscripcionId}/descargar`,
      { 
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    )

    if (!response.ok) {
      alert("No se pudo generar el certificado.")
      return
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)

    const a = document.createElement("a")
    a.href = url
    a.download = "certificado.pdf"
    a.click()

    window.URL.revokeObjectURL(url)

    alert("🎉 ¡Curso completado! Tu certificado ha sido descargado.")

  } catch (error) {
    console.error(error)
    alert("Ocurrió un error al generar el certificado.")
  }
}

onMounted(async () => {
  await cargarInscripcionReal()
  await cargarEstructura()
  await cargarProgreso()
})
</script>


<style scoped>
.curso-contenido-bg {
  min-height: 100vh;
  background: radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
  padding: 3rem 0 3.5rem;
}
.container {
  max-width: 700px;
  margin: 0 auto;
  padding: 0 2rem;
}
.header {
  text-align: center;
  margin-bottom: 2.4rem;
}
.header h1 {
  font-size: 2rem;
  color: #12222b;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
}
.btn-back {
  display: inline-block;
  margin-bottom: 1.2rem;
  background: #dde6ec;
  color: #23313f;
  border-radius: 999px;
  padding: 0.5rem 1.2rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  transition: background 0.2s;
}
.btn-back:hover {
  background: #b9d8d2;
}
.modulo-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(15, 35, 34, 0.12);
  border: 1px solid #a3d8c3;
  padding: 1.2rem 1.2rem 1rem;
  margin-bottom: 1.2rem;
}
.modulo-card h2 {
  color: #3a6f66;
  font-size: 1.2rem;
  margin-bottom: 0.7rem;
}
.empty {
  color: #888;
  text-align: center;
  margin-top: 2rem;
}
.progress-bar-container {
  margin-bottom: 1.5rem;
}
.progress-label {
  font-weight: 600;
  margin-bottom: 0.3rem;
}
.progress-bar-bg {
  width: 100%;
  height: 18px;
  background: #e0f2f1;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(58, 111, 102, 0.08);
}
.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #3a6f66 0%, #7ed6b7 100%);
  border-radius: 10px;
  transition: width 0.3s;
}
.leccion-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
}
</style>
