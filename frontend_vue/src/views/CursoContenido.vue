<template>
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

const studentStore = useStudentStore()
const enrollmentStore = useEnrollmentStore()
const progressStore = useProgressStore()

const leccionesCompletadas = ref<number[]>([])
const porcentaje = ref(0)
let inscripcionId: number | null = null

// ID único para cada lección
const leccionId = (mIdx: number, lIdx: number) => mIdx * 1000 + lIdx

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

const cargarInscripcionYProgreso = async () => {
  await enrollmentStore.fetchEnrollments()

  const student = studentStore.student
  if (!student) return

  const inscripcion = enrollmentStore.enrollments.find(
    e => e.curso_id === cursoId && e.estudiante_id === student.id
  )

  if (!inscripcion) return

  inscripcionId = inscripcion.id

  await progressStore.fetchProgress(inscripcionId)

  if (progressStore.progress) {
    leccionesCompletadas.value = progressStore.progress.lecciones_completadas || []
  } else {
    leccionesCompletadas.value = []
  }
  porcentaje.value = calcularPorcentaje()
}

const guardarProgreso = async () => {
  if (!inscripcionId) return
  // La barra ya se actualiza por el watcher, solo guardar en backend
  try {
    await progressStore.updateProgress(
      inscripcionId,
      leccionesCompletadas.value,
      porcentaje.value
    )
  } catch (e) {
    // Si falla, recargar progreso desde backend
    await cargarInscripcionYProgreso()
  }
}

// Hacer la barra reactiva: recalcula porcentaje cada vez que cambian los checkboxes
watch(leccionesCompletadas, () => {
  porcentaje.value = calcularPorcentaje()
})


// NUEVA FUNCIÓN
const completarCurso = () => {
  alert("Curso completado 🎉 (Aquí puedes generar el certificado o cambiar el estado)")
}

onMounted(async () => {
  await cargarEstructura()
  await cargarInscripcionYProgreso()
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
