<template>
  <div class="mis-cursos-estudiante">
    <div class="container">
      <div class="header">
        <h1>📚 Mis Cursos</h1>
        <p>Aquí puedes ver todos los cursos en los que te has inscrito</p>
        <router-link to="/dashboard" class="btn-back">← Volver al Dashboard</router-link>
      </div>

      <!-- Estado de carga -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Cargando tus cursos...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <div class="error-content">
          <h2>❌ Error al cargar</h2>
          <p>{{ error }}</p>
          <button @click="cargarMisCursos" class="btn btn-primary">Reintentar</button>
        </div>
      </div>

      <!-- Sin cursos -->
      <div v-else-if="misCursos.length === 0" class="empty-state">
        <div class="empty-content">
          <h2>📖 No tienes cursos todavía</h2>
          <p>Explora nuestros cursos disponibles y comienza a aprender</p>
          <router-link to="/courses" class="btn btn-primary">
            🔍 Ver Cursos Disponibles
          </router-link>
        </div>
      </div>

      <!-- Lista de cursos -->
      <div v-else class="cursos-grid">
        <div
          v-for="inscripcion in misCursos"
          :key="inscripcion.id"
          class="curso-card"
        >
          <div class="curso-header">
            <div class="curso-info">
              <h3>{{ inscripcion.curso.titulo }}</h3>
              <p class="maestro">
                👨‍🏫
                {{ inscripcion.curso.maestro?.nombre || "Instructor no disponible" }}
              </p>
            </div>
            <div class="estado-badge" :class="estadoClass(inscripcion.estado)">
              {{ estadoTexto(inscripcion.estado) }}
            </div>
          </div>

          <div class="curso-content">
            <p class="descripcion">{{ inscripcion.curso.descripcion }}</p>

            <div class="curso-meta">
              <span class="duracion">
                ⏱️ {{ inscripcion.curso.duracion }} horas
              </span>
              <span class="nivel">
                🎯 {{ inscripcion.curso.nivel }}
              </span>
              <span class="categoria">
                📂 {{ inscripcion.curso.categoria }}
              </span>
            </div>

            <!-- Información de progreso -->
            <div class="progreso-info">
              <div class="progreso-bar">
                <div
                  class="progreso-fill"
                  :style="{ width: calcularProgreso(inscripcion) + '%' }"
                ></div>
              </div>
              <span class="progreso-text">
                {{ calcularProgreso(inscripcion) }}% completado
              </span>
            </div>

            <!-- Información del pago -->
            <div
              v-if="inscripcion.pagos && inscripcion.pagos.length > 0"
              class="pago-info"
            >
              <div class="pago-detalle">
                <span class="monto">
                  💰 Pagado:
                  Q{{ (inscripcion.pagos[0].monto_centavos / 100).toFixed(2) }}
                </span>
                <span class="fecha">
                  📅 {{ formatearFecha(inscripcion.created_at) }}
                </span>
              </div>
            </div>
          </div>

          <div class="curso-actions">
            <button
              class="btn btn-primary"
              @click="accederCurso(inscripcion.curso.id)"
              :disabled="!(inscripcion.estado === 'pagado' || inscripcion.estado === 'completado')"
            >
              🎓
              {{
                inscripcion.estado === "pagado" ||
                inscripcion.estado === "completado"
                  ? "Acceder al Curso"
                  : "Pago Pendiente"
              }}
            </button>

            <button class="btn btn-outline" @click="verDetalles(inscripcion)">
              📋 Ver Detalles
            </button>

            <!-- =========================
                 MODAL DETALLES DEL CURSO
                 ========================= -->
            <div
              v-if="mostrarModal"
              class="modal-overlay"
              @click.self="cerrarModal"
            >
              <div class="modal-content modal-detalles-curso">
                <h2 class="modal-title">📋 Detalles del Curso</h2>

                <div class="modal-detalles-grid">
                  <div class="modal-col">
                    <div class="modal-seccion">
                      <h3>👤 Estudiante</h3>
                      <ul>
                        <li>
                          <strong>Nombre:</strong>
                          {{
                            detalleSeleccionado.estudiante?.nombre ||
                            authStore.user?.nombre
                          }}
                        </li>
                        <li>
                          <strong>Correo:</strong>
                          {{
                            detalleSeleccionado.estudiante?.correo ||
                            authStore.user?.correo
                          }}
                        </li>
                        <li
                          v-if="detalleSeleccionado.estudiante?.carrera"
                        >
                          <strong>Carrera:</strong>
                          {{ detalleSeleccionado.estudiante.carrera }}
                        </li>
                        <li v-if="detalleSeleccionado.estudiante?.carnet">
                          <strong>Carnet:</strong>
                          {{ detalleSeleccionado.estudiante.carnet }}
                        </li>
                      </ul>
                    </div>

                    <div class="modal-seccion">
                      <h3>👨‍🏫 Maestro</h3>
                      <ul>
                        <li>
                          <strong>Nombre:</strong>
                          {{ detalleSeleccionado.curso?.maestro?.nombre }}
                        </li>
                        <li
                          v-if="detalleSeleccionado.curso?.maestro?.correo"
                        >
                          <strong>Correo:</strong>
                          {{ detalleSeleccionado.curso.maestro.correo }}
                        </li>
                        <li
                          v-if="detalleSeleccionado.curso?.maestro?.especialidad"
                        >
                          <strong>Especialidad:</strong>
                          {{ detalleSeleccionado.curso.maestro.especialidad }}
                        </li>
                        <li
                          v-if="detalleSeleccionado.curso?.maestro?.biografia"
                        >
                          <strong>Biografía:</strong>
                          {{ detalleSeleccionado.curso.maestro.biografia }}
                        </li>
                      </ul>
                    </div>
                  </div>

                  <div class="modal-col">
                    <div class="modal-seccion">
                      <h3>📚 Curso</h3>
                      <ul>
                        <li>
                          <strong>Nombre:</strong>
                          {{ detalleSeleccionado.curso?.titulo }}
                        </li>
                        <li>
                          <strong>Descripción:</strong>
                          {{ detalleSeleccionado.curso?.descripcion }}
                        </li>
                        <li>
                          <strong>Categoría:</strong>
                          {{ detalleSeleccionado.curso?.categoria }}
                        </li>
                        <li>
                          <strong>Nivel:</strong>
                          {{ detalleSeleccionado.curso?.nivel }}
                        </li>
                        <li>
                          <strong>Duración:</strong>
                          {{ detalleSeleccionado.curso?.duracion }} horas
                        </li>
                      </ul>
                    </div>

                    <div class="modal-seccion">
                      <h3>💳 Inscripción y Pago</h3>
                      <ul>
                        <li>
                          <strong>Estado:</strong>
                          {{ estadoTexto(detalleSeleccionado.estado) }}
                        </li>
                        <li>
                          <strong>Fecha de Inscripción:</strong>
                          {{
                            detalleSeleccionado.created_at
                              ? formatearFecha(detalleSeleccionado.created_at)
                              : "N/A"
                          }}
                        </li>
                        <li>
                          <strong>Fecha de Pago:</strong>
                          {{
                            detalleSeleccionado.pagos &&
                            detalleSeleccionado.pagos[0]
                              ? formatearFecha(
                                  detalleSeleccionado.pagos[0].created_at ||
                                    detalleSeleccionado.created_at
                                )
                              : "N/A"
                          }}
                        </li>
                        <li>
                          <strong>Fecha de Finalización:</strong>
                          {{
                            detalleSeleccionado.updated_at
                              ? formatearFecha(detalleSeleccionado.updated_at)
                              : "N/A"
                          }}
                        </li>
                        <li
                          v-if="
                            detalleSeleccionado.pagos &&
                            detalleSeleccionado.pagos[0]
                          "
                        >
                          <strong>Monto Pagado:</strong>
                          Q{{
                            (
                              detalleSeleccionado.pagos[0].monto_centavos / 100
                            ).toFixed(2)
                          }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="modal-actions">
                  <button class="btn btn-outline modal-btn" @click="cerrarModal">
                    Cerrar
                  </button>

                  <button
                    v-if="detalleSeleccionado.estado === 'completado'"
                    class="btn btn-primary modal-btn modal-btn-full"
                    @click="descargarCertificado(detalleSeleccionado.id)"
                    :disabled="descargandoCert"
                  >
                    <span v-if="!descargandoCert">📄 Descargar Certificado</span>
                    <span v-else class="spinner-mini"></span>
                    <span v-if="descargandoCert"> Descargando...</span>
                  </button>
                </div>
              </div>
            </div>
            <!-- =========================
                 / MODAL DETALLES
                 ========================= -->
          </div>
        </div>
      </div>

      <!-- Estadísticas del estudiante -->
      <div v-if="misCursos.length > 0" class="estadisticas">
        <h2>📊 Mis Estadísticas</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-number">{{ misCursos.length }}</span>
            <span class="stat-label">Cursos Inscritos</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">{{ cursosActivos }}</span>
            <span class="stat-label">Cursos Activos</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">{{ horasTotales }}</span>
            <span class="stat-label">Horas de Estudio</span>
          </div>
          <div class="stat-card">
            <span class="stat-number">Q{{ totalInvertido }}</span>
            <span class="stat-label">Total Invertido</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import apiClient from "../services/api";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

// ESTADO DE DESCARGA
const descargandoCert = ref(false);

// ACCIONES
function accederCurso(cursoId: number) {
  router.push(`/curso/${cursoId}/contenido`);
}

async function descargarCertificado(inscripcionId: number) {
  try {
    descargandoCert.value = true;

    const response = await apiClient.get(
      `/certificados/${inscripcionId}/descargar`,
      {
        responseType: "blob",
      }
    );

    const fileURL = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = fileURL;
    link.setAttribute("download", `certificado_${inscripcionId}.pdf`);
    document.body.appendChild(link);
    link.click();
    window.URL.revokeObjectURL(fileURL);
    document.body.removeChild(link);

    alert("📄 El certificado se descargó correctamente.");
  } catch (error) {
    console.error("❌ Error descargando certificado:", error);
    alert("No se pudo descargar el certificado.");
  } finally {
    descargandoCert.value = false;
  }
}

// MODAL
const mostrarModal = ref(false);
const detalleSeleccionado = ref<any>({});

function verDetalles(inscripcion: any) {
  detalleSeleccionado.value = inscripcion;
  mostrarModal.value = true;
}

function cerrarModal() {
  mostrarModal.value = false;
  detalleSeleccionado.value = {};
}

// DATOS PRINCIPALES
const loading = ref(false);
const error = ref("");
const misCursos = ref<any[]>([]);

const cursosActivos = ref(0);
const horasTotales = ref(0);
const totalInvertido = ref(0);

// LÓGICAS
const calcularProgreso = (inscripcion: any) => {
  if (inscripcion.progreso && inscripcion.progreso.porcentaje !== undefined) {
    return inscripcion.progreso.porcentaje;
  }
  return 0;
};

const estadoClass = (estado: string) => {
  switch (estado) {
    case "pagado":
    case "completado":
      return "success";
    case "pendiente":
      return "warning";
    case "cancelado":
      return "danger";
    default:
      return "secondary";
  }
};

const estadoTexto = (estado: string) => {
  switch (estado) {
    case "pagado":
      return "✅ Activo";
    case "completado":
      return "🏆 Completado";
    case "pendiente":
      return "⏳ Pendiente";
    case "cancelado":
      return "❌ Cancelado";
    default:
      return estado;
  }
};

const formatearFecha = (fecha: string) => {
  return new Date(fecha).toLocaleDateString("es-GT");
};

// CARGAR CURSOS
const cargarMisCursos = async () => {
  loading.value = true;
  try {
    const response = await fetch("/api/mis-cursos", {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        "Content-Type": "application/json",
      },
    });

    const data = await response.json();

    if (response.ok) {
      misCursos.value = data.inscripciones || [];

      cursosActivos.value = misCursos.value.filter(
        (c) => c.estado === "pagado" || c.estado === "completado"
      ).length;

      horasTotales.value = misCursos.value.reduce(
        (acc, c) => acc + (c.curso?.duracion ? Number(c.curso.duracion) : 0),
        0
      );

      totalInvertido.value = misCursos.value.reduce(
        (acc, c) =>
          acc +
          (c.pagos && c.pagos[0] ? c.pagos[0].monto_centavos / 100 : 0),
        0
      );
    } else {
      throw new Error(data.error || "Error al cargar cursos");
    }
  } catch (err: any) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  cargarMisCursos();
});
</script>

