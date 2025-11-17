import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import CatalogoCursos from '../views/CatalogoCursos.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'catalogo',
    component: CatalogoCursos,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
