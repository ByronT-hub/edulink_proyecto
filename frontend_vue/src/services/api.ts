import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Cliente Laravel
export const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('edulink_token')
  if (token) {
    // nos aseguramos de que headers exista
    if (!config.headers) {
      config.headers = {}
    }
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Cliente Flask
export const paymentsClient = axios.create({
  baseURL: 'http://127.0.0.1:5055/api',
  headers: { 'Content-Type': 'application/json' }
})

export default apiClient
