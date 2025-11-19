import apiClient from './api'
import type { EnrollmentRequest, EnrollmentResponse } from '@/types/enrollment'

export const enrollmentApi = {

  // Inscribirse SIN pago (tu backend lo permite, pero no lo usaremos)
  enrollToCourse: async (courseId: number): Promise<EnrollmentResponse> => {
    const response = await apiClient.post(`/cursos/${courseId}/inscribirse`)
    return response.data
  },

  // Obtener MIS inscripciones
  getMyEnrollments: async (): Promise<EnrollmentResponse[]> => {
    const response = await apiClient.get('/mis-cursos')
    return response.data
  }
}

export default enrollmentApi
