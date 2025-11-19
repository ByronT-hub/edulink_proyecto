import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Certificate } from '@/types/certificate'
import certificateApi from '@/services/certificateApi'

export const useCertificateStore = defineStore('certificate', () => {
  const certificates = ref<Certificate[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const generatingCertificate = ref(false)

  // Getters
  const certificatesByStudent = computed(() => {
    return (studentId: number) => 
      certificates.value.filter(c => c.estudiante_id === studentId)
  })

  const certificatesByCourse = computed(() => {
    return (courseId: number) => 
      certificates.value.filter(c => c.curso_id === courseId)
  })

  const getCertificateById = computed(() => {
    return (id: number) => 
      certificates.value.find(c => c.id === id)
  })

  const validCertificates = computed(() => 
    certificates.value.filter(c => c.valido === true)
  )

  // Actions
  const fetchCertificates = async () => {
    loading.value = true
    error.value = null

    try {
      const data = await certificateApi.getAll()
      certificates.value = data
    } catch (err: any) {
      error.value = err.message || 'Error al cargar certificados'
    } finally {
      loading.value = false
    }
  }

  const fetchStudentCertificates = async (studentId: number) => {
    loading.value = true
    error.value = null

    try {
      const data = await certificateApi.getByStudent(studentId)
      // Actualizar solo los certificados del estudiante
      certificates.value = certificates.value.filter(c => c.estudiante_id !== studentId)
      certificates.value.push(...data)
    } catch (err: any) {
      error.value = err.message || 'Error al cargar certificados del estudiante'
    } finally {
      loading.value = false
    }
  }

  const generateCertificate = async (enrollmentId: number) => {
    generatingCertificate.value = true
    error.value = null

    try {
      const newCertificate = await certificateApi.generate(enrollmentId)
      certificates.value.push(newCertificate)
      return newCertificate
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al generar certificado'
      throw err
    } finally {
      generatingCertificate.value = false
    }
  }

  const validateCertificate = async (codigoQr: string) => {
    loading.value = true
    error.value = null

    try {
      const validation = await certificateApi.validate(codigoQr)
      return validation
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al validar certificado'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getCertificateByQr = async (codigoQr: string) => {
    loading.value = true
    error.value = null

    try {
      const certificate = await certificateApi.getByQr(codigoQr)
      
      // Agregar al store si no existe
      const existingIndex = certificates.value.findIndex(c => c.id === certificate.id)
      if (existingIndex === -1) {
        certificates.value.push(certificate)
      } else {
        certificates.value[existingIndex] = certificate
      }
      
      return certificate
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al obtener certificado'
      throw err
    } finally {
      loading.value = false
    }
  }

  const downloadCertificate = async (id: number): Promise<Blob> => {
    loading.value = true
    error.value = null

    try {
      const pdfBlob = await certificateApi.download(id)
      return pdfBlob
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al descargar certificado'
      throw err
    } finally {
      loading.value = false
    }
  }

  const revokeCertificate = async (id: number) => {
    loading.value = true
    error.value = null

    try {
      const updatedCertificate = await certificateApi.revoke(id)
      
      const index = certificates.value.findIndex(c => c.id === id)
      if (index !== -1) {
        certificates.value[index] = updatedCertificate
      }
      
      return updatedCertificate
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al revocar certificado'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCertificates = () => {
    certificates.value = []
  }

  return {
    certificates,
    loading,
    error,
    generatingCertificate,
    certificatesByStudent,
    certificatesByCourse,
    getCertificateById,
    validCertificates,
    fetchCertificates,
    fetchStudentCertificates,
    generateCertificate,
    validateCertificate,
    getCertificateByQr,
    downloadCertificate,
    revokeCertificate,
    clearError,
    clearCertificates
  }
})