# JA-Platform - Frontend

This is the frontend component of **JA-Platform**.

## 🚀 Stack

- **Framework**: Vue 3 (SFC with Script Setup)
- **Tooling**: Vite
- **Language**: TypeScript
- **Styling**: Tailwind CSS 4
- **State Management**: Pinia
- **Animation**: Native CSS/Vue transitions

## 🎓 LMS Features

The frontend implements a premium learning experience with the following components:

### 📱 Student Experience
- **Course Catalog**: Beautifully designed course listing and enrollment.
- **Learning Portal**: A dedicated classroom view with a navigation sidebar, progress tracking, and dynamic content rendering.
- **Quiz Renderer**: Interactive assessment interface with real-time feedback and score calculation.

### ⚙️ Admin Management
- **Course Management**: Full CRUD for courses.
- **Curriculum Builder**: Intuitively manage lessons and topics for each course.
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
