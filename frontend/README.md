# JA-Platform - Frontend

This is the frontend component of **JA-Platform**.

## 🚀 Stack

- **Framework**: Vue 3 (SFC with Script Setup)
- **Tooling**: Vite
- **Language**: TypeScript
- **Styling**: Tailwind CSS 4
- **State Management**: Pinia
- **Animation**: Native CSS/Vue transitions

## ✨ Animations

Untuk menjaga estetika premium **JA-Platform**, gunakan transisi native CSS/Vue:

```typescript
const visible = ref(false)

onMounted(() => {
  visible.value = true
})
```
Pastikan setiap komponen utama memiliki transisi halus dengan pendekatan ringan agar performa tetap optimal.

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
