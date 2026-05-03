# Modular Storage Strategy & Hierarchy

This document outlines the storage structure for the multi-module LMS platform to ensure strict isolation between modules, schools, and individual courses.

## 1. Storage Disks

We utilize two primary local partitions (defined in `config/filesystems.php`):
- **`public`**: Accessible directly via URL (`/storage/...`). Root: `storage/app/public`.
- **`local` (Private)**: Not accessible via URL. Root: `storage/app/private`.

## 2. Directory Hierarchy

### A. Public Assets (`storage/app/public/`)
*Non-sensitive assets accessible by anyone.*

| Path | Description |
| :--- | :--- |
| `shared/` | Assets used across multiple modules. |
| `core/` | User profiles (avatars), system public assets. |
| `cms/` | Blog post images, banners. |
| `lms/` | Course cover images, category thumbnails. |
| `school/school_{id}/identity/` | Institution logo, header backgrounds. |
| `school/school_{id}/level_{id}/identity/` | Unit-specific logos and branding. |

---

### B. Private Materials (`storage/app/private/`)
*Course materials, staff documents, and student data. MUST go through respective secure controllers.*

Structure pattern: `{module}/school_{id}/[level_{id}]/{type}/`

#### 1. LMS Module Scope:
| Path | Description |
| :--- | :--- |
| `lms/school_{id}/course_{id}/materials/` | Documents (PDF, Docx, PPT) for lessons. |
| `lms/school_{id}/course_{id}/media/` | Private video/audio content. |
| `lms/school_{id}/course_{id}/tasks/` | Student-submitted assignments. |

#### 2. School Module Scope:
| Path | Description |
| :--- | :--- |
| `school/school_{id}/hr/staff_{id}/` | Staff legal documents (CV, KTP, Certificates). |
| `school/school_{id}/level_{id}/students/` | Student academic records, health docs. |
| `school/school_{id}/level_{id}/operations/` | Operational reports, asset photos (private). |

---

## 3. Isolation Rules

1. **No Cross-Entity Leakage**: Files must never be accessible across different `school_id` or `level_id` unless explicitly marked as shared.
2. **Multi-Tenant Scoping**: The top-level folder inside a module MUST be the `school_id` (e.g., `school/school_1/...`).
3. **Hierarchy Consistency**: Follow the folder naming pattern `school_{id}` and `level_{id}` to allow automated permission parsing in secure controllers.
4. **Helper Methods**: 
    - LMS: Use `LmsService::uploadCourseFile()`.
    - School: Use `SchoolMediaHelper::getIsolatedPath()` (TODO) or standardized manual scoping.

## 4. Secure File Access (Streaming)

Private files are accessed via a secure proxy route to prevent unauthorized downloads:
`GET /api/v1/lms/files/private/{path}`

**Logic Flow:**
1. Request received by `LmsFileController@stream`.
2. Controller extracts `school_id` and `course_id` from the path.
3. Permission Check:
    - **Admins**: Full access.
    - **Teachers**: Access to their school's files (strict ownership TODO).
    - **Students**: Access only to courses they are **enrolled** in.
4. If authorized, the file is streamed via `Storage::disk('local')->response($path)`.

---

## 5. Security Policy

To maintain platform integrity, all file uploads MUST adhere to the central security rules defined in `Modules\Core\Helpers\UploadSettingsHelper`.

### A. Whitelist Strategy
We use a **Strict Whitelist** approach. Only files with the following extensions are allowed (configurable via System Settings):
- **Images**: `jpg, jpeg, png, gif, webp, svg` (SVGs are sanitized).
- **Documents**: `pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar`.
- **Video**: `mp4, webm, ogg`.
- **Audio**: `mp3, wav, aac`.

### B. Validation Rules
When implementing uploads, always use the helper to get validation rules:
```php
$rules = UploadSettingsHelper::getUploadValidationRules();
$request->validate($rules);
```

### C. Sanitization
Any SVG upload MUST be sanitized using the `enshrined/svg-sanitize` library to prevent XSS attacks. This is handled automatically by `MediaService` and `FileManagerController`.

### C3.  **Frontend Centralization**:
    - Global upload settings (mime types, max sizes) are managed via `useCoreStore` in `@/modules/Core/stores/core.ts`.
    - Components fetching these settings call `coreStore.fetchSettingsGroup('media')` which queries the standardized Core settings API.
    - This ensures all modules (CMS, LMS, etc.) share the same security policies.

---

## 6. Usage Example

### Uploading a file in a Controller/Service:
```php
$service = app(LmsService::class);
$path = $service->uploadCourseFile(
    $request->file('material'), 
    $schoolId, 
    $courseId, 
    'materials'
);
// Resulting path: lms/school_1/course_5/materials/unique_name.pdf
```

### Displaying a private file link in Frontend:
The API should return the secure path, which the frontend prefix with the proxy URL:
`https://api.example.com/api/v1/lms/files/private/lms/school_1/course_5/materials/unique_name.pdf`
