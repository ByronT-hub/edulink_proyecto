<template>
  <div class="home">
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Bienvenido a EduLink</h1>
        <p class="hero-subtitle">
          La plataforma líder en educación online con certificaciones verificables
        </p>
        <div class="hero-actions">
          <router-link to="/courses" class="btn btn-primary btn-large">
            Explorar Cursos
          </router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="btn btn-outline btn-large">
            Registrarse Gratis
          </router-link>
        </div>
      </div>
      <div class="hero-image">
        <div class="hero-illustration">
          📚
        </div>
      </div>
    </section>

    <section class="features">
      <div class="container">
        <h2 class="section-title">¿Por qué elegir EduLink?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">🎓</div>
            <h3>Cursos de Calidad</h3>
            <p>Contenido creado por expertos en la industria con metodologías probadas</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📜</div>
            <h3>Certificados Verificables</h3>
            <p>Obtén certificados con códigos QR únicos para verificar tu aprendizaje</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Pagos Seguros</h3>
            <p>Sistema de pagos integrado y seguro para inscripciones sin complicaciones</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">🚀</div>
            <h3>Aprende a tu Ritmo</h3>
            <p>Acceso 24/7 a todo el contenido desde cualquier dispositivo</p>
          </div>
        </div>
      </div>
    </section>

    <section class="stats">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item">
            <h3 class="stat-number">{{ totalCourses }}</h3>
            <p class="stat-label">Cursos Disponibles</p>
          </div>
          <div class="stat-item">
            <h3 class="stat-number">1000+</h3>
            <p class="stat-label">Estudiantes Activos</p>
          </div>
          <div class="stat-item">
            <h3 class="stat-number">95%</h3>
            <p class="stat-label">Satisfacción</p>
          </div>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="container">
        <div class="cta-content">
          <h2>¿Listo para comenzar tu aprendizaje?</h2>
          <p>Únete a miles de estudiantes que ya están transformando sus carreras</p>
          <router-link to="/courses" class="btn btn-primary btn-large">
            Comenzar Ahora
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useStudentStore } from '@/stores/student'
import { useCoursesStore } from '@/stores/courses'

const studentStore = useStudentStore()
const coursesStore = useCoursesStore()

const isAuthenticated = computed(() => studentStore.isAuthenticated())
const totalCourses = computed(() => coursesStore.courses.length)

onMounted(async () => {
  try {
    await coursesStore.fetchCourses()
  } catch (error) {
    console.error('Error al cargar cursos:', error)
  }
})
</script>

<style scoped>
.home {
  width: 100%;
}

.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4rem 0;
  display: flex;
  align-items: center;
  min-height: 500px;
}

.hero-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.hero-title {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.9;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.hero-illustration {
  font-size: 10rem;
  text-align: center;
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

.features {
  padding: 4rem 0;
  background: #f8f9fa;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 3rem;
  color: #2c3e50;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #2c3e50;
}

.feature-card p {
  color: #6c757d;
  line-height: 1.6;
}

.stats {
  padding: 4rem 0;
  background: #2c3e50;
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #ff6b6b;
}

.stat-label {
  font-size: 1.1rem;
  opacity: 0.9;
}

.cta {
  padding: 4rem 0;
  background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
  color: white;
}

.cta-content {
  text-align: center;
}

.cta-content h2 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
  display: inline-block;
  text-align: center;
  font-weight: 500;
}

.btn-primary {
  background: #ff6b6b;
  color: white;
}

.btn-primary:hover {
  background: #ee5a5a;
  transform: translateY(-2px);
}

.btn-outline {
  background: transparent;
  color: white;
  border: 2px solid white;
}

.btn-outline:hover {
  background: white;
  color: #667eea;
}

@media (max-width: 768px) {
  .hero-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1.1rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .cta-content h2 {
    font-size: 2rem;
  }
}
</style>