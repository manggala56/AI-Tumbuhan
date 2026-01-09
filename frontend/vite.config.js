import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
const ngrokDomain = 'heterodox-crowned-jasiah.ngrok-free.dev'; 
export default defineConfig({
  plugins: [react()],
  server: {
    allowedHosts: [ngrokDomain],
    host: true, 
    port: 5174, 
  hmr: {
      host: ngrokDomain,
      protocol: 'wss', 
      clientPort: 443 
    }
  },
})
