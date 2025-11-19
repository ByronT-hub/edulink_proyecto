import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Crear instancia de axios para Laravel
export const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor para requests (agregar token si existe)
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('edulink_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para responses (manejar errores)
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado o inválido
      localStorage.removeItem('edulink_token')
      localStorage.removeItem('edulink_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// 🔥🔥🔥 NUEVO: Cliente para el microservicio Flask (tarjetas)
export const paymentsClient = axios.create({
  baseURL: 'http://127.0.0.1:5055/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

export default apiClient
