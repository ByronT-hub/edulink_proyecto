// Types for Certificate
export interface Certificate {
  id: number
  codigo: string
  url_qr: string
  fecha_emision: string
  estudiante: {
    id: number
    nombre: string
    correo: string
  }
  curso: {
    id: number
    titulo: string
    costo_centavos: number
  }
}