<style scoped>
/* ===== MODAL LAYOUT Y ESTILO (NUEVO) ===== */
.modal-detalles-grid {
  display: flex;
  gap: 2.4rem;
  flex-wrap: wrap;
  margin-bottom: 1.4rem;
}

.modal-col {
  flex: 1 1 260px;
  min-width: 240px;
}

.modal-detalles-curso {
  max-width: 820px;
  width: 96%;
}

.modal-seccion {
  margin-bottom: 1.4rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.24);
}

.modal-seccion:last-child {
  border-bottom: none;
}

.modal-seccion h3 {
  margin: 0 0 0.55rem 0;
  font-size: 1.02rem;
  color: #6ee7b7;
  letter-spacing: 0.02em;
}

.modal-seccion ul {
  margin: 0;
  padding: 0 0 0 0.3rem;
}

.modal-seccion li {
  margin-bottom: 0.35rem;
  font-size: 0.95rem;
}

/* Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: radial-gradient(circle at top, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.9));
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

/* Card del modal */
.modal-content {
  background: radial-gradient(circle at top left, #020617 0, #020d10 45%, #020617 100%);
  border-radius: 24px;
  padding: 2.1rem 2.3rem 1.7rem;
  box-shadow:
    0 32px 80px rgba(0, 0, 0, 0.8),
    0 0 0 1px rgba(148, 163, 184, 0.25);
  color: #e5e7eb;
  max-height: 90vh;
  overflow-y: auto;
}

/* Scroll del modal */
.modal-content::-webkit-scrollbar {
  width: 6px;
}
.modal-content::-webkit-scrollbar-track {
  background: transparent;
}
.modal-content::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.7);
  border-radius: 999px;
}

