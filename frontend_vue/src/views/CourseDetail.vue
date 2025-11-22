<template>
  <div class="course-detail">
    <div class="container">
      <div v-if="loading" class="loading">
        <p>Cargando curso...</p>
      </div>

      <div v-else-if="!course" class="error">
        <p>Curso no encontrado</p>
        <router-link to="/courses" class="btn btn-primary">
          Volver a Cursos
        </router-link>
      </div>

      <div v-else class="course-content">
        <div class="course-header">
          <div class="course-image">
            <img :src="course.imagen || '/course-placeholder.jpg'" :alt="course.titulo" />
          </div>
          <div class="course-info">
            <h1>{{ course.titulo }}</h1>
            <p class="course-description">{{ course.descripcion }}</p>
            
            <div class="course-meta">
              <div class="meta-item">
                <span class="meta-label">Precio:</span>
                <span class="meta-value">${{ course.precio }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Duración:</span>
                <span class="meta-value">{{ course.duracion_horas }} horas</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Nivel:</span>
                <span class="meta-value">{{ course.nivel }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Categoría:</span>
                <span class="meta-value">{{ course.categoria }}</span>
              </div>
            </div>

            <div class="course-actions">
              <button 
                v-if="!isAuthenticated"
                @click="$router.push('/login')"
                class="btn btn-primary btn-large"
              >
                Inicia Sesión para Inscribirte
              </button>
              
              <button 
                v-else
                @click="goToPayment"
                class="btn btn-primary btn-large"
              >
                Ir al Pago
              </button>
            </div>
          </div>
        </div>

        <div class="course-details">
          <div class="details-section">
            <h2>Descripción del Curso</h2>
            <div class="description-content">
              <p>{{ course.descripcion_larga || course.descripcion }}</p>
            </div>
          </div>

          <div class="details-section">
            <h2>Lo que aprenderás</h2>
            <ul class="learning-objectives">
              <li>Dominio completo de {{ course.titulo }}</li>
              <li>Aplicación práctica de conceptos</li>
              <li>Proyectos del mundo real</li>
              <li>Certificación al completar el curso</li>
            </ul>
          </div>

          <div class="details-section">
            <h2>Requisitos</h2>
            <ul class="requirements">
              <li>Motivación para aprender</li>
              <li>Acceso a internet</li>
              <li>Computadora o dispositivo móvil</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCoursesStore } from '@/stores/courses'
import { useStudentStore } from '@/stores/student'

const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()
const studentStore = useStudentStore()

const courseId = computed(() => parseInt(route.params.id as string))
const course = computed(() => coursesStore.getCourseById(courseId.value))
const loading = computed(() => coursesStore.loading)
const isAuthenticated = computed(() => studentStore.isAuthenticated())

const goToPayment = () => {
  router.push(`/payment/${courseId.value}`)
}

onMounted(async () => {
  if (!course.value) {
    await coursesStore.fetchCourses()
  }
})
</script>

<style scoped>
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.course-detail {
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
  max-width: 1150px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* ===========================
   ESTADOS
   =========================== */
.loading,
.error {
  text-align: center;
  padding: 4rem 0;
  color: #6d7a86;
}

.error p {
  margin-bottom: 1.5rem;
}

/* ===========================
   CONTENIDO PRINCIPAL
   =========================== */
.course-content {
  background: rgba(255, 255, 255, 0.96);
  border-radius: 26px;
  padding: 2.3rem 2.1rem;
  box-shadow:
    0 26px 70px rgba(15, 35, 34, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.6);
}

/* ===========================
   HEADER DEL CURSO
   =========================== */
.course-header {
  display: grid;
  grid-template-columns: minmax(260px, 340px) 1fr;
  gap: 2.2rem;
  align-items: stretch;
  margin-bottom: 2.4rem;
}

/* Imagen del curso */
.course-image {
  border-radius: 22px;
  overflow: hidden;
  background: radial-gradient(circle at top, #c5e3da 0, #7ca79b 50%, #29433c 100%);
  box-shadow: 0 22px 55px rgba(8, 32, 26, 0.4);
  position: relative;
}

.course-image::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at bottom right, rgba(0, 0, 0, 0.08), transparent 65%);
  pointer-events: none;
}

.course-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  mix-blend-mode: normal;
}

/* Info del curso */
.course-info {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.course-info h1 {
  font-size: 2rem;
  color: #12222b;
  margin-bottom: 0.6rem;
  letter-spacing: 0.02em;
}

.course-description {
  color: #6d7a86;
  font-size: 0.98rem;
  line-height: 1.7;
  margin-bottom: 1.6rem;
}

/* ===========================
   META (precio, nivel, etc)
   =========================== */
.course-meta {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem 1.6rem;
  margin-bottom: 1.8rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  padding: 0.7rem 0.9rem;
  border-radius: 14px;
  background: #f6f9fb;
  border: 1px solid rgba(163, 216, 195, 0.5);
}

.meta-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: #7a8a98;
  margin-bottom: 0.15rem;
}

.meta-value {
  font-size: 0.98rem;
  font-weight: 600;
  color: #23313f;
}

/* ===========================
   ACCIONES
   =========================== */
.course-actions {
  margin-top: auto;
}

/* Botones base (mismo look que el resto) */
.btn {
  padding: 0.85rem 1.6rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-decoration: none;
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

.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 12px 26px rgba(8, 32, 26, 0.5);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 16px 32px rgba(5, 22, 18, 0.65);
}

.btn-large {
  padding-inline: 2.4rem;
  font-size: 0.95rem;
}

/* ===========================
   DETALLES DEL CURSO (secciones)
   =========================== */
.course-details {
  margin-top: 2.1rem;
  display: grid;
  grid-template-columns: 2fr 1.5fr;
  gap: 2rem;
}

/* Sección individual */
.details-section {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.7rem 1.5rem;
  box-shadow:
    0 16px 40px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(163, 216, 195, 0.5);
}

.details-section h2 {
  font-size: 1.2rem;
  color: #12222b;
  margin-bottom: 0.9rem;
}

/* Descripción larga */
.description-content p {
  margin: 0;
  color: #4a5a68;
  font-size: 0.96rem;
  line-height: 1.8;
}

/* Listas */
.learning-objectives,
.requirements {
  list-style: none;
  padding: 0;
  margin: 0.3rem 0 0;
}

.learning-objectives li,
.requirements li {
  position: relative;
  padding-left: 1.6rem;
  margin-bottom: 0.5rem;
  color: #4a5a68;
  font-size: 0.94rem;
  line-height: 1.6;
}

.learning-objectives li::before,
.requirements li::before {
  content: '•';
  position: absolute;
  left: 0.4rem;
  top: 0.1rem;
  font-size: 1.1rem;
  color: var(--emerald-primary);
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 992px) {
  .course-content {
    padding: 1.8rem 1.4rem;
  }

  .course-header {
    grid-template-columns: 1fr;
  }

  .course-image {
    max-width: 380px;
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .course-detail {
    padding: 2.2rem 0 2.7rem;
  }

  .container {
    padding: 0 1.4rem;
  }

  .course-content {
    padding: 1.6rem 1.2rem;
  }

  .course-info h1 {
    font-size: 1.6rem;
  }

  .course-meta {
    grid-template-columns: 1fr;
  }

  .course-details {
    grid-template-columns: 1fr;
  }
}
</style>
