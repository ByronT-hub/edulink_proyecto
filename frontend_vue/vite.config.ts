import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },

  // 🔥 ESTE ES EL CAMBIO IMPORTANTE
  server: {
    port: 5173,         // ⚡ Puerto oficial de Vite
    strictPort: true,   // ⛔ No permite cambiar a otro puerto automáticamente
    host: true,
    proxy: {
      // Proxy opcional: solo si quieres usar fetch("/api/...") sin dominio
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      }
    }
  }
})