.modal-title {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.35rem;
  font-weight: 600;
  color: #f9fafb;
  text-align: left;
  letter-spacing: 0.04em;
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
  padding-bottom: 0.6rem;
}

/* Acciones del modal */
.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  margin-top: 0.8rem;
}

.modal-btn {
  width: 100%;
}

.modal-btn-full {
  width: 100%;
}

/* Spinner pequeño en botón */
.spinner-mini {
  width: 16px;
  height: 16px;
  border-radius: 999px;
  border: 2px solid rgba(226, 232, 240, 0.4);
  border-top-color: #e5e7eb;
  animation: spin-mini 0.6s linear infinite;
  display: inline-block;
  margin-right: 0.3rem;
  vertical-align: middle;
}

@keyframes spin-mini {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* ===== RESTO DE ESTILOS ORIGINALES ===== */
.mis-cursos-estudiante {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --danger: #e05252;
  --warning: #f7b267;

  min-height: 100vh;
  padding: 2.5rem 0 3.5rem;
  background: radial-gradient(
    circle at top left,
    #eaf6f3 0,
    #d7ece6 40%,
    #c7e2dc 75%,
    #b9d8d2 100%
  );
  font-family: "Poppins", "Roboto", "Arial", sans-serif;
}

.container {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 3rem;
  color: #12222b;
}

.header h1 {
  font-size: 2.2rem;
  margin-bottom: 0.6rem;
  letter-spacing: 0.04em;
}

.header p {
  font-size: 0.98rem;
  opacity: 0.9;
  margin-bottom: 1.1rem;
}

.btn-back {
  display: inline-block;
  padding: 0.65rem 1.4rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  color: #12222b;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.22);
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(10, 28, 24, 0.35);
}

/* ESTADOS GENERALES */
.loading {
  text-align: center;
  padding: 4rem 0;
  color: #1f2e3a;
}

.spinner {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 4px solid rgba(79, 144, 133, 0.2);
  border-top-color: var(--emerald-dark);
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.error-state,
.empty-state {
  text-align: center;
  padding: 3.5rem 0 1rem;
}

.error-content,
.empty-content {
  max-width: 520px;
  margin: 0 auto;
  padding: 2rem 1.8rem;
  border-radius: 20px;
  background: rgba(249, 252, 251, 0.96);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(163, 216, 195, 0.9);
  box-shadow: 0 22px 60px rgba(10, 28, 24, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.9);
  color: #1f2e3a;
}

.error-content h2,
.empty-content h2 {
  margin-bottom: 0.7rem;
  font-size: 1.4rem;
}

.error-content p,
.empty-content p {
  font-size: 0.95rem;
  opacity: 0.9;
  margin-bottom: 1.3rem;
}

/* GRID DE CURSOS */
.cursos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1.9rem;
  margin-bottom: 3rem;
}

.curso-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 22px;
  padding: 1.8rem 1.6rem 1.6rem;
  box-shadow: 0 24px 65px rgba(10, 28, 24, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(175, 219, 203, 0.9);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.curso-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 30px 80px rgba(5, 15, 13, 0.9), 0 0 0 1px rgba(255, 255, 255, 1);
}

.curso-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.3rem;
}

