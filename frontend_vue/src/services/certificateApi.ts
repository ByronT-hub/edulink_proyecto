import apiClient from './api'
import type { Certificate } from '@/types/certificate'

const certificateApi = {

  // Obtener TODOS los certificados del usuario
  getMyCertificates: async (): Promise<Certificate[]> => {
    const { data } = await apiClient.get('/mis-certificados')
    return data
  },

  // Descargar PDF
  download: async (id: number): Promise<Blob> => {
    const { data } = await apiClient.get(`/certificados/${id}/descargar`, {
      responseType: 'blob'
    })
    return data
  },

  // VALIDAR certificado por código
  validate: async (codigo: string) => {
    const { data } = await apiClient.get(`/certificados/validar/${codigo}`)
    return data
  }
}

export default certificateApi
