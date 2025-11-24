import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Certificate } from '@/types/certificate'
import certificateApi from '@/services/certificateApi'

export const useCertificateStore = defineStore('certificate', () => {
  const certificates = ref<Certificate[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // 🔥 AHORA filtramos correctamente usando inscripcion.estudiante_id
  const certificatesByStudent = computed(() => {
    return (studentId: number) =>
      certificates.value.filter(c => c.inscripcion?.estudiante_id === studentId)
  })

  const validCertificates = computed(() =>
    certificates.value.filter(c => c.valido)
  )

  // 🔥 SOLO obtiene mis certificados
  const fetchStudentCertificates = async () => {
    loading.value = true
    error.value = null

    try {
      const data = await certificateApi.getMyCertificates()
      certificates.value = data
    } catch (err: any) {
      error.value = err.message || 'Error cargando certificados'
    } finally {
      loading.value = false
    }
  }

  const downloadCertificate = async (id: number): Promise<Blob> => {
    loading.value = true
    error.value = null
    try {
      return await certificateApi.download(id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al descargar certificado'
      throw err
    } finally {
      loading.value = false
    }
  }

  const validateCertificate = async (codigo: string) => {
    loading.value = true
    error.value = null

    try {
      return await certificateApi.validate(codigo)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al verificar'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    certificates,
    loading,
    error,
    certificatesByStudent,
    validCertificates,
    fetchStudentCertificates,
    downloadCertificate,
    validateCertificate
  }
})
