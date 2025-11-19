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
              <h3>{{ validCertificates }}</h3>
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
          <div 
            v-for="certificate in userCertificates" 
            :key="certificate.id"
            class="certificate-card"
            :class="{ 'invalid': !certificate.valido }"
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
              <h3 class="certificate-title">{{ certificate.curso?.titulo || 'Curso' }}</h3>
              <p class="certificate-recipient">{{ certificate.estudiante?.nombre || 'Estudiante' }}</p>
              
              <div class="certificate-details">
                <div class="detail-item">
                  <span class="detail-label">Fecha de emisión:</span>
                  <span class="detail-value">{{ formatDate(certificate.fecha_emision) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Código QR:</span>
                  <span class="detail-value code">{{ certificate.codigo_qr }}</span>
                </div>
              </div>

              <div class="qr-code-section">
                <div class="qr-placeholder">
                  <span>QR Code</span>
                  <small>{{ certificate.codigo_qr }}</small>
                </div>
              </div>
            </div>

            <div class="certificate-actions">
              <button 
                @click="downloadCertificate(certificate.id)"
                :disabled="downloading === certificate.id"
                class="btn btn-primary"
              >
                {{ downloading === certificate.id ? 'Descargando...' : '📥 Descargar PDF' }}
              </button>
              
              <button 
                @click="shareCertificate(certificate)"
                class="btn btn-outline"
              >
                🔗 Compartir
              </button>
              
              <button 
                @click="verifyCertificate(certificate.codigo_qr)"
                class="btn btn-secondary"
              >
                🔍 Verificar
              </button>
            </div>
          </div>
        </div>

        <!-- Sección de verificación -->
        <div class="verify-section">
          <h2>Verificar Certificado</h2>
          <p>Ingresa un código QR para verificar la autenticidad de un certificado</p>
          
          <form @submit.prevent="handleVerification" class="verify-form">
            <div class="form-group">
              <input
                v-model="verifyCode"
                type="text"
                placeholder="Ingresa el código QR del certificado"
                class="form-input"
                :disabled="verifying"
              />
            </div>
            
            <button 
              type="submit" 
              :disabled="!verifyCode || verifying"
              class="btn btn-primary"
            >
              {{ verifying ? 'Verificando...' : 'Verificar Certificado' }}
            </button>
          </form>

          <div v-if="verificationResult" class="verification-result">
            <div v-if="verificationResult.valido" class="verification-success">
              <h3>✅ Certificado Válido</h3>
              <p><strong>Curso:</strong> {{ verificationResult.curso?.titulo }}</p>
              <p><strong>Estudiante:</strong> {{ verificationResult.estudiante?.nombre }}</p>
              <p><strong>Fecha de emisión:</strong> {{ formatDate(verificationResult.fecha_emision) }}</p>
            </div>
            <div v-else class="verification-error">
              <h3>❌ Certificado Inválido</h3>
              <p>El código QR no corresponde a un certificado válido</p>
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

const certificateStore = useCertificateStore()
const studentStore = useStudentStore()

const downloading = ref<number | null>(null)
const verifying = ref(false)
const verifyCode = ref('')
const verificationResult = ref<any>(null)

const loading = computed(() => certificateStore.loading)
const userCertificates = computed(() => 
  studentStore.student ? certificateStore.certificatesByStudent(studentStore.student.id) : []
)

const validCertificates = computed(() => 
  userCertificates.value.filter(cert => cert.valido).length
)

const recentCertificates = computed(() => {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()
  
  return userCertificates.value.filter(cert => {
    const certDate = new Date(cert.fecha_emision)
    return certDate.getMonth() === currentMonth && certDate.getFullYear() === currentYear
  }).length
})

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const downloadCertificate = async (certificateId: number) => {
  downloading.value = certificateId
  
  try {
    const pdfBlob = await certificateStore.downloadCertificate(certificateId)
    
    // Crear enlace de descarga
    const url = window.URL.createObjectURL(pdfBlob)
    const link = document.createElement('a')
    link.href = url
    link.download = `certificado_${certificateId}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error: any) {
    alert(error.message || 'Error al descargar certificado')
  } finally {
    downloading.value = null
  }
}

const shareCertificate = (certificate: any) => {
  const url = `${window.location.origin}/verify/${certificate.codigo_qr}`
  
  if (navigator.share) {
    navigator.share({
      title: `Certificado - ${certificate.curso?.titulo}`,
      text: `Verifica mi certificado del curso ${certificate.curso?.titulo}`,
      url: url
    })
  } else {
    // Fallback: copiar al portapapeles
    navigator.clipboard.writeText(url).then(() => {
      alert('Enlace copiado al portapapeles')
    })
  }
}

const verifyCertificate = async (qrCode: string) => {
  verifyCode.value = qrCode
  await handleVerification()
}

const handleVerification = async () => {
  if (!verifyCode.value) return
  
  verifying.value = true
  verificationResult.value = null
  
  try {
    const result = await certificateStore.validateCertificate(verifyCode.value)
    verificationResult.value = result
  } catch (error: any) {
    verificationResult.value = { valido: false }
  } finally {
    verifying.value = false
  }
}

onMounted(async () => {
  if (studentStore.student) {
    try {
      await certificateStore.fetchStudentCertificates(studentStore.student.id)
    } catch (error) {
      console.error('Error al cargar certificados:', error)
    }
  }
})
</script>

<style scoped>
.certificates {
  padding: 2rem 0;
  background: #f8f9fa;
  min-height: calc(100vh - 140px);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.certificates-header {
  text-align: center;
  margin-bottom: 3rem;
}

.certificates-header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.certificates-header p {
  font-size: 1.25rem;
  color: #6c757d;
}

.loading {
  text-align: center;
  padding: 4rem 0;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
}

.empty-illustration {
  font-size: 5rem;
  margin-bottom: 2rem;
}

.empty-state h2 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #6c757d;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

.certificates-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 3rem;
}

.stat-info h3 {
  font-size: 2rem;
  margin-bottom: 0.25rem;
  color: #667eea;
}

.stat-info p {
  color: #6c757d;
  margin: 0;
}

.certificates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.certificate-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s;
}

.certificate-card:hover {
  transform: translateY(-5px);
}

.certificate-card.invalid {
  opacity: 0.7;
  border: 2px solid #dc3545;
}

.certificate-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: bold;
}

.status-badge.valid {
  background: rgba(255, 255, 255, 0.2);
  color: #d4edda;
}

.status-badge.invalid {
  background: rgba(255, 255, 255, 0.2);
  color: #f8d7da;
}

.certificate-date {
  font-size: 0.9rem;
  opacity: 0.9;
}

.certificate-content {
  padding: 2rem;
  text-align: center;
}

.certificate-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.certificate-title {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.certificate-recipient {
  color: #667eea;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.certificate-details {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: left;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: 500;
  color: #2c3e50;
}

.detail-value {
  color: #6c757d;
}

.detail-value.code {
  font-family: monospace;
  font-size: 0.8rem;
  background: #e9ecef;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.qr-code-section {
  margin-bottom: 1.5rem;
}

.qr-placeholder {
  background: #e9ecef;
  border: 2px dashed #adb5bd;
  padding: 2rem;
  border-radius: 8px;
  color: #6c757d;
}

.qr-placeholder small {
  display: block;
  font-size: 0.8rem;
  margin-top: 0.5rem;
  font-family: monospace;
}

.certificate-actions {
  padding: 1.5rem;
  background: #f8f9fa;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.verify-section {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.verify-section h2 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.verify-section p {
  color: #6c757d;
  margin-bottom: 2rem;
}

.verify-form {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 300px;
}

.form-input {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
}

.verification-result {
  padding: 1.5rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.verification-success {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.verification-error {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
}

.verification-result h3 {
  margin-bottom: 1rem;
}

.verification-result p {
  margin: 0.5rem 0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s;
  text-align: center;
  font-weight: 500;
  display: inline-block;
  flex: 1;
  min-width: 120px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a6fd8;
}

.btn-outline {
  background: transparent;
  color: #667eea;
  border: 2px solid #667eea;
}

.btn-outline:hover {
  background: #667eea;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #545b62;
}

@media (max-width: 768px) {
  .certificates-header h1 {
    font-size: 2rem;
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