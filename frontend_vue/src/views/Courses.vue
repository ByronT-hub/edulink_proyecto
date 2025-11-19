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

      <div class="course-info" v-if="selectedCourse">
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

// ==========================
// ⚠ CORREGIDO AQUÍ ⚠
// ==========================
const procesarPago = async () => {

  if (!selectedCourse.value) return;

  enrolling.value = selectedCourse.value.id;

  const payload = {
    merchant_ref: `curso-${selectedCourse.value.id}`,
    amount_cents: Math.round(Number(selectedCourse.value.precio) * 100),
    currency: "GTQ",
    card: {
      holder_name: cardData.value.nombre_titular,
      pan: cardData.value.numero_tarjeta,
      exp_mm: cardData.value.fecha_expiracion.split("/")[0],
      exp_yy: cardData.value.fecha_expiracion.split("/")[1],
      ccv: cardData.value.cvv,
    }
  };

  try {
    // ⭐ CORREGIDO → siempre localhost
    const res = await fetch("http://127.0.0.1:5055/api/tarjetas/autorizar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!data.approved) {
      alert("❌ Pago rechazado: " + (data.message || "No autorizado"));
      enrolling.value = null;
      return;
    }

    alert("✔ Pago aprobado\nCódigo: " + data.auth_code);
    showPaymentModal.value = false;

  } catch (err: any) {
    alert("Error: " + err.message);
  }

  enrolling.value = null;
}

const fetchCourses = async () => {
  loading.value = true
  try {
    const res = await fetch("http://localhost:8000/api/cursos")
    courses.value = await res.json()
  } catch (e) {
    error.value = "No se pudieron cargar los cursos"
  }
  loading.value = false
}

onMounted(fetchCourses)
</script>

<style scoped>
/* NO TOQUÉ NADA DEL DISEÑO */
</style>