.curso-info h3 {
  margin: 0 0 0.4rem;
  color: #12222b;
  font-size: 1.25rem;
}

.maestro {
  margin: 0;
  color: #60707e;
  font-size: 0.9rem;
}

/* BADGES DE ESTADO */
.estado-badge {
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}

.estado-badge.success {
  background: rgba(79, 144, 133, 0.12);
  color: var(--emerald-dark);
  border: 1px solid rgba(79, 144, 133, 0.7);
}

.estado-badge.warning {
  background: rgba(247, 178, 103, 0.12);
  color: #ad651e;
  border: 1px solid rgba(247, 178, 103, 0.8);
}

.estado-badge.danger {
  background: rgba(224, 82, 82, 0.09);
  color: #b02e2e;
  border: 1px solid rgba(224, 82, 82, 0.85);
}

.estado-badge.secondary {
  background: rgba(163, 177, 193, 0.2);
  color: #435161;
  border: 1px solid rgba(163, 177, 193, 0.8);
}

/* CONTENIDO DEL CURSO */
.descripcion {
  color: #4d5a65;
  margin-bottom: 1.1rem;
  font-size: 0.9rem;
  line-height: 1.6;
}

.curso-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.2rem;
}

.curso-meta span {
  font-size: 0.78rem;
  color: #455463;
  background: #edf3f1;
  padding: 0.35rem 0.8rem;
  border-radius: 999px;
}

