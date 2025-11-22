import { createPinia } from 'pinia'

export const pinia = createPinia()

export default pinia

// Re-export all stores
export { useCoursesStore } from './courses'
export { useStudentStore } from './student'
export { useEnrollmentStore } from './enrollment'
export { usePaymentStore } from './payment'
export { useCertificateStore } from './certificate'
export { useProgressStore } from './progress'