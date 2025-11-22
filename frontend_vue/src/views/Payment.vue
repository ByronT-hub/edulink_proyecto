<template>
  <div class="payment">
    <div class="container">
      <div v-if="loading" class="loading">
        <p>Cargando información del pago...</p>
      </div>

      <div v-else-if="!course" class="error">
        <p>Curso no encontrado</p>
        <router-link to="/dashboard" class="btn btn-primary">
          Volver al Dashboard
        </router-link>
      </div>

      <div v-else class="payment-container">
        <div class="payment-header">
          <h1>Completar Pago</h1>
          <p>Finaliza tu inscripción al curso</p>
        </div>

        <div class="payment-content">
          <div class="course-summary">
            <h2>Resumen del Curso</h2>
            <div class="course-card">
              <img 
                :src="course.imagen || '/course-placeholder.jpg'" 
                :alt="course.titulo"
                class="course-image"
              />
              <div class="course-info">
                <h3>{{ course.titulo }}</h3>
                <p>{{ course.descripcion }}</p>
                <div class="course-details">
                  <span>📚 {{ course.duracion_horas }}h</span>
                  <span>🎯 {{ course.nivel }}</span>
                </div>
              </div>
            </div>
            
            <div class="price-breakdown">
              <div class="price-row">
                <span>Precio del curso:</span>
                <span>${{ course.precio }}</span>
              </div>
              <div class="price-row total">
                <span>Total a pagar:</span>
                <span>${{ course.precio }}</span>
              </div>
            </div>
          </div>

          <div class="payment-form-section">
            <h2>Información de Pago</h2>
            
            <form @submit.prevent="processPayment" class="payment-form">
              <div class="form-group">
                <label for="payment-method">Método de Pago</label>
                <select 
                  id="payment-method" 
                  v-model="paymentData.metodo_pago"
                  required
                  class="form-input"
                  :disabled="processing"
                >
                  <option value="">Seleccionar método</option>
                  <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                </select>
              </div>

              <div v-if="paymentData.metodo_pago === 'tarjeta'" class="card-details">
                <div class="form-group">
                  <label for="card-number">Número de Tarjeta</label>
                  <input
                    id="card-number"
                    v-model="cardData.numero"
                    type="text"
                    placeholder="1234 5678 9012 3456"
                    required
                    class="form-input"
                    :disabled="processing"
                  />
                </div>

                <div class="card-row">
                  <div class="form-group">
                    <label for="expiry">Fecha de Expiración</label>
                    <input
                      id="expiry"
                      v-model="cardData.expiracion"
                      type="text"
                      placeholder="MM/YY"
                      required
                      class="form-input"
                      :disabled="processing"
                    />
                  </div>
                  <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input
                      id="cvv"
                      v-model="cardData.cvv"
                      type="text"
                      placeholder="123"
                      required
                      maxlength="4"
                      class="form-input"
                      :disabled="processing"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="cardholder">Nombre del Tarjetahabiente</label>
                  <input
                    id="cardholder"
                    v-model="cardData.titular"
                    type="text"
                    placeholder="Nombre completo"
                    required
                    class="form-input"
                    :disabled="processing"
                  />
                </div>
              </div>

              <div v-if="errorMessage" class="feedback-message error-message">
                {{ errorMessage }}
              </div>

              <div v-if="successMessage" class="feedback-message success-message">
                {{ successMessage }}
              </div>

              <button 
                type="submit" 
                :disabled="processing || !isFormValid"
                class="btn btn-primary btn-large btn-full"
              >
                {{ processing ? 'Procesando pago...' : `Pagar $${course.precio}` }}
              </button>
            </form>

            <div class="security-info">
              <p>🔒 Tu información está protegida con cifrado SSL</p>
              <p>💳 Aceptamos las principales tarjetas de crédito y débito</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient, { paymentsClient } from '@/services/api'
import { useCoursesStore } from '@/stores/courses'
import { useStudentStore } from '@/stores/student'

const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()
const studentStore = useStudentStore()

const processing = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const paymentData = ref({
  metodo_pago: 'tarjeta'
})

const cardData = ref({
  numero: '',
  expiracion: '',
  cvv: '',
  titular: ''
})

const courseId = computed(() => parseInt(route.params.enrollmentId as string))
const course = computed(() => coursesStore.getCourseById(courseId.value))
const loading = computed(() => coursesStore.loading)

const isFormValid = computed(() => {
  return (
    paymentData.value.metodo_pago === 'tarjeta' &&
    cardData.value.numero &&
    cardData.value.expiracion &&
    cardData.value.cvv &&
    cardData.value.titular
  )
})

const processPayment = async () => {
  if (!course.value) return

  processing.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const [exp_mm, exp_yy] = cardData.value.expiracion.split('/')

    const payload = {
      merchant_ref: `curso-${course.value.id}`,
      amount_cents: course.value.precio * 100,
      currency: "GTQ",
      card: {
        holder_name: cardData.value.titular,
        pan: cardData.value.numero.replace(/\s/g, ''),
        exp_mm,
        exp_yy,
        ccv: cardData.value.cvv
      }
    }

    // LLAMADA AL MICROSERVICIO
    const response = await paymentsClient.post('/tarjetas/autorizar', payload)
    const data = response.data

    if (!data.approved) {
      errorMessage.value = data.message || "Pago rechazado"
      return
    }

    successMessage.value = `Pago aprobado. Código: ${data.auth_code}`

    // LLAMAR A LARAVEL
    await apiClient.post('/pagos/autorizar', {
      curso_id: course.value.id,
      tarjeta: {
        nombre: cardData.value.titular,
        pan: cardData.value.numero,
        exp_mm,
        exp_yy,
        ccv: cardData.value.cvv
      }
    })

    setTimeout(() => router.push('/dashboard'), 2000)

  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Error al procesar el pago'
  } finally {
    processing.value = false
  }
}

onMounted(async () => {
  if (!course.value) {
    await coursesStore.fetchCourses()
  }
})
</script>

<style scoped>
.payment {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --danger: #e05252;

  min-height: calc(100vh - 80px);
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  padding: 3rem 0 3.5rem;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* HEADER */
.payment-header {
  text-align: center;
  margin-bottom: 2.4rem;
  color: #12222b;
}

.payment-header h1 {
  font-size: 2.1rem;
  margin-bottom: 0.5rem;
  letter-spacing: 0.04em;
}

.payment-header p {
  font-size: 0.95rem;
  opacity: 0.9;
}

/* ESTADOS */
.loading,
.error {
  text-align: center;
  padding: 4rem 0;
  color: #5c6a74;
}

.error .btn {
  margin-top: 1.2rem;
}

/* LAYOUT PRINCIPAL */
.payment-container {
  background: transparent;
}

.payment-content {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr);
  gap: 2rem;
  align-items: flex-start;
}

/* PANEL CURSO (IZQ) */
.course-summary {
  background: rgba(248, 252, 250, 0.92);
  border-radius: 24px;
  padding: 2rem 1.8rem;
  box-shadow:
    0 24px 60px rgba(10, 28, 24, 0.4),
    0 0 0 1px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(163, 216, 195, 0.8);
}

.course-summary h2 {
  margin-bottom: 1.2rem;
  color: #12222b;
  font-size: 1.2rem;
}

.course-card {
  display: flex;
  gap: 1.2rem;
  margin-bottom: 1.5rem;
}

.course-image {
  width: 110px;
  height: 110px;
  object-fit: cover;
  border-radius: 18px;
  box-shadow: 0 16px 35px rgba(6, 22, 18, 0.7);
}

.course-info h3 {
  color: #12222b;
  margin: 0 0 0.35rem;
  font-size: 1rem;
}

.course-info p {
  color: #5c6a74;
  font-size: 0.9rem;
  margin: 0 0 0.6rem;
  line-height: 1.4;
}

.course-details {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  font-size: 0.78rem;
}

.course-details span {
  background: #edf3f1;
  color: #23313f;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
}

/* PRICE BOX */
.price-breakdown {
  margin-top: 1.3rem;
  border-radius: 18px;
  background: #f3faf7;
  padding: 1rem 1.1rem;
  border: 1px dashed rgba(79, 144, 133, 0.5);
}

.price-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #23313f;
  margin-bottom: 0.3rem;
}

.price-row.total {
  border-top: 1px dashed rgba(79, 144, 133, 0.45);
  padding-top: 0.55rem;
  margin-top: 0.4rem;
  font-weight: 700;
}

/* PANEL PAGO (DER) */
.payment-form-section {
  background: rgba(255, 255, 255, 0.97);
  border-radius: 24px;
  padding: 2.1rem 1.9rem 1.8rem;
  box-shadow:
    0 24px 60px rgba(10, 28, 24, 0.45),
    0 0 0 1px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(163, 216, 195, 0.85);
  position: relative;
}

.payment-form-section h2 {
  margin-bottom: 1.4rem;
  color: #12222b;
  font-size: 1.2rem;
}

/* FORM */
.payment-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #23313f;
}

.form-input {
  width: 100%;
  padding: 0.8rem 1rem;
  border-radius: 10px;
  border: 2px solid #e1ece8;
  font-size: 0.92rem;
  background: rgba(251, 253, 252, 0.95);
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
  background: #ffffff;
}

/* CARD DETAILS */
.card-details {
  margin-top: 0.2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.card-row {
  display: grid;
  grid-template-columns: 1.2fr 0.9fr;
  gap: 0.8rem;
}

/* MENSAJES */
.feedback-message {
  padding: 0.75rem 0.9rem;
  border-radius: 10px;
  font-size: 0.86rem;
  margin-top: 0.3rem;
}

.error-message {
  background: #fde2e2;
  border: 1px solid #f19999;
  color: #9b2626;
}

.success-message {
  background: #dcf7e8;
  border: 1px solid #7ad6a2;
  color: #1c6b3a;
}

/* BOTONES */
.btn {
  padding: 0.75rem 1.6rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease,
    color 0.2s ease;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 16px 35px rgba(6, 22, 18, 0.7);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 20px 50px rgba(5, 15, 13, 0.9);
}

.btn-full {
  width: 100%;
  margin-top: 0.5rem;
}

.btn-large {
  padding-block: 0.9rem;
}

/* INFO SEGURIDAD */
.security-info {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px dashed #e1ece8;
  font-size: 0.82rem;
  color: #6a7880;
}

/* RESPONSIVE */
@media (max-width: 960px) {
  .payment-content {
    grid-template-columns: minmax(0, 1fr);
  }

  .course-summary {
    order: 2;
  }

  .payment-form-section {
    order: 1;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .payment-header h1 {
    font-size: 1.8rem;
  }

  .course-card {
    flex-direction: column;
  }

  .course-image {
    width: 100%;
    height: 180px;
  }

  .card-row {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .card-row {
    grid-template-columns: 1fr;
  }

  .payment-form-section,
  .course-summary {
    padding: 1.5rem 1.3rem 1.3rem;
  }
}
</style>
