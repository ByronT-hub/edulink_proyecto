<template>
  <div class="certificates">
    <div class="container">
      <div class="certificates-header">
        <h1>Mis Certificados</h1>
        <p>Visualiza y descarga tus certificados de cursos completados</p>
      </div>

      <div v-if="loading" class="loading">
        <p>Cargando certificados...</p>
      </div>

      <div v-else-if="userCertificates.length === 0" class="empty-state">
        <div class="empty-illustration">📜</div>
        <h2>No tienes certificados aún</h2>
        <p>Completa cursos para obtener certificados verificables</p>
        <router-link to="/courses" class="btn btn-primary">
          Explorar Cursos
        </router-link>
      </div>

      <div v-else class="certificates-content">
        <div class="certificates-stats">
          <div class="stat-card">
            <div class="stat-icon">📜</div>
            <div class="stat-info">
              <h3>{{ userCertificates.length }}</h3>
              <p>Certificados Totales</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3>{{ validCertificates.length }}</h3>
              <p>Certificados Válidos</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
              <h3>{{ recentCertificates }}</h3>
              <p>Este Mes</p>
            </div>
          </div>
        </div>

        <div class="certificates-grid">

          <!-- 🔥 AQUÍ ESTABA EL ERROR: ahora recorre bien -->
          <div 
            v-for="certificate in userCertificates" 
            :key="certificate.id"
            class="certificate-card"
            :class="{ 'invalid': certificate.valido === false }"

          >
            <div class="certificate-header">
              <div class="certificate-status">
                <span v-if="certificate.valido" class="status-badge valid">
                  ✅ Válido
                </span>
                <span v-else class="status-badge invalid">
                  ❌ Inválido
                </span>
              </div>
              <div class="certificate-date">
                {{ formatDate(certificate.fecha_emision) }}
              </div>
            </div>

            <div class="certificate-content">
              <div class="certificate-icon">🏆</div>

              <!-- 🔥 YA FUNCIONA: curso desde inscripcion -->
              <h3 class="certificate-title">
                {{ certificate.inscripcion?.curso?.titulo || 'Curso' }}
              </h3>

              <p class="certificate-recipient">
                {{ certificate.inscripcion?.estudiante?.nombre || 'Estudiante' }}
              </p>

              <div class="certificate-details">
                <div class="detail-item">
                  <span class="detail-label">Fecha de emisión:</span>
                  <span class="detail-value">{{ formatDate(certificate.fecha_emision) }}</span>
                </div>

                <div class="detail-item">
                  <span class="detail-label">Código:</span>
                  <span class="detail-value code">{{ certificate.codigo }}</span>
                </div>
              </div>

              <div class="qr-code-section">
                <QRCodeVue
                  :value="`https://edulink.uvg/certificates/verify/${certificate.codigo}`"
                  :size="120"
                  :level="'M'"
                  :bgColor="'#ecf1f5'"
                  :fgColor="'#23313f'"
                  style="margin: 0 auto; display: block; border-radius: 8px; background: #ecf1f5; padding: 8px;"
                />
                <small style="display:block; text-align:center; margin-top:0.5rem; font-size:0.8rem; color:#6d7a86;">{{ certificate.codigo }}</small>
              </div>
            </div>

            <div class="certificate-actions" style="justify-content: center;">
              <button 
                @click="downloadCertificate(certificate.inscripcion_id)"
                :disabled="downloading === certificate.inscripcion_id"
                class="btn btn-primary"
                style="margin: 0 auto; min-width: 180px; display: block;"
              >
                {{ downloading === certificate.inscripcion_id ? 'Descargando...' : '📥 Descargar PDF' }}
              </button>
            </div>
          </div>

        </div>

        <div class="verify-section">
          <h2>Verificar Certificado</h2>
          <p>Ingresa un código QR para verificar la autenticidad</p>

          <form @submit.prevent="handleVerification" class="verify-form">
            <div class="form-group">
              <input
                v-model="verifyCode"
                type="text"
                placeholder="Ingresa el código"
                class="form-input"
                :disabled="verifying"
              />
            </div>

            <button 
              type="submit" 
              :disabled="!verifyCode || verifying"
              class="btn btn-primary"
            >
              {{ verifying ? 'Verificando...' : 'Verificar' }}
            </button>
          </form>

          <div v-if="verificationResult" class="verification-result">
            <div v-if="verificationResult.valido" class="verification-success">
              <h3>✅ Certificado Válido</h3>
              <p><strong>Curso:</strong> {{ verificationResult.curso?.titulo }}</p>
              <p><strong>Estudiante:</strong> {{ verificationResult.estudiante?.nombre }}</p>
            </div>

            <div v-else class="verification-error">
              <h3>❌ Certificado Inválido</h3>
              <p>Este certificado no existe o fue revocado.</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useCertificateStore } from '@/stores/certificate'
