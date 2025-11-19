import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Enrollment, EnrollmentCreateData } from '@/types/enrollment'
import enrollmentApi from '@/services/enrollmentApi'

export const useEnrollmentStore = defineStore('enrollment', () => {
  const enrollments = ref<Enrollment[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const enrollmentsByStudent = computed(() => {
    return (studentId: number) => 
      enrollments.value.filter(e => e.estudiante_id === studentId)
  })

  const enrollmentsByCourse = computed(() => {
    return (courseId: number) => 
      enrollments.value.filter(e => e.curso_id === courseId)
  })

  const getEnrollmentById = computed(() => {
    return (id: number) => 
      enrollments.value.find(e => e.id === id)
  })

  // Actions
  const fetchEnrollments = async () => {
    loading.value = true
    error.value = null

    try {
      const data = await enrollmentApi.getAll()
      enrollments.value = data
    } catch (err: any) {
      error.value = err.message || 'Error al cargar inscripciones'
    } finally {
      loading.value = false
    }
  }

  const fetchStudentEnrollments = async (studentId: number) => {
    loading.value = true
    error.value = null

    try {
      const data = await enrollmentApi.getByStudent(studentId)
      // Actualizar solo las inscripciones del estudiante
      enrollments.value = enrollments.value.filter(e => e.estudiante_id !== studentId)
      enrollments.value.push(...data)
    } catch (err: any) {
      error.value = err.message || 'Error al cargar inscripciones del estudiante'
    } finally {
      loading.value = false
    }
  }

  const createEnrollment = async (data: EnrollmentCreateData) => {
    loading.value = true
    error.value = null

    try {
      const newEnrollment = await enrollmentApi.create(data)
      enrollments.value.push(newEnrollment)
      return newEnrollment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al crear inscripción'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateEnrollment = async (id: number, data: Partial<EnrollmentCreateData>) => {
    loading.value = true
    error.value = null

    try {
      const updatedEnrollment = await enrollmentApi.update(id, data)
      
      const index = enrollments.value.findIndex(e => e.id === id)
      if (index !== -1) {
        enrollments.value[index] = updatedEnrollment
      }
      
      return updatedEnrollment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al actualizar inscripción'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteEnrollment = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      await enrollmentApi.delete(id)
      enrollments.value = enrollments.value.filter(e => e.id !== id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al eliminar inscripción'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    enrollments,
    loading,
    error,
    enrollmentsByStudent,
    enrollmentsByCourse,
    getEnrollmentById,
    fetchEnrollments,
    fetchStudentEnrollments,
    createEnrollment,
    updateEnrollment,
    deleteEnrollment,
    clearError
  }
})