/* PROGRESO */
.progreso-info {
  margin-bottom: 1.1rem;
}

.progreso-bar {
  width: 100%;
  height: 8px;
  border-radius: 999px;
  background: #e0e7eb;
  overflow: hidden;
  margin-bottom: 0.35rem;
}

.progreso-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--emerald-dark), var(--emerald-primary));
  transition: width 0.3s ease;
}

.progreso-text {
  font-size: 0.78rem;
  color: #60707e;
}

/* PAGO */
.pago-info {
  background: linear-gradient(120deg, #e5f7ef, #f0faf6);
  border-radius: 14px;
  padding: 0.75rem 0.9rem;
  border-left: 4px solid var(--emerald-primary);
  margin-bottom: 1.25rem;
}

.pago-detalle {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #32414d;
}

.monto {
  font-weight: 600;
}

.fecha {
  color: #7a8a95;
}

/* ACCIONES */
.curso-actions {
  display: flex;
  gap: 0.7rem;
}

.btn {
  flex: 1;
  padding: 0.7rem 1.4rem;
  border-radius: 999px;
  border: none;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  text-decoration: none;
  transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease,
    color 0.18s ease, border-color 0.18s ease;
}

.btn:disabled {
  opacity: 0.6;
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
  box-shadow: 0 20px 55px rgba(5, 15, 13, 0.9);
}

.btn-outline {
  background: transparent;
  color: var(--emerald-dark);
  border: 2px solid rgba(79, 144, 133, 0.8);
}

.btn-outline:hover {
  background: var(--emerald-dark);
  color: #ffffff;
  box-shadow: 0 16px 35px rgba(6, 22, 18, 0.7);
}

/* ESTADÍSTICAS */
.estadisticas {
  background: rgba(249, 252, 251, 0.96);
  border-radius: 22px;
  padding: 1.9rem 1.7rem;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(163, 216, 195, 0.9);
  box-shadow: 0 24px 65px rgba(10, 28, 24, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.95);
  color: #12222b;
}

.estadisticas h2 {
  text-align: center;
  margin-bottom: 1.7rem;
  font-size: 1.4rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.3rem;
}

.stat-card {
  padding: 1.2rem 1rem;
  border-radius: 18px;
  text-align: center;
  background: radial-gradient(
    circle at top left,
    #f9fdfa 0,
    #e7f2ec 45%,
    #d8e7e0 100%
  );
  box-shadow: 0 18px 40px rgba(10, 28, 24, 0.35);
}

.stat-number {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 0.3rem;
}

.stat-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: #5b6a76;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .cursos-grid {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1.4rem;
  }

  .header h1 {
    font-size: 1.9rem;
  }

  .curso-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .curso-actions,
  .modal-actions {
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .curso-card {
    padding: 1.5rem 1.3rem;
  }

  .estadisticas {
    padding: 1.6rem 1.3rem;
  }
}
</style>
