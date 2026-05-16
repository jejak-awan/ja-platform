# JA-Platform: Modular System Blueprint (COMPLETED ✅)

This document serves as the single source of truth for the architectural refactoring of the JA-Platform. It combines the modular tiering plan, communication standards, and the execution roadmap.

---

## 1. Architectural Tiers

To achieve "True Modularity" (Plug-and-Play), the system is divided into four logical tiers:

| Tier | Module | Prefix (Table) | Purpose |
| :--- | :--- | :--- | :--- |
| **1. System** | `System` | `sys_` | Kernel, Auth, Registry, Base Traits. |
| **2. Infrastructure**| `Security`, `Infra` | `sec_`, `infra_`| Ops, Backups, Firewalls, Webhooks. |
| **3. Service** | `Media`, `Engagement`| `srv_` | Engines (Files, Analytics, Search). |
| **4. Application** | `Cms`, `School` | `cms_`, `sch_` | Business & Domain logic. |

---

## 2. Inter-Module Communication SOP

### A. Contract-Based (Interface First)
- Modules must interact via **Contracts** (Interfaces) located in `Modules/[Module]/app/Contracts`.
- Dependency Injection must use the Interface, not the concrete class.

### B. Event-Driven (EDA)
- Use **Fire & Forget** events for cross-module side effects.
- Event Name Format: `Modules.[ModuleName].[Action]`.

### C. Registry Pattern (Dynamic Hooks)
- The `System` module provides central registries (`Menu`, `Dashboard`, `Settings`).
- Modules register their capabilities during the `boot()` phase.

### D. Data Transfer Objects (DTO)
- Use DTOs for complex data exchange between modules to ensure type safety.

---

## 3. Database & Model Standards

- **Primary Keys**: Use **UUID** (or ULID) for all application models.
- **Table Prefixes**: Strictly follow the tier-based prefixes.
- **Logic**: Models are for persistence only; all business logic must reside in **Services**.
- **Seeders**: Factory-based seeders with realistic data.

---

## 4. API Routing Standards

The platform follows a strict hierarchical routing standard for management and public access:

| Prefix | Use Case | Target Module |
| :--- | :--- | :--- |
| `/v1/manage` | Core System Admin | `System`, `Security` |
| `/v1/manage/cms` | Content & Site Management | `Cms`, `Media` |
| `/v1/manage/school`| Academic Management | `School` |
| `/v1/manage/infra` | System Utilities | `Infra`, `Ai`, `Analytics` |
| `/v1/analytics` | Public Traffic Tracking | `Analytics` |

---

---

## 5. Execution Roadmap & Status

### Phase 1-3: Infrastructure Extraction [COMPLETED ✅]
- [x] Kernel extraction (`System`).
- [x] Security & Analytics isolation.
- [x] AI & Infrastructure utility extraction.

### Phase 4: Application Refinement & Rebranding [COMPLETED ✅]
- [x] **CMS Polish**: Decoupled from cross-module analytics services.
- [x] **School Refactor**: Domain-driven cleanup and legacy core decoupling.
- [x] **Frontend Rebranding**: Complete frontend migration to `/manage/` endpoints.

### Phase 5: Cleanup & Validation [COMPLETED ✅]
- [x] **Legacy Deletion**: Removed deprecated `Modules/Core`.
- [x] **Autoloader Polish**: Optimized `composer.json` PSR-4 mappings.
- [x] **System-Wide Testing**: Standardized feature tests passing successfully.

### Phase 6: Final Migration & Deployment [COMPLETED ✅]
- [x] **Clean Seeders**: Factory-based seeding verified on pgsql and sqlite.
- [x] **Final Handover**: Production-ready modular kernel stabilized.

---

> [!IMPORTANT]
> The guiding principle of this roadmap is **Isolation**. If any module folder (except System) is deleted, the application must still be able to boot and run its remaining features.
