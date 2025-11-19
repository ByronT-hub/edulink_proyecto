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
/* TODOS tus estilos se mantienen EXACTAMENTE igual */
/* ... */
</style>
