import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Payment, PaymentCreateData } from '@/types/payment'
import paymentApi from '@/services/paymentApi'

export const usePaymentStore = defineStore('payment', () => {
  const payments = ref<Payment[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const processingPayment = ref(false)

  // Getters
  const paymentsByStudent = computed(() => {
    return (studentId: number) => 
      payments.value.filter(p => p.estudiante_id === studentId)
  })

  const paymentsByEnrollment = computed(() => {
    return (enrollmentId: number) => 
      payments.value.filter(p => p.inscripcion_id === enrollmentId)
  })

  const getPendingPayments = computed(() => 
    payments.value.filter(p => p.estado === 'pendiente')
  )

  const getCompletedPayments = computed(() => 
    payments.value.filter(p => p.estado === 'completado')
  )

  // Actions
  const fetchPayments = async () => {
    loading.value = true
    error.value = null

    try {
      const data = await paymentApi.getAll()
      payments.value = data
    } catch (err: any) {
      error.value = err.message || 'Error al cargar pagos'
    } finally {
      loading.value = false
    }
  }

  const fetchStudentPayments = async (studentId: number) => {
    loading.value = true
    error.value = null

    try {
      const data = await paymentApi.getByStudent(studentId)
      // Actualizar solo los pagos del estudiante
      payments.value = payments.value.filter(p => p.estudiante_id !== studentId)
      payments.value.push(...data)
    } catch (err: any) {
      error.value = err.message || 'Error al cargar pagos del estudiante'
    } finally {
      loading.value = false
    }
  }

  const initializePayment = async (data: PaymentCreateData) => {
    loading.value = true
    processingPayment.value = true
    error.value = null

    try {
      const newPayment = await paymentApi.create(data)
      payments.value.push(newPayment)
      return newPayment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al inicializar pago'
      throw err
    } finally {
      loading.value = false
      processingPayment.value = false
    }
  }

  const processPayment = async (paymentId: number, paymentMethod: string) => {
    processingPayment.value = true
    error.value = null

    try {
      const updatedPayment = await paymentApi.processPayment(paymentId, paymentMethod)
      
      const index = payments.value.findIndex(p => p.id === paymentId)
      if (index !== -1) {
        payments.value[index] = updatedPayment
      }
      
      return updatedPayment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al procesar pago'
      throw err
    } finally {
      processingPayment.value = false
    }
  }

  const updatePaymentStatus = async (paymentId: number, estado: string) => {
    loading.value = true
    error.value = null

    try {
      const updatedPayment = await paymentApi.update(paymentId, { estado })
      
      const index = payments.value.findIndex(p => p.id === paymentId)
      if (index !== -1) {
        payments.value[index] = updatedPayment
      }
      
      return updatedPayment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al actualizar estado del pago'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getPaymentById = async (id: number) => {
    // Primero buscar en store local
    let payment = payments.value.find(p => p.id === id)
    
    if (!payment) {
      loading.value = true
      try {
        payment = await paymentApi.getById(id)
        if (payment) {
          const existingIndex = payments.value.findIndex(p => p.id === id)
          if (existingIndex === -1) {
            payments.value.push(payment)
          }
        }
      } catch (err: any) {
        error.value = err.message || 'Error al obtener pago'
      } finally {
        loading.value = false
      }
    }
    
    return payment
  }

  const clearError = () => {
    error.value = null
  }

  const clearPayments = () => {
    payments.value = []
  }

  return {
    payments,
    loading,
    error,
    processingPayment,
    paymentsByStudent,
    paymentsByEnrollment,
    getPendingPayments,
    getCompletedPayments,
    fetchPayments,
    fetchStudentPayments,
    initializePayment,
    processPayment,
    updatePaymentStatus,
    getPaymentById,
    clearError,
    clearPayments
  }
})