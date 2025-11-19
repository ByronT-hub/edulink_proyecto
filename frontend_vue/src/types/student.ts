// Types for Student
export interface Student {
  id: number
  nombre: string
  correo: string
  created_at: string
  updated_at: string
}

export interface StudentFormData {
  nombre: string
  correo: string
  contrasena: string
}

export interface StudentRegistrationResponse {
  message: string
  access_token: string
  token_type: string
  user: {
    id: number
    nombre: string
    correo: string
    role: string
  }
}