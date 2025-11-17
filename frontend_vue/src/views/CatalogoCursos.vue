<script setup lang="ts">
import { computed, ref } from 'vue';

interface Curso {
  id: number;
  titulo: string;
  descripcion: string;
  costo_centavos: number;
  fecha_inicio: string; // formato "2025-01-10"
  fecha_fin: string;
  activo: boolean;
}

// 🔹 Datos mock de ejemplo
const cursos = ref<Curso[]>([
  {
    id: 1,
    titulo: 'Programación Web con Laravel',
    descripcion: 'Aprendé a construir APIs REST con Laravel y MySQL.',
    costo_centavos: 250000,
    fecha_inicio: '2025-01-15',
    fecha_fin: '2025-02-15',
    activo: true,
  },
  {
    id: 2,
    titulo: 'Introducción a Redes',
    descripcion: 'Fundamentos de redes, protocolos y topologías.',
    costo_centavos: 150000,
    fecha_inicio: '2025-02-01',
    fecha_fin: '2025-03-01',
    activo: true,
  },
  {
    id: 3,
    titulo: 'Curso antiguo (inactivo)',
    descripcion: 'Solo para probar el filtro de activos.',
    costo_centavos: 100000,
    fecha_inicio: '2024-01-01',
    fecha_fin: '2024-02-01',
    activo: false,
  },
]);

// Solo mostramos cursos activos
const cursosActivos = computed(() => cursos.value.filter((c) => c.activo));

// Helper para fecha y costo
const formatearFecha = (fecha: string) =>
  new Date(fecha).toLocaleDateString('es-GT');

const formatearCosto = (centavos: number) =>
  (centavos / 100).toFixed(2);
</script>

<template>
  <section>
    <h2 class="text-2xl font-bold mb-4">
      Catálogo de Cursos Activos
    </h2>

    <p class="text-sm text-slate-300 mb-6 max-w-2xl">
      Aquí se muestran los cursos activos con su descripción, costo y fechas.
      Más adelante agregaremos el registro de estudiantes, inscripción y pago.
    </p>

    <div
      v-if="cursosActivos.length === 0"
      class="text-slate-300 text-sm"
    >
      No hay cursos activos por el momento.
    </div>

    <div
      v-else
      class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
      <article
        v-for="curso in cursosActivos"
        :key="curso.id"
        class="bg-slate-900/80 border border-slate-700 rounded-xl p-4 shadow-sm flex flex-col"
      >
        <h3 class="text-lg font-semibold mb-1">
          {{ curso.titulo }}
        </h3>

        <p class="text-sm text-slate-300 mb-3">
          {{ curso.descripcion }}
        </p>

        <p class="text-xs text-slate-400">
          <span class="font-semibold">Costo:</span>
          Q {{ formatearCosto(curso.costo_centavos) }}
        </p>
        <p class="text-xs text-slate-400">
          <span class="font-semibold">Inicio:</span>
          {{ formatearFecha(curso.fecha_inicio) }}
        </p>
        <p class="text-xs text-slate-400 mb-4">
          <span class="font-semibold">Fin:</span>
          {{ formatearFecha(curso.fecha_fin) }}
        </p>

        <button
          class="mt-auto w-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold py-2 px-4 rounded-lg text-sm transition"
        >
          Inscribirme (próximamente)
        </button>
      </article>
    </div>
  </section>
</template>

<style scoped>
/* Puedes dejarlo vacío por ahora */
</style>
