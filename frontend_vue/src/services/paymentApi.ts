import apiClient from './api'
import type { PaymentRequest, PaymentResponse } from '@/types/payment'

export const paymentApi = {
  // Procesar pago
  processPayment: async (data: PaymentRequest): Promise<PaymentResponse> => {
    const response = await apiClient.post('/pagos/autorizar', data)
    return response.data
  }
}

export default paymentApi