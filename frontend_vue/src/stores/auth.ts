import { defineStore } from 'pinia'
import { ref } from 'vue'

interface User {
  id: number
  nombre: string
  correo: string
  role: string
  especialidad?: string
  biografia?: string
  telefono?: string
  created_at: string
  updated_at: string
}

interface RegisterData {
  nombre: string
  correo: string
  contrasena: string
  role: string
  especialidad?: string
  biografia?: string
  telefono?: string
}

interface AuthResponse {
  access_token: string
  user: User
  message?: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Inicializar desde localStorage
  const initializeFromStorage = () => {
    const storedToken = localStorage.getItem('edulink_token')
    const storedUser = localStorage.getItem('edulink_user')
    
    if (storedToken && storedUser) {
      try {
        const userData = JSON.parse(storedUser)
        if (userData && userData.id) {
          token.value = storedToken
          user.value = userData
        } else {
          // Limpiar datos inválidos
          localStorage.removeItem('edulink_token')
          localStorage.removeItem('edulink_user')
        }
      } catch {
        // Limpiar datos corruptos
        localStorage.removeItem('edulink_token')
        localStorage.removeItem('edulink_user')
      }
    }
  }

  // Registro universal
  const register = async (data: RegisterData): Promise<AuthResponse> => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('http://localhost:8000/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Error al registrar usuario')
      }

      const result: AuthResponse = await response.json()
      
      // Guardar datos del usuario
      user.value = result.user
      token.value = result.access_token

      // Persistir en localStorage
      localStorage.setItem('edulink_token', result.access_token)
      localStorage.setItem('edulink_user', JSON.stringify(user.value))

      return result
    } catch (err: any) {
      console.error('Error en registro:', err)
      error.value = err.message || 'Error al registrar usuario'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Login universal
  const login = async (correo: string, contrasena: string): Promise<AuthResponse> => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('http://localhost:8000/api/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ correo, contrasena })
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Error al iniciar sesión')
      }

      const result: AuthResponse = await response.json()
      
      user.value = result.user
      token.value = result.access_token

      localStorage.setItem('edulink_token', result.access_token)
      localStorage.setItem('edulink_user', JSON.stringify(user.value))

      return result
    } catch (err: any) {
      console.error('Error en login:', err)
      error.value = err.message || 'Error al iniciar sesión'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Logout
  const logout = async () => {
    loading.value = true
    
    try {
      if (token.value) {
        await fetch('http://localhost:8000/api/auth/logout', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token.value}`,
            'Content-Type': 'application/json',
          }
        })
      }
    } catch (err) {
      console.error('Error en logout:', err)
    } finally {
      // Limpiar todo el estado
      user.value = null
      token.value = null
      error.value = null
      loading.value = false
      
      // Limpiar localStorage completamente
      localStorage.removeItem('edulink_token')
      localStorage.removeItem('edulink_user')
      
      // También limpiar cualquier otro dato relacionado
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      
      console.log('Logout completed - state cleared')
    }
  }

  // Verificar si está autenticado
  const isAuthenticated = () => {
    return !!(token.value && user.value && user.value.id)
  }

  // Verificar rol
  const hasRole = (role: string) => {
    return user.value?.role === role
  }

  // Obtener datos del usuario actual
  const getCurrentUser = async () => {
    if (!token.value) return null

    try {
      const response = await fetch('http://localhost:8000/api/auth/me', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        }
      })

      if (!response.ok) {
        throw new Error('Token inválido')
      }

      const userData = await response.json()
      user.value = userData
      localStorage.setItem('edulink_user', JSON.stringify(user.value))
      
      return userData
    } catch (err) {
      console.error('Error al obtener usuario:', err)
      await logout()
      return null
    }
  }

  // Limpiar error
  const clearError = () => {
    error.value = null
  }

  // Limpiar todo (útil para debug)
  const clearAll = () => {
    user.value = null
    token.value = null
    error.value = null
    loading.value = false
    
    // Limpiar localStorage completamente
    localStorage.removeItem('edulink_token')
    localStorage.removeItem('edulink_user')
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    
    console.log('All auth state cleared')
  }

  return {
    user,
    token,
    loading,
    error,
    initializeFromStorage,
    register,
    login,
    logout,
    isAuthenticated,
    hasRole,
    getCurrentUser,
    clearError,
    clearAll
  }
})