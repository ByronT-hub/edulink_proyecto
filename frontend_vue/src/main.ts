import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import pinia from './stores'
import './style.css'
import App from './App.vue'

// Importar componentes para las rutas
import Home from './views/Home.vue'
import Courses from './views/Courses.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import Dashboard from './views/Dashboard.vue'
import CourseDetail from './views/CourseDetail.vue'
import Payment from './views/Payment.vue'
import Certificates from './views/Certificates.vue'

// Vistas específicas del Maestro
import MisCursos from './views/MisCursos.vue'
import CrearCurso from './views/CrearCurso.vue'
import BuscarEstudiantes from './views/BuscarEstudiantes.vue'
import MiPerfilMaestro from './views/MiPerfilMaestro.vue'

// Vistas específicas del Estudiante
import MisCursosEstudiante from './views/MisCursosEstudiante.vue'
import MiPerfilEstudiante from './views/MiPerfilEstudiante.vue'

// Configurar rutas
const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/courses', name: 'courses', component: Courses },
  { path: '/course/:id', name: 'course-detail', component: CourseDetail, props: true },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/payment/:enrollmentId', name: 'payment', component: Payment, props: true, meta: { requiresAuth: true } },
  { path: '/certificates', name: 'certificates', component: Certificates, meta: { requiresAuth: true } },
  
  // Rutas específicas del Maestro
  { path: '/maestro/mis-cursos', name: 'mis-cursos', component: MisCursos, meta: { requiresAuth: true, requiresRole: 'maestro' } },
  { path: '/maestro/crear-curso', name: 'crear-curso', component: CrearCurso, meta: { requiresAuth: true, requiresRole: 'maestro' } },
  { path: '/maestro/buscar-estudiantes', name: 'buscar-estudiantes', component: BuscarEstudiantes, meta: { requiresAuth: true, requiresRole: 'maestro' } },
  { path: '/maestro/mi-perfil', name: 'mi-perfil-maestro', component: MiPerfilMaestro, meta: { requiresAuth: true, requiresRole: 'maestro' } },
  
  // Rutas específicas del Estudiante
  { path: '/estudiante/mis-cursos', name: 'mis-cursos-estudiante', component: MisCursosEstudiante, meta: { requiresAuth: true, requiresRole: 'estudiante' } },
  { path: '/estudiante/mi-perfil', name: 'mi-perfil-estudiante', component: MiPerfilEstudiante, meta: { requiresAuth: true, requiresRole: 'estudiante' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guard para rutas protegidas
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresRole = to.matched.find(record => record.meta.requiresRole)?.meta.requiresRole
  const isAuthenticated = localStorage.getItem('edulink_token')
  
  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (requiresRole) {
    // Verificar rol del usuario
    const userRole = JSON.parse(localStorage.getItem('edulink_user') || '{}')?.role
    if (userRole !== requiresRole) {
      next('/dashboard') // Redirigir al dashboard si no tiene el rol correcto
    } else {
      next()
    }
  } else {
    next()
  }
})

const app = createApp(App)

app.use(pinia)
app.use(router)

app.mount('#app')