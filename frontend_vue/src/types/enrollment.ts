// Types for Enrollment
export interface Enrollment {
  id: number
  estudiante_id: number
  curso_id: number
  estado: string
  created_at: string
  updated_at: string
}

export interface EnrollmentRequest {
  curso_id: number
}

export interface EnrollmentResponse {
  id: number
  estudiante_id: number
  curso_id: number
  estado: string
  estudiante?: {
    id: number
    nombre: string
    correo: string
  }
  curso?: {
    id: number
    titulo: string
    descripcion: string
    costo_centavos: number
  }
}