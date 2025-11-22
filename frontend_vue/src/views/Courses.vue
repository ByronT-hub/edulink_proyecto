<template> 
  <div class="courses">
    <div class="container">
      <div class="courses-header">
        <h1>Nuestros Cursos</h1>
        <p>Descubre una amplia gama de cursos diseñados para impulsar tu carrera</p>
      </div>

      <div class="filters">
        <div class="search-box">
          <input 
            v-model="searchTerm" 
            type="text" 
            placeholder="Buscar cursos..."
            class="search-input"
          >
        </div>

        <select v-model="selectedCategory" class="category-filter">
          <option value="">Todas las categorías</option>
          <option value="Programación">Programación</option>
          <option value="Diseño">Diseño</option>
          <option value="Marketing">Marketing</option>
          <option value="Negocios">Negocios</option>
        </select>
      </div>

      <div v-if="loading" class="loading">
        <p>Cargando cursos...</p>
      </div>

      <div v-else-if="error" class="error">
        <p>Error al cargar cursos: {{ error }}</p>
        <button @click="fetchCourses" class="btn btn-primary">Reintentar</button>
      </div>

      <div v-else-if="filteredCourses.length === 0" class="no-courses">
        <p>No se encontraron cursos que coincidan con tu búsqueda.</p>
      </div>

      <div v-else class="courses-grid">
        <div 
          v-for="course in filteredCourses" 
          :key="course.id"
          class="course-card"
        >
          <div class="course-image">
            <img :src="'/course-placeholder.jpg'" :alt="course.titulo" />
            <div class="course-price">
              Q{{ course.precio }}
            </div>
          </div>
          
          <div class="course-content">
            <h3 class="course-title">{{ course.titulo }}</h3>
            <p class="course-description">{{ course.descripcion }}</p>

            <div class="course-meta">
              <span class="course-duration">
                📚 {{ course.duracion }} horas
              </span>

              <span class="course-level">
                🎯 {{ course.nivel }}
              </span>
            </div>

            <div class="course-actions">
              <router-link 
                :to="`/course/${course.id}`" 
                class="btn btn-outline"
              >
                Ver Detalles
              </router-link>
              
              <button 
                v-if="!isAuthenticated || authStore.user?.role !== 'estudiante'"
                @click="$router.push('/login')"
                class="btn btn-primary"
              >
                Inscribirse
              </button>
              
              <button 
                v-else
                @click="handleEnrollment(course)"
                :disabled="enrolling === course.id"
                class="btn btn-primary"
              >
                {{ enrolling === course.id ? 'Procesando...' : `Inscribirse - Q${course.precio}` }}
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Pago -->
  <div v-if="showPaymentModal" class="modal-overlay" @click="showPaymentModal = false">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>💳 Procesar Pago</h2>
        <button class="close-btn" @click="showPaymentModal = false">&times;</button>
      </div>

      <div class="modal-course-info" v-if="selectedCourse">
        <h3>{{ selectedCourse.titulo }}</h3>
        <p class="price">Total a pagar: <strong>Q{{ selectedCourse.precio }}</strong></p>
      </div>

      <form @submit.prevent="procesarPago" class="payment-form">
        <div class="payment-methods">
          <h4>Método de Pago</h4>
          <div class="method-options">
            <label class="method-option">
              <input type="radio" value="tarjeta" v-model="paymentMethod" required>
              <span>💳 Tarjeta de Crédito/Débito</span>
            </label>

            <label class="method-option">
              <input type="radio" value="paypal" v-model="paymentMethod">
              <span>🔵 PayPal</span>
            </label>

            <label class="method-option">
              <input type="radio" value="transferencia" v-model="paymentMethod">
              <span>🏦 Transferencia Bancaria</span>
            </label>
          </div>
        </div>

        <!-- Campos tarjeta -->
        <div v-if="paymentMethod === 'tarjeta'" class="card-fields">
          <div class="form-group">
            <label>Número de Tarjeta *</label>
            <input v-model="cardData.numero_tarjeta" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Fecha Exp *</label>
              <input v-model="cardData.fecha_expiracion" required placeholder="MM/YY" />
            </div>

            <div class="form-group">
              <label>CVV *</label>
              <input v-model="cardData.cvv" required />
            </div>
          </div>

          <div class="form-group">
            <label>Nombre del Titular *</label>
            <input v-model="cardData.nombre_titular" required />
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" @click="showPaymentModal = false">
            Cancelar
          </button>

          <button type="submit" class="btn btn-primary" :disabled="enrolling">
            {{ enrolling ? 'Procesando...' : `Pagar Q${selectedCourse?.precio || 0}` }}
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const searchTerm = ref('')
const selectedCategory = ref('')
const enrolling = ref<number | null>(null)
const courses = ref<any[]>([])
const loading = ref(false)
const error = ref('')
const showPaymentModal = ref(false)
const selectedCourse = ref<any>(null)

