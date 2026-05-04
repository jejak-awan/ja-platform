# JA-Platform - Frontend
**Edition**: edu v.1.0.0  
**Theme**: janari v.1.0.0  
**Version**: 1.0.0-beta.1

This is the frontend component of **JA-Platform**. Developed by **Jejakawan** ([jejakawan.com](https://jejakawan.com)) for **PT. Kirana Karina Network (K2NET)**.

## 🚀 Stack

- **Framework**: Vue 3 (SFC with Script Setup)
- **Tooling**: Vite
- **Language**: TypeScript
- **Styling**: Tailwind CSS 4
- **State Management**: Pinia
- **Animation**: Native CSS/Vue transitions

## 🎓 LMS Features
## LMS Dashboard & Learning Portal

Antarmuka pengguna untuk Manajemen Kursus dan Portal Belajar Siswa yang dibangun secara *native* dan terintegrasi dengan modul School.

## Struktur Modul
- **Admin**: Manajemen Kursus, Kurikulum (Lessons), dan Materi (Topics).
- **Student**: Katalog Kursus, Progres Belajar, dan Learning Portal (Viewer).
- **Service**: Integrasi API yang dioptimasi untuk performa tinggi.
- **Quiz Editor**: Build complex assessments with multiple question types.

## ✨ Animations

Untuk menjaga estetika premium **JA-Platform**, gunakan transisi native CSS/Vue:

```typescript
const visible = ref(false)

onMounted(() => {
  visible.value = true
})
```
Setiap komponen LMS dirancang dengan transisi mikro untuk memberikan kesan *premium* dan *alive*.

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
