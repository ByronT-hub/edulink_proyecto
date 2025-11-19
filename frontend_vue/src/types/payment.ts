// Types for Payment
export interface CreditCard {
  nombre: string
  pan: string
  exp_mm: string
  exp_yy: string
  ccv: string
}

export interface PaymentRequest {
  inscripcion_id: number
  monto_centavos: number
  tarjeta: CreditCard
}

export interface PaymentResponse {
  pago: {
    id: number
    inscripcion_id: number
    monto_centavos: number
    moneda: string
    estado: string
    codigo_autorizacion: string | null
    mensaje: string | null
    created_at: string
  }
  certificado?: {
    id: number
    codigo: string
    url_qr: string
    fecha_emision: string
  }
}

export interface PaymentStatus {
  loading: boolean
  success: boolean
  error: string | null
}