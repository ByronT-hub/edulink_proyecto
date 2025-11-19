import apiClient from './api'
import type { Certificate } from '@/types/certificate'

const certificateApi = {

  // Obtener mis certificados
  getMyCertificates: async (): Promise<Certificate[]> => {
    const response = await apiClient.get('/mis-certificados')
    return response.data
  },

  // Obtener certificado por ID
  getById: async (id: number): Promise<Certificate> => {
    const response = await apiClient.get(`/certificados/${id}`)
    return response.data
  }
}

export default certificateApi
