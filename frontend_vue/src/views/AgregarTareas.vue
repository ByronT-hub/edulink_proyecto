<template>
  <div class="agregar-tareas-bg">
    <div class="container">
      <div class="header">
        <router-link to="/maestro/mis-cursos" class="btn btn-back">← Volver a Mis Cursos</router-link>
        <h1>Agregar Módulos y Lecciones</h1>
      </div>
      <form class="form-tarea" @submit.prevent="guardarEstructura">
        <div v-for="(modulo, mIdx) in modulos" :key="mIdx" class="modulo-card">
          <div class="modulo-header">
            <label :for="`modulo-nombre-${mIdx}`">Módulo {{ mIdx + 1 }}</label>
            <input :id="`modulo-nombre-${mIdx}`" v-model="modulo.nombre" type="text" required placeholder="Nombre del módulo" />
            <button type="button" class="btn btn-danger btn-sm" @click="eliminarModulo(mIdx)" v-if="modulos.length > 1">Eliminar módulo</button>
          </div>
          <div class="lecciones-block">
            <div v-for="(leccion, lIdx) in modulo.lecciones" :key="lIdx" class="leccion-item">
              <label :for="`leccion-nombre-${mIdx}-${lIdx}`">Lección {{ lIdx + 1 }}</label>
              <input :id="`leccion-nombre-${mIdx}-${lIdx}`" v-model="leccion.nombre" type="text" required placeholder="Nombre de la lección" />
              <button type="button" class="btn btn-danger btn-xs" @click="eliminarLeccion(mIdx, lIdx)" v-if="modulo.lecciones.length > 1">Eliminar</button>
            </div>
            <button type="button" class="btn btn-secondary btn-xs" @click="agregarLeccion(mIdx)">+ Agregar lección</button>
          </div>
        </div>
        <button type="button" class="btn btn-primary" @click="agregarModulo">+ Agregar módulo</button>
        <button type="submit" class="btn btn-success" :disabled="guardando">{{ guardando ? 'Guardando...' : 'Guardar estructura' }}</button>
        <div v-if="guardado" style="color:green; margin-top:1rem;">¡Estructura guardada correctamente!</div>
        <div v-if="error" style="color:red; margin-top:1rem;">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'

interface Leccion {
  nombre: string
}
interface Modulo {
  nombre: string
  lecciones: Leccion[]
}

const route = useRoute();
const cursoId = route.params.id as string;

const modulos = ref<Modulo[]>([
  { nombre: 'Módulo 1', lecciones: [ { nombre: 'Lección 1' } ] }
])

const guardando = ref(false);
const guardado = ref(false);
const error = ref('');

const agregarModulo = () => {
  modulos.value.push({ nombre: `Módulo ${modulos.value.length + 1}`, lecciones: [ { nombre: 'Lección 1' } ] })
}
const eliminarModulo = (idx: number) => {
  if (modulos.value.length > 1) modulos.value.splice(idx, 1)
}
const agregarLeccion = (modIdx: number) => {
  modulos.value[modIdx].lecciones.push({ nombre: `Lección ${modulos.value[modIdx].lecciones.length + 1}` })
}
const eliminarLeccion = (modIdx: number, lecIdx: number) => {
  if (modulos.value[modIdx].lecciones.length > 1) modulos.value[modIdx].lecciones.splice(lecIdx, 1)
}

const guardarEstructura = async () => {
  guardando.value = true;
  guardado.value = false;
  error.value = '';
  try {
    const response = await fetch(`http://localhost:8000/api/cursos/${cursoId}/estructura`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ estructura: modulos.value }),
    });
    if (!response.ok) throw new Error('Error al guardar la estructura');
    guardado.value = true;
  } catch (e: any) {
    error.value = e.message || 'Error desconocido';
  } finally {
    guardando.value = false;
  }
};
</script>

<style scoped>
.agregar-tareas-bg {
  --emerald-primary: #4f9085;
  --emerald-dark: #3a6f66;
  --emerald-soft: #e4f1ed;
  --neutral-background: #f6f8fa;
  --neutral-dark: #23313f;
  --accent-highlight: #a3d8c3;
  --border-radius-primary: 22px;
  min-height: calc(100vh - 80px);
  padding: 3rem 0 3.5rem;
  background:
    radial-gradient(circle at top left, #eaf6f3 0, #d7ece6 40%, #c7e2dc 75%, #b9d8d2 100%);
  font-family: 'Poppins', 'Roboto', 'Arial', sans-serif;
}
.container {
  max-width: 700px;
  margin: 0 auto;
  padding: 0 2rem;
}
.header {
  text-align: center;
  margin-bottom: 2.4rem;
}
.header h1 {
  font-size: 2rem;
  color: #12222b;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
}
.btn-back {
  display: inline-block;
  margin-bottom: 1.2rem;
  background: #dde6ec;
  color: #23313f;
  border-radius: 999px;
  padding: 0.5rem 1.2rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  transition: background 0.2s;
}
.btn-back:hover {
  background: #b9d8d2;
}
.form-tarea {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}
.modulo-card {
  background: rgba(255,255,255,0.98);
  border-radius: var(--border-radius-primary);
  box-shadow: 0 22px 60px rgba(15, 35, 34, 0.18), 0 0 0 1px rgba(255,255,255,0.9);
  border: 1px solid rgba(163, 216, 195, 0.75);
  padding: 1.5rem 1.2rem 1.2rem;
  margin-bottom: 1.2rem;
}
.modulo-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}
.modulo-header label {
  font-weight: 600;
  color: var(--emerald-dark);
  min-width: 90px;
}
.modulo-header input {
  flex: 1;
  padding: 0.5rem 0.7rem;
  border: 1px solid #b9d8d2;
  border-radius: 6px;
  font-size: 1rem;
  background: #f8fafb;
}
.lecciones-block {
  margin-left: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}
.leccion-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
}
.leccion-item label {
  font-weight: 500;
  color: #23313f;
  min-width: 90px;
}
.leccion-item input {
  flex: 1;
  padding: 0.5rem 0.7rem;
  border: 1px solid #b9d8d2;
  border-radius: 6px;
  font-size: 1rem;
  background: #f8fafb;
}
.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 999px;
  border: none;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background 0.22s ease,
    color 0.22s ease,
    border-color 0.22s ease;
}
.btn.btn-primary {
  background: var(--emerald-dark);
  color: #fff;
  margin-top: 1rem;
  box-shadow: 0 12px 28px rgba(8, 32, 26, 0.55);
}
.btn.btn-primary:hover {
  background: var(--emerald-primary);
  transform: translateY(-1px);
  box-shadow: 0 16px 36px rgba(5, 22, 18, 0.65);
}
.btn.btn-success {
  background: linear-gradient(135deg, #4f9085 0%, #5ca598 100%);
  color: #fff;
  margin-top: 1rem;
  box-shadow: 0 12px 28px rgba(8, 32, 26, 0.5);
}
.btn.btn-success:hover {
  background: linear-gradient(135deg, #458378 0%, #4f9386 100%);
  transform: translateY(-1px);
  box-shadow: 0 16px 38px rgba(5, 22, 18, 0.65);
}
.btn.btn-danger {
  background: #e05252;
  color: #fff;
}
.btn.btn-secondary {
  background: #dde6ec;
  color: #23313f;
}
.btn-xs {
  padding: 0.2rem 0.6rem;
  font-size: 0.85rem;
}
</style>
