import apiClient from './api'
import type { StudentFormData, StudentRegistrationResponse } from '@/types/student'

export const authApi = {
  // Registrar nuevo estudiante
  registerStudent: async (data: StudentFormData): Promise<StudentRegistrationResponse> => {
    const response = await apiClient.post('/auth/register', data)
    return response.data
  },

  // Login estudiante
  loginStudent: async (correo: string, contrasena: string): Promise<StudentRegistrationResponse> => {
    const response = await apiClient.post('/auth/login', {
      correo,
      contrasena,
      tipo: 'estudiante'
    })
    return response.data
  },

  // Logout
  logout: async (): Promise<void> => {
    await apiClient.post('/auth/logout')
  },

  // Obtener información del usuario autenticado
  me: async () => {
    const response = await apiClient.get('/auth/me')
    return response.data
  }
}

export default authApi