const paymentMethod = ref('tarjeta')
const cardData = ref({
  numero_tarjeta: '',
  fecha_expiracion: '',
  cvv: '',
  nombre_titular: ''
})

const isAuthenticated = computed(() => authStore.isAuthenticated())

const filteredCourses = computed(() => {
  let list = courses.value

  if (searchTerm.value) {
    let text = searchTerm.value.toLowerCase()
    list = list.filter(c => 
      c.titulo.toLowerCase().includes(text) ||
      c.descripcion.toLowerCase().includes(text)
    )
  }

  if (selectedCategory.value) {
    list = list.filter(c => c.categoria === selectedCategory.value)
  }

  return list
})

const handleEnrollment = (course: any) => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }

  selectedCourse.value = course
  showPaymentModal.value = true
}

// 💳 PROCESAR PAGO (solo Laravel)
const procesarPago = async () => {
  if (!selectedCourse.value) return;

  enrolling.value = selectedCourse.value.id;

  const [exp_mm, exp_yy] = cardData.value.fecha_expiracion.split('/');

  try {
    const payloadLaravel = {
      curso_id: selectedCourse.value.id,
      tarjeta: {
        nombre: cardData.value.nombre_titular,
        pan: cardData.value.numero_tarjeta,
        exp_mm,
        exp_yy,
        ccv: cardData.value.cvv
      }
    };

    const response = await apiClient.post('/pagos/autorizar', payloadLaravel);

    alert("✔ Pago aprobado y registrado correctamente");
    console.log("Respuesta Laravel:", response.data);

    showPaymentModal.value = false;

  } catch (err: any) {
    console.error("ERROR PROCESANDO PAGO:", err);

    let msg = "Error procesando pago";

    if (err.response?.data?.message) msg = err.response.data.message;
    if (err.response?.data?.error) msg = err.response.data.error;

    alert("❌ " + msg);
  }

  enrolling.value = null;
};

const fetchCourses = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch('http://localhost:8000/api/cursos')
    courses.value = await res.json()
  } catch (e) {
    error.value = 'No se pudieron cargar los cursos'
  }
  loading.value = false
}

onMounted(fetchCourses)
</script>

<style scoped>
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.courses {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 20px;

  min-height: calc(100vh - 80px);
  padding: 3rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* ===========================
   HEADER
   =========================== */
.courses-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.courses-header h1 {
  font-size: 2.1rem;
  color: #12222b;
  margin-bottom: 0.6rem;
  letter-spacing: 0.03em;
}

.courses-header p {
  font-size: 0.98rem;
  color: #6d7a86;
}

/* ===========================
   FILTROS
   =========================== */
.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.4rem;
  background: rgba(255, 255, 255, 0.96);
  border-radius: 999px;
  padding: 0.8rem 1.1rem;
  box-shadow:
    0 18px 44px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.6);
}

.search-box {
  flex: 1;
  min-width: 220px;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  padding: 0.6rem 0.8rem;
  font-size: 0.96rem;
  color: var(--neutral-dark);
}

.search-input::placeholder {
  color: #9aa7b2;
}

.search-input:focus {
  outline: none;
}

/* Selector de categoría */
.category-filter {
  min-width: 210px;
  border-radius: 999px;
  border: 1px solid rgba(163, 216, 195, 0.7);
  padding: 0.55rem 1rem;
  font-size: 0.9rem;
  background: #ffffff;
  color: #4a5a68;
  box-shadow: 0 8px 20px rgba(15, 35, 34, 0.16);
}

/* ===========================
   ESTADOS
   =========================== */
.loading,
.error,
.no-courses {
  text-align: center;
  padding: 4rem 0;
  color: #6d7a86;
}

.error p {
  margin-bottom: 1.2rem;
}

/* ===========================
   GRID DE CURSOS
   =========================== */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap: 1.9rem;
}

/* CARD CURSO */
.course-card {
  background: #ffffff;
  border-radius: 24px;
  box-shadow:
    0 24px 60px rgba(15, 35, 34, 0.22),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.55);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
  cursor: default;
}

.course-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 28px 70px rgba(8, 32, 26, 0.35);
  border-color: var(--emerald-primary);
}

