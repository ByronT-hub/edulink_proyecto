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
/* Paleta de Colores Esmeralda Sutil y Apagado */
:root {
  /* Verde Esmeralda sutil, tirando más a un verde grisáceo o bosque claro */
  --emerald-primary: #558B84; 
  /* Tono más oscuro para hover y botones principales */
  --emerald-dark: #3E6C66;     
  /* Fondo muy claro, casi blanco */
  --neutral-background: #F8F9FB; 
  /* Texto principal/fondo oscuro (azul medianoche) */
  --neutral-dark: #34495e;     
  /* Acento muy claro y suave */
  --accent-highlight: #A3D8C3; 
  --border-radius-primary: 16px; 
}

/* --- ESTILO GLOBAL DE FUENTE --- */
.home {
  width: 100%;
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif; 
}

/* --- TIPOGRAFÍA DE ENCABEZADOS Y TITULOS --- */
.hero-title, .section-title, .cta-content h2 {
  font-weight: 700; 
}

/* --- SECCIÓN HERO (Banner Principal) --- */
.hero {
  /* Aplicación de la paleta sutil */
  background: linear-gradient(135deg, var(--emerald-primary) 0%, #7CB8B0 100%);
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
  font-size: 3.2rem; 
  margin-bottom: 1rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.35rem; 
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

/* --- SECCIÓN FEATURES (Características) --- */
.features {
  padding: 6rem 0; 
  background: var(--neutral-background); /* Fondo extra claro */
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-title {
  text-align: center;
  font-size: 2.8rem; 
  margin-bottom: 4rem; 
  color: var(--neutral-dark); /* Texto oscuro */
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2.5rem; 
}

.feature-card {
  background: white;
  padding: 2.5rem; 
  border-radius: var(--border-radius-primary); 
  text-align: center;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08); 
  /* Línea de acento muy sutil */
  border-bottom: 4px solid var(--accent-highlight); 
  transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
}

.feature-card:hover {
  transform: translateY(-7px); 
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
  border-color: var(--emerald-primary); /* El hover trae el color principal */
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--emerald-primary); /* Iconos con el tono principal sutil */
}

.feature-card h3 {
  font-size: 1.6rem; 
  margin-bottom: 1rem;
  color: var(--neutral-dark);
}

.feature-card p {
  color: #6c757d;
  line-height: 1.7; 
}

/* --- SECCIÓN STATS (Estadísticas) --- */
.stats {
  padding: 6rem 0;
  background: var(--neutral-dark); /* Fondo oscuro (azul medianoche) */
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
  font-size: 3.5rem; 
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: var(--accent-highlight); /* Acento muy claro y sutil */
}

.stat-label {
  font-size: 1.2rem;
  opacity: 0.9;
}

/* --- SECCIÓN CTA (Llamada a la Acción) --- */
.cta {
  padding: 6rem 0;
  background: var(--emerald-primary); /* El CTA usa el color principal sutil */
  color: white;
}

.cta-content {
  text-align: center;
}

.cta-content h2 {
  font-size: 3rem; 
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.4rem; 
  margin-bottom: 2.5rem; 
  opacity: 0.9;
}

/* --- ESTILOS DE BOTONES --- */
.btn {
  padding: 0.8rem 1.8rem; 
  border: none;
  border-radius: 50px; /* Estilo píldora */
  text-decoration: none;
  cursor: pointer;
  font-size: 1.1rem;
  transition: all 0.3s;
  display: inline-block;
  text-align: center;
  font-weight: 600; 
}

.btn-primary {
  background: var(--emerald-dark); /* Botón principal más oscuro para buen contraste */
  color: white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); 
}

.btn-primary:hover {
  background: var(--emerald-primary); /* Se aclara ligeramente en hover */
  transform: translateY(-3px); 
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.btn-outline {
  background: transparent;
  color: white;
  border: 2px solid white;
}

.btn-outline:hover {
  background: white;
  color: var(--emerald-dark); 
}

/* --- MEDIA QUERIES (Responsividad) --- */
@media (max-width: 768px) {
  .hero-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.2rem;
  }
  
  .section-title {
    font-size: 2.2rem;
    margin-bottom: 3rem;
  }
  
  .cta-content h2 {
    font-size: 2.2rem;
  }
  
  .cta-content p {
    font-size: 1.2rem;
  }
}
</style>