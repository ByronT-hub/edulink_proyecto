<template>
  <div class="courses-by-teacher">
    <div class="container">
      <div class="page-header">
        <button class="btn btn-outline volver-btn" @click="router.push('/dashboard')" style="margin-bottom: 1.2rem; float:left;">
          ← Volver
        </button>
        <h1>👨‍🏫 Cursos por Maestro</h1>
        <p>
          Visualiza los cursos asignados a cada instructor dentro de la plataforma EduLink.
        </p>
      </div>
      

      <!-- Estado de carga -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando maestros y sus cursos...</p>
      </div>

      <!-- Sin maestros / respuesta vacía -->
      <div v-else-if="maestros.length === 0" class="empty-state">
        <div class="empty-card">
          <h2>🙈 No se encontraron maestros</h2>
          <p>
            Aún no hay maestros registrados o asociados a la vista de administración.
          </p>
        </div>
      </div>

      <!-- Lista de maestros con sus cursos -->
      <div v-else class="teachers-grid">
        <div
          v-for="maestro in maestros"
          :key="maestro.id"
          class="teacher-card"
        >
          <div class="teacher-header">
            <div class="avatar">
              {{ maestro.nombre?.charAt(0).toUpperCase() || 'M' }}
            </div>
            <div class="teacher-info">
              <h2>{{ maestro.nombre }}</h2>
              <p class="email">{{ maestro.correo }}</p>
              <span class="badge">
                {{ maestro.cursos?.length || 0 }} curso(s)
              </span>
            </div>
          </div>

          <div class="teacher-courses">
            <h3>Cursos asignados</h3>

            <ul v-if="maestro.cursos && maestro.cursos.length" class="courses-list">
              <li
                v-for="curso in maestro.cursos"
                :key="curso.id"
                class="course-row"
              >
                <div class="course-main">
                  <span class="course-title">{{ curso.titulo }}</span>
                  <p class="course-description">
                    {{ curso.descripcion }}
                  </p>
                </div>
              </li>
            </ul>

            <div
              v-else
              class="no-courses"
            >
              <span>Sin cursos asignados</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const maestros = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  const token = localStorage.getItem('edulink_token')

  const res = await axios.get('/api/admin/maestros', {
    headers: { Authorization: `Bearer ${token}` }
  })

  const maestrosData = res.data

  // Para cada maestro, obtener sus cursos
  for (const maestro of maestrosData) {
    const cursosRes = await axios.get(`/api/admin/cursos/maestro/${maestro.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    maestro.cursos = cursosRes.data
  }

  maestros.value = maestrosData
  loading.value = false
})
</script>

<style scoped>
.courses-by-teacher {
  min-height: 100vh;
  padding: 2.5rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
}

.container {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* HEADER */
.page-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.page-header h1 {
  font-size: 2.4rem;
  margin-bottom: 0.75rem;
  color: #12222b;
  letter-spacing: 0.04em;
}

.page-header p {
  margin: 0;
  font-size: 0.98rem;
  color: #5f6d78;
  opacity: 0.9;
}

/* LOADING */
.loading-state {
  text-align: center;
  padding: 4rem 0;
  color: #12222b;
}

.spinner {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  border: 3px solid rgba(37, 92, 80, 0.18);
  border-top: 3px solid #4f9085;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* EMPTY */
.empty-state {
  display: flex;
  justify-content: center;
  padding: 3rem 0;
}

.empty-card {
  max-width: 520px;
  width: 100%;
  padding: 2rem;
  border-radius: 22px;
  background: rgba(249, 252, 251, 0.96);
  border: 1px solid rgba(175, 219, 203, 0.9);
  backdrop-filter: blur(20px);
  text-align: center;
  box-shadow:
    0 18px 55px rgba(7, 26, 22, 0.65),
    0 0 0 1px rgba(255, 255, 255, 0.98);
}

.empty-card h2 {
  margin-bottom: 0.75rem;
  color: #12222b;
}

.empty-card p {
  margin: 0;
  color: #5f6d78;
}

/* GRID DE MAESTROS */
.teachers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 1.8rem;
}

/* TARJETA DE MAESTRO */
.teacher-card {
  background: rgba(249, 252, 251, 0.98);
  border-radius: 22px;
  padding: 1.75rem 1.75rem 1.9rem;
  border: 1px solid rgba(175, 219, 203, 0.9);
  backdrop-filter: blur(18px);
  box-shadow:
    0 24px 70px rgba(7, 26, 22, 0.72),
    0 0 0 1px rgba(255, 255, 255, 0.98);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
}

.teacher-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 30px 90px rgba(5, 18, 15, 0.95),
    0 0 0 1px rgba(255, 255, 255, 1);
  border-color: rgba(115, 182, 163, 0.9);
}

/* HEADER DE MAESTRO */
.teacher-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f9085, #355f57);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.5rem;
  box-shadow: 0 14px 32px rgba(7, 26, 22, 0.6);
}

.teacher-info h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #12222b;
}

.teacher-info .email {
  margin: 0.1rem 0 0.3rem;
  font-size: 0.9rem;
  color: #5f6d78;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  background: #e4f1ed;
  color: #355f57;
  font-size: 0.78rem;
  font-weight: 600;
}

/* CURSOS DEL MAESTRO */
.teacher-courses h3 {
  margin: 0 0 0.8rem;
  font-size: 1rem;
  color: #12222b;
}

.courses-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.course-row {
  padding: 0.75rem 0.85rem;
  border-radius: 14px;
  background: #f4faf7;
  border: 1px solid rgba(208, 230, 222, 0.9);
  margin-bottom: 0.75rem;
}

.course-main {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.course-title {
  font-weight: 600;
  font-size: 0.95rem;
  color: #12222b;
}

.course-description {
  margin: 0;
  font-size: 0.85rem;
  color: #5f6d78;
  line-height: 1.5;
}

.no-courses {
  padding: 0.9rem 1rem;
  border-radius: 14px;
  background: #fff8f1;
  border: 1px solid #fed7aa;
  font-size: 0.88rem;
  color: #9a6118;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .container {
    padding: 0 1.5rem;
  }
}

@media (max-width: 600px) {
  .courses-by-teacher {
    padding: 2rem 0 3rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .teachers-grid {
    grid-template-columns: minmax(0, 1fr);
  }
}
</style>
