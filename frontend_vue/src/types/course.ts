// Types for Course
export interface Course {
  id: number
  titulo: string
  descripcion: string
  costo_centavos: number
  fecha_inicio: string | null
  fecha_fin: string | null
  activo: boolean
  created_at: string
  updated_at: string
}

export interface CourseFormData {
  titulo: string
  descripcion: string
  costo_centavos: number
  fecha_inicio: string
  fecha_fin: string
}