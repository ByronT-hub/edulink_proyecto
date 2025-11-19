import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Course } from '@/types/course'
import coursesApi from '@/services/coursesApi'

export const useCoursesStore = defineStore('courses', () => {
  const courses = ref<Course[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const activeCourses = computed(() => 
    courses.value.filter(course => course.activo)
  )

  const getCourseById = computed(() => (id: number) =>
    courses.value.find(course => course.id === id)
  )

  // Actions
  const fetchCourses = async () => {
    loading.value = true
    error.value = null
    
    try {
      courses.value = await coursesApi.getActiveCourses()
    } catch (err: any) {
      error.value = err.message || 'Error al cargar cursos'
    } finally {
      loading.value = false
    }
  }

  const formatPrice = (centavos: number): string => {
    return `Q${(centavos / 100).toFixed(2)}`
  }

  const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'No definida'
    
    const date = new Date(dateString)
    return date.toLocaleDateString('es-GT', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  return {
    courses,
    loading,
    error,
    activeCourses,
    getCourseById,
    fetchCourses,
    formatPrice,
    formatDate
  }
})