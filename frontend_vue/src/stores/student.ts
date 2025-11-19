import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Student, StudentFormData, StudentRegistrationResponse } from '@/types/student'
import authApi from '@/services/authApi'

export const useStudentStore = defineStore('student', () => {
  const student = ref<Student | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Inicializar desde localStorage
  const initializeFromStorage = () => {
    const storedToken = localStorage.getItem('edulink_token')
    const storedUser = localStorage.getItem('edulink_user')
    
    if (storedToken) token.value = storedToken
    if (storedUser) {
      try {
        student.value = JSON.parse(storedUser)
      } catch {
        localStorage.removeItem('edulink_user')
      }
    }
  }

  // Actions
  const register = async (data: StudentFormData): Promise<StudentRegistrationResponse> => {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.registerStudent(data)
      
      // Guardar datos
      student.value = {
        id: response.user.id,
        nombre: response.user.nombre,
        correo: response.user.correo,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      }
      token.value = response.access_token

      // Persistir en localStorage
      localStorage.setItem('edulink_token', response.access_token)
      localStorage.setItem('edulink_user', JSON.stringify(student.value))

      return response
    } catch (err: any) {
      console.error('Error en registro:', err)
      if (err.response?.status === 422) {
        // Error de validación
        const validationErrors = err.response.data.errors
        if (validationErrors) {
          const firstError = Object.values(validationErrors)[0] as string[]
          error.value = firstError[0]
        } else {
          error.value = err.response.data.message || 'Error de validación'
        }
      } else {
        error.value = err.response?.data?.message || 'Error al registrar estudiante'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const login = async (correo: string, contrasena: string): Promise<StudentRegistrationResponse> => {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.loginStudent(correo, contrasena)
      
      student.value = {
        id: response.user.id,
        nombre: response.user.nombre,
        correo: response.user.correo,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      }
      token.value = response.access_token

      localStorage.setItem('edulink_token', response.access_token)
      localStorage.setItem('edulink_user', JSON.stringify(student.value))

      return response
    } catch (err: any) {
      console.error('Error en login:', err)
      if (err.response?.status === 422) {
        const validationErrors = err.response.data.errors
        if (validationErrors) {
          const firstError = Object.values(validationErrors)[0] as string[]
          error.value = firstError[0]
        } else {
          error.value = err.response.data.message || 'Error de validación'
        }
      } else {
        error.value = err.response?.data?.message || 'Error al iniciar sesión'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await authApi.logout()
      }
    } catch (err) {
      console.error('Error al hacer logout:', err)
    } finally {
      // Limpiar estado local
      student.value = null
      token.value = null
      localStorage.removeItem('edulink_token')
      localStorage.removeItem('edulink_user')
    }
  }

  const isAuthenticated = (): boolean => {
    return !!(student.value && token.value)
  }

  return {
    student,
    token,
    loading,
    error,
    register,
    login,
    logout,
    isAuthenticated,
    initializeFromStorage
  }
})