# JA-Platform - Frontend

This is the frontend component of **JA-Platform**.

## 🚀 Stack

- **Framework**: Vue 3 (SFC with Script Setup)
- **Tooling**: Vite
- **Language**: TypeScript
- **Styling**: Tailwind CSS 4
- **State Management**: Pinia
- **Animation**: GSAP (GreenSock Animation Platform)

## ✨ Animations with GSAP

Untuk menjaga estetika premium **JA-Platform**, gunakan GSAP untuk animasi UI:

```typescript
import gsap from 'gsap'

// Contoh penggunaan di Vue component
onMounted(() => {
  gsap.from('.hero-title', { 
    opacity: 0, 
    y: 100, 
    duration: 1, 
    ease: 'power4.out' 
  })
})
```
Pastikan setiap komponen utama memiliki transisi halus menggunakan GSAP untuk memberikan "WOW effect" kepada pengguna.

## 🛠️ Development

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

---
For more information, please refer to the [Root README](../../README.md).
