import apiClient from './api'
import type { Course } from '@/types/course'

export const coursesApi = {
  // Obtener todos los cursos
  getActiveCourses: async (): Promise<Course[]> => {
    const response = await apiClient.get('/cursos')
    return response.data
  }
}

export default coursesApi
