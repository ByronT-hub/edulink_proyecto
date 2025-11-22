import apiClient from './api'

export const progressApi = {
  // Obtener progreso de un estudiante en un curso (por inscripcion)
  getProgress: async (inscripcionId: number) => {
    const response = await apiClient.get(`/progresos/${inscripcionId}`)
    return response.data.progreso
  },
  // Guardar o actualizar progreso
  updateProgress: async (inscripcionId: number, leccionesCompletadas: number[], porcentaje: number) => {
    const response = await apiClient.put(`/progresos/${inscripcionId}`, {
      lecciones_completadas: leccionesCompletadas,
      porcentaje: porcentaje
    })
    return response.data.progreso
  }
}

export default progressApi;
