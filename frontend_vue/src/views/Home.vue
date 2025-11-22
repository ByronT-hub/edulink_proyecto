<template>
  <div class="home">
    <section class="hero">
      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">Bienvenido a EduLink</h1>
          <p class="hero-subtitle">
            La plataforma líder en educación online con certificaciones verificables
          </p>
          <div class="hero-actions">
            <router-link to="/courses" class="btn btn-primary btn-large">
              Explorar Cursos
            </router-link>
            <router-link
              v-if="!isAuthenticated"
              to="/register"
              class="btn btn-outline btn-large"
            >
              Registrarse Gratis
            </router-link>
          </div>
        </div>
        <div class="hero-image">
          <div class="hero-illustration">
            📚
          </div>
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
/* ===========================
   PALETA & BASE DE DISEÑO
   =========================== */
.home {
  /* Paleta esmeralda suave */
  --emerald-primary: #4f9085;      /* Verde esmeralda sutil */
  --emerald-dark: #3a6f66;         /* Versión más profunda para botones */
  --emerald-soft: #e4f1ed;         /* Fondo muy suave */
  --neutral-background: #f6f8fa;   /* Fondo general */
  --neutral-dark: #23313f;         /* Texto principal */
  --accent-highlight: #a3d8c3;     /* Acento claro */
  --border-radius-primary: 18px;

  min-height: 100vh;
  background: var(--neutral-background);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
  color: var(--neutral-dark);
}

/* ===========================
   HERO (full height elegante)
   =========================== */
.hero {
  position: relative;
  background: radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  padding: 5rem 0 4rem;
  min-height: calc(100vh - 80px); /* casi pantalla completa, dejando espacio por si hay navbar */
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 10% 20%, rgba(79, 144, 133, 0.18), transparent 55%),
              radial-gradient(circle at 80% 80%, rgba(163, 216, 195, 0.35), transparent 60%);
  opacity: 0.9;
  pointer-events: none;
}

.hero-content {
  position: relative; /* para estar encima del overlay */
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
  gap: 3.5rem;
  align-items: center;
}

.hero-text {
  max-width: 620px;
}

.hero-title {
  font-size: 3.1rem;
  line-height: 1.15;
  margin-bottom: 1rem;
  color: #12222b;
  letter-spacing: 0.02em;
}

.hero-subtitle {
  font-size: 1.2rem;
  margin-bottom: 2.3rem;
  opacity: 0.9;
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

/* Tarjeta/ilustración del lado derecho */
.hero-image {
  display: flex;
  justify-content: center;
}

.hero-illustration {
  width: 260px;
  height: 260px;
  border-radius: 28px;
  background: linear-gradient(145deg, rgba(79, 144, 133, 0.15), rgba(255, 255, 255, 0.9));
  box-shadow:
    0 22px 60px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 5rem;
}

/* Tamaño botones hero */
.btn-large {
  padding: 0.95rem 2.2rem;
  font-size: 1.02rem;
}

/* ===========================
   FEATURES (cards elegantes)
   =========================== */
.features {
  padding: 5.5rem 0 5rem;
  background: var(--neutral-background);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-title {
  text-align: center;
  font-size: 2.4rem;
  margin-bottom: 3.5rem;
  color: #1c2936;
  letter-spacing: 0.03em;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 2.3rem;
}

.feature-card {
  background: #ffffff;
  padding: 2.2rem 2rem;
  border-radius: var(--border-radius-primary);
  text-align: left;
  box-shadow: 0 16px 40px rgba(15, 35, 34, 0.07);
  border: 1px solid rgba(163, 216, 195, 0.5);
  position: relative;
  overflow: hidden;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.feature-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 144, 133, 0.06), transparent 55%);
  opacity: 0;
  transition: opacity 0.25s ease;
}

.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 45px rgba(8, 32, 26, 0.18);
  border-color: var(--emerald-primary);
}

.feature-card:hover::before {
  opacity: 1;
}

.feature-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: var(--emerald-soft);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: var(--emerald-dark);
  margin-bottom: 1.4rem;
}

.feature-card h3 {
  font-size: 1.4rem;
  margin-bottom: 0.8rem;
  color: #1f2e3a;
}

.feature-card p {
  color: #6d7a86;
  line-height: 1.7;
  font-size: 0.98rem;
}

/* ===========================
   STATS (bloque contrastado)
   =========================== */
.stats {
  padding: 5rem 0;
  background: radial-gradient(circle at top, #2c3e50 0, #182531 55%, #101820 100%);
  color: #f9fbff;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 2rem;
  align-items: stretch;
}

.stat-item {
  text-align: center;
  padding: 1.8rem 1.2rem;
  border-radius: 18px;
  background: rgba(10, 23, 30, 0.75);
  border: 1px solid rgba(163, 216, 195, 0.4);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
}

.stat-number {
  font-size: 2.8rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
  color: var(--accent-highlight);
}

.stat-label {
  font-size: 1.02rem;
  opacity: 0.9;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

/* ===========================
   CTA (llamada a la acción)
   =========================== */
.cta {
  padding: 5rem 0 5.5rem;
  background: linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary));
  color: #ffffff;
}

.cta-content {
  text-align: center;
  max-width: 700px;
  margin: 0 auto;
}

.cta-content h2 {
  font-size: 2.5rem;
  margin-bottom: 0.9rem;
  letter-spacing: 0.03em;
}

.cta-content p {
  font-size: 1.1rem;
  margin-bottom: 2.2rem;
  opacity: 0.95;
}

/* ===========================
   BOTONES
   =========================== */
.btn {
  padding: 0.8rem 1.9rem;
  border-radius: 999px;
  text-decoration: none;
  cursor: pointer;
  font-size: 0.98rem;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  font-weight: 600;
  transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease, color 0.22s ease,
    border-color 0.22s ease;
}

.btn-primary {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 10px 25px rgba(16, 52, 46, 0.35);
}

.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-2px);
  box-shadow: 0 16px 35px rgba(8, 32, 26, 0.45);
}

.btn-outline {
  background: transparent;
  color: #ffffff;
  border: 1.5px solid rgba(255, 255, 255, 0.75);
}

.btn-outline:hover {
  background: #ffffff;
  color: var(--emerald-dark);
  transform: translateY(-2px);
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 992px) {
  .hero-content {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .hero-text {
    margin: 0 auto;
  }

  .hero-image {
    margin-top: 2.5rem;
  }

  .hero-illustration {
    width: 220px;
    height: 220px;
    font-size: 4.2rem;
  }

  .hero-title {
    font-size: 2.5rem;
  }

  .hero-subtitle {
    font-size: 1.05rem;
  }
}

@media (max-width: 600px) {
  .hero {
    padding: 4rem 0 3rem;
    min-height: auto;
  }

  .section-title {
    font-size: 2rem;
  }

  .cta-content h2 {
    font-size: 2rem;
  }

  .cta-content p {
    font-size: 1rem;
  }

  .btn-large {
    width: 100%;
    justify-content: center;
  }
}
</style>
