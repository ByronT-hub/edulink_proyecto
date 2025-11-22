import { defineStore } from 'pinia'
import { ref } from 'vue'
import progressApi from '@/services/progressApi'

export const useProgressStore = defineStore('progress', () => {
  const progress = ref<any | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchProgress = async (inscripcionId: number) => {
    loading.value = true
    error.value = null
    try {
      progress.value = await progressApi.getProgress(inscripcionId)
    } catch (err: any) {
      error.value = err.message || 'Error al cargar progreso'
    } finally {
      loading.value = false
    }
  }

  const updateProgress = async (inscripcionId: number, leccionesCompletadas: number[], porcentaje: number) => {
    loading.value = true
    error.value = null
    try {
      progress.value = await progressApi.updateProgress(inscripcionId, leccionesCompletadas, porcentaje)
    } catch (err: any) {
      error.value = err.message || 'Error al guardar progreso'
    } finally {
      loading.value = false
    }
  }

  return { progress, loading, error, fetchProgress, updateProgress }
})

export default useProgressStore