/* Imagen + precio */
.course-image {
  position: relative;
  height: 170px;
  background: radial-gradient(circle at top, #c5e3da 0, #7ca79b 50%, #29433c 100%);
  overflow: hidden;
}

.course-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  mix-blend-mode: normal;
}

/* Badge de precio */
.course-price {
  position: absolute;
  right: 1rem;
  bottom: 1rem;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  background: rgba(10, 28, 26, 0.9);
  color: #fdfefe;
  font-size: 0.88rem;
  font-weight: 600;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45);
}

/* Contenido */
.course-content {
  padding: 1.3rem 1.3rem 1.1rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.course-title {
  font-size: 1.1rem;
  color: #12222b;
  margin-bottom: 0.4rem;
}

.course-description {
  font-size: 0.9rem;
  color: #6d7a86;
  line-height: 1.6;
  margin-bottom: 1rem;
}

/* Meta (horas/nivel) */
.course-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #4a5a68;
  margin-bottom: 1.1rem;
}

.course-duration,
.course-level {
  display: inline-flex;
  align-items: center;
}

/* Acciones */
.course-actions {
  margin-top: auto;
  display: flex;
  gap: 0.6rem;
}

/* ===========================
   BOTONES REUTILIZADOS
   =========================== */
.btn {
  padding: 0.7rem 1.4rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-decoration: none;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
  flex: 1;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Principal */
.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 22px rgba(8, 32, 26, 0.45);
}

.btn-primary:hover:not(:disabled) {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 14px 30px rgba(5, 22, 18, 0.6);
}

/* Outline */
.btn-outline {
  background: #ffffff;
  color: var(--emerald-dark);
  border: 1px solid rgba(163, 216, 195, 0.8);
  box-shadow: 0 6px 16px rgba(15, 35, 34, 0.16);
}

.btn-outline:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
}

/* Secundario (modal) */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.6);
  box-shadow: 0 6px 16px rgba(15, 35, 34, 0.16);
}

.btn-secondary:hover {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* ===========================
   MODAL PAGO
   =========================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(4, 16, 14, 0.68);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: #ffffff;
  border-radius: 22px;
  padding: 1.9rem 1.6rem;
  max-width: 480px;
  width: 90%;
  box-shadow:
    0 30px 80px rgba(0, 0, 0, 0.55),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.6);
}

/* Header modal */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
  padding-bottom: 0.8rem;
  border-bottom: 1px solid #e4ebf0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.15rem;
  color: #12222b;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.6rem;
  cursor: pointer;
  color: #7a8a98;
  transition: color 0.22s ease, transform 0.22s ease;
}

.close-btn:hover {
  color: var(--emerald-dark);
  transform: scale(1.05);
}

/* Info curso dentro del modal */
.modal-course-info h3 {
  margin: 0 0 0.3rem;
  font-size: 1rem;
  color: #23313f;
}

.modal-course-info .price {
  margin: 0;
  font-size: 0.95rem;
  color: #4a5a68;
}

/* Formulario pago */
.payment-form {
  margin-top: 1.2rem;
}

.payment-methods h4 {
  margin: 0 0 0.6rem;
  font-size: 0.95rem;
  color: #23313f;
}

.method-options {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.1rem;
}

.method-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #4a5a68;
}

.method-option input {
  accent-color: var(--emerald-dark);
}

/* Campos tarjeta */
.card-fields {
  background: #f6f9fb;
  border-radius: 16px;
  padding: 1rem 1rem 0.9rem;
  border: 1px solid rgba(163, 216, 195, 0.6);
  margin-bottom: 1.3rem;
}

.form-group {
  margin-bottom: 0.8rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #23313f;
}

.form-group input {
  width: 100%;
  padding: 0.55rem 0.7rem;
  border-radius: 10px;
  border: 1px solid #dde3ea;
  font-size: 0.9rem;
  box-sizing: border-box;
  background: #ffffff;
  transition: border-color 0.22s ease, box-shadow 0.22s ease;
}

.form-group input:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
}

.form-row {
  display: flex;
  gap: 0.8rem;
}

/* Acciones modal */
.modal-actions {
  display: flex;
  gap: 0.7rem;
  justify-content: flex-end;
  margin-top: 1rem;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .filters {
    border-radius: 18px;
    flex-direction: column;
    align-items: stretch;
  }

  .courses-grid {
    grid-template-columns: 1fr;
  }

  .course-card {
    border-radius: 20px;
  }

  .modal-content {
    padding: 1.5rem 1.3rem;
  }

  .form-row {
    flex-direction: column;
  }

  .modal-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>