import { useStudentStore } from '@/stores/student'
import QRCodeVue from 'qrcode.vue'

const certificateStore = useCertificateStore()
const studentStore = useStudentStore()

const downloading = ref<number | null>(null)
const verifying = ref(false)
const verifyCode = ref('')
const verificationResult = ref<any>(null)

const loading = computed(() => certificateStore.loading)

const userCertificates = computed(() =>
  studentStore.student
    ? certificateStore.certificatesByStudent(studentStore.student.id)
    : []
)

const validCertificates = computed(() =>
  userCertificates.value.filter(cert => cert.valido)
)

const recentCertificates = computed(() => {
  const now = new Date()
  return userCertificates.value.filter(cert => {
    const d = new Date(cert.fecha_emision)
    return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear()
  }).length
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-GT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const downloadCertificate = async (inscripcionId: number) => {
  downloading.value = inscripcionId
  try {
    const blob = await certificateStore.downloadCertificate(inscripcionId)
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `certificado_${inscripcionId}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } finally {
    downloading.value = null
  }
}

const verifyCertificate = async (codigo: string) => {
  verifyCode.value = codigo
  await handleVerification()
}

const handleVerification = async () => {
  verifying.value = true
  try {
    verificationResult.value = await certificateStore.validateCertificate(verifyCode.value)
  } catch {
    verificationResult.value = { valido: false }
  } finally {
    verifying.value = false
  }
}

onMounted(async () => {
  await studentStore.initializeFromStorage()
  await certificateStore.fetchStudentCertificates()
})
</script>


<style scoped>
/* ===========================
   PALETA & BASE (branding EduLink)
   =========================== */
.certificates {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 18px;

  padding: 3rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  min-height: calc(100vh - 80px);
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
.certificates-header {
  text-align: center;
  margin-bottom: 3rem;
}

.certificates-header h1 {
  font-size: 2.2rem;
  color: #12222b;
  margin-bottom: 0.8rem;
  letter-spacing: 0.03em;
}

.certificates-header p {
  font-size: 1rem;
  color: #6d7a86;
}

/* ===========================
   ESTADOS
   =========================== */
.loading {
  text-align: center;
  padding: 4rem 0;
  color: #6d7a86;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
}

.empty-illustration {
  font-size: 4.5rem;
  margin-bottom: 1.5rem;
}

.empty-state h2 {
  color: #12222b;
  margin-bottom: 0.7rem;
}

.empty-state p {
  color: #6d7a86;
  margin-bottom: 1.6rem;
  font-size: 0.98rem;
}

/* ===========================
   STATS
   =========================== */
.certificates-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.6rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: #ffffff;
  padding: 1.6rem 1.5rem;
  border-radius: var(--border-radius-primary);
  box-shadow:
    0 20px 50px rgba(15, 35, 34, 0.18),
    0 0 0 1px rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(163, 216, 195, 0.55);
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 144, 133, 0.08), transparent 70%);
  opacity: 1;
  pointer-events: none;
}

.stat-icon {
  font-size: 2.3rem;
  position: relative;
  z-index: 1;
}

.stat-info {
  position: relative;
  z-index: 1;
}

.stat-info h3 {
  font-size: 1.8rem;
  margin-bottom: 0.1rem;
  color: var(--emerald-dark);
}

.stat-info p {
  color: #6d7a86;
  margin: 0;
  font-size: 0.9rem;
}

/* ===========================
   GRID CERTIFICADOS
   =========================== */
.certificates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.certificate-card {
  background: #ffffff;
  border-radius: 20px;
  box-shadow:
    0 24px 60px rgba(15, 35, 34, 0.22),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  overflow: hidden;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease,
    opacity 0.22s ease;
  border: 1px solid rgba(163, 216, 195, 0.5);
  display: flex;
  flex-direction: column;
}

.certificate-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 28px 70px rgba(8, 32, 26, 0.36);
  border-color: var(--emerald-primary);
}

.certificate-card.invalid {
  opacity: 0.8;
  border-color: #e46a76;
}

/* ===========================
   HEADER CERTIFICADO
   =========================== */
.certificate-header {
  background: linear-gradient(120deg, rgba(10, 28, 26, 0.98), rgba(23, 61, 55, 0.96));
  color: #fdfefe;
  padding: 1.1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-badge {
  padding: 0.25rem 0.8rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.status-badge.valid {
  background: rgba(163, 216, 195, 0.16);
  color: #c7f5da;
  border: 1px solid rgba(163, 216, 195, 0.5);
}

.status-badge.invalid {
  background: rgba(228, 106, 118, 0.16);
  color: #ffd7dd;
  border: 1px solid rgba(228, 106, 118, 0.5);
}

.certificate-date {
  font-size: 0.85rem;
  opacity: 0.9;
}

/* ===========================
   CONTENIDO CERTIFICADO
   =========================== */
.certificate-content {
  padding: 1.7rem 1.7rem 1.2rem;
  text-align: center;
}

.certificate-icon {
  font-size: 2.6rem;
  margin-bottom: 0.8rem;
}

.certificate-title {
  font-size: 1.35rem;
  color: #12222b;
  margin-bottom: 0.3rem;
}

.certificate-recipient {
  color: var(--emerald-dark);
  font-size: 1rem;
  margin-bottom: 1.3rem;
  font-weight: 600;
}

/* Detalles */
.certificate-details {
  background: #f6f9fb;
  padding: 1rem 1.1rem;
  border-radius: 14px;
  margin-bottom: 1.3rem;
  text-align: left;
  border: 1px dashed rgba(163, 216, 195, 0.7);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.45rem;
  gap: 0.5rem;
}

.detail-label {
  font-weight: 600;
  color: #4a5a68;
  font-size: 0.88rem;
}

.detail-value {
  color: #23313f;
  font-size: 0.88rem;
}

.detail-value.code {
  font-family: monospace;
  font-size: 0.8rem;
  background: #ecf1f5;
  padding: 0.25rem 0.5rem;
  border-radius: 999px;
}

/* QR */
.qr-code-section {
  margin-bottom: 1.3rem;
}

.qr-placeholder {
  background: #ecf1f5;
  border: 2px dashed rgba(163, 216, 195, 0.9);
  padding: 1.3rem;
  border-radius: 14px;
  color: #6d7a86;
}

.qr-placeholder span {
  display: block;
  font-weight: 600;
  margin-bottom: 0.3rem;
}

.qr-placeholder small {
  display: block;
  font-size: 0.8rem;
  font-family: monospace;
}

/* ===========================
   ACCIONES CERTIFICADO
   =========================== */
.certificate-actions {
  padding: 1.2rem 1.4rem 1.3rem;
  background: #f6f8fa;
  display: flex;
  gap: 0.7rem;
  flex-wrap: wrap;
}

/* ===========================
   BOTONES REUTILIZADOS
   =========================== */
.btn {
  padding: 0.7rem 1.4rem;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-decoration: none;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
  flex: 1;
  min-width: 150px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Principal (CTA) */
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
  box-shadow: 0 6px 18px rgba(15, 35, 34, 0.16);
}

.btn-outline:hover {
  background: var(--emerald-soft);
  border-color: var(--emerald-primary);
}

/* Secundario */
.btn-secondary {
  background: #ecf1f5;
  color: #23313f;
  border: 1px solid rgba(163, 216, 195, 0.5);
  box-shadow: 0 6px 16px rgba(15, 35, 34, 0.16);
}

.btn-secondary:hover:not(:disabled) {
  background: #dde6ec;
  transform: translateY(-1px);
}

/* ===========================
   SECCIÓN VERIFICACIÓN
   =========================== */
.verify-section {
  background: #ffffff;
  padding: 2.3rem 2rem;
  border-radius: 20px;
  box-shadow:
    0 24px 60px rgba(15, 35, 34, 0.22),
    0 0 0 1px rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(163, 216, 195, 0.55);
}

.verify-section h2 {
  color: #12222b;
  margin-bottom: 0.6rem;
}

.verify-section p {
  color: #6d7a86;
  margin-bottom: 1.6rem;
  font-size: 0.96rem;
}

.verify-form {
  display: flex;
  gap: 0.9rem;
  margin-bottom: 1.8rem;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 260px;
}

.form-input {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #dde3ea;
  border-radius: 999px;
  font-size: 0.96rem;
  box-sizing: border-box;
  background: #fdfefe;
  transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--emerald-primary);
  box-shadow: 0 0 0 3px rgba(79, 144, 133, 0.18);
  background: #ffffff;
}

/* Resultado verificación */
.verification-result {
  padding: 1.3rem 1.2rem;
  border-radius: 14px;
  margin-top: 0.5rem;
}

.verification-success {
  background: #e6f7ef;
  border: 1px solid #b9e3cc;
  color: #226644;
}

.verification-error {
  background: #fbe6e8;
  border: 1px solid #f0bcc4;
  color: #8b2a32;
}

.verification-result h3 {
  margin-bottom: 0.7rem;
}

.verification-result p {
  margin: 0.3rem 0;
  font-size: 0.9rem;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 768px) {
  .certificates {
    padding: 2.3rem 0 2.6rem;
  }

  .container {
    padding: 0 1.4rem;
  }

  .certificates-header h1 {
    font-size: 1.8rem;
  }
  
  .certificates-grid {
    grid-template-columns: 1fr;
  }
  
  .certificate-actions {
    flex-direction: column;
  }
  
  .verify-form {
    flex-direction: column;
  }
  
  .form-group {
    min-width: auto;
  }
}
</style>
