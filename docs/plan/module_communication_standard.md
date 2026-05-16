# JA-Platform: Inter-Module Communication Standard (SOP)

To ensure the system remains modular, scalable, and "Plug-and-Play," all modules must adhere to this communication standard.

## 1. Contract-Based Interaction (Interface First)
Directly importing and using concrete classes from other modules is **STRICTLY PROHIBITED** (except for Models in specific cases).

- **Requirement**: Any service intended to be used by other modules must have an Interface.
- **Location**: `Modules/[ModuleName]/app/Contracts/`
- **Usage**:
  ```php
  // DO NOT:
  public function __construct(Modules\Media\Services\MediaService $service)

  // DO:
  public function __construct(Modules\Media\Contracts\MediaServiceInterface $service)
  ```
- **Binding**: Bindings are handled in the module's `ServiceProvider`.

## 2. Event-Driven Communication (EDA)
Use Events to notify other modules about actions without knowing who is listening.

- **Naming**: `Modules.[ModuleName].[Action]` (e.g., `Modules.School.StudentGraduated`)
- **Philosophy**: A module should fire an event and "forget." It should not depend on the success or existence of listeners in other modules.
- **Example**: When a user is deleted in `System`, fire `UserDeleted`. The `Media` module listens and deletes the user's avatar. The `System` module doesn't need to know `Media` exists.

## 3. Data Transfer Objects (DTO)
When passing complex data structures between modules, use DTOs to ensure type safety.

- **Location**: `Modules/[ModuleName]/app/Dto/`
- **Example**: `StudentEnrollmentDto` containing name, email, and target unit, rather than passing a raw array.

## 4. The Registry Pattern (Hooks)
The `System` module provides central registries that other modules "hook" into during the `boot()` phase.

- **Sidebar Menu**: `MenuRegistry->register(...)`
- **Dashboard Widgets**: `DashboardRegistry->registerStats(...)`
- **Global Settings**: `SettingsRegistry->registerTab(...)`
- **Permissions**: Each module registers its own permissions to the `PermissionRegistry`.

## 5. Cross-Module Data Relations
- **Foreign Keys**: Avoid hard database Foreign Keys between different module tiers (e.g., Application -> Service). Use logical relations.
- **Eloquent Joins**: Avoid `->with('moduleBRelation')` across modules. Instead, fetch IDs and query separately or use a Service method to aggregate data.
- **Standard Scopes**: Use shared Traits (like `HasWorkspace`) from the `System` module to ensure consistent data filtering.

## 6. Directory Layout for a "Perfect" Module
```text
Modules/[Name]/
├── app/
│   ├── Contracts/      # Public Interfaces
│   ├── Dto/            # Data Transfer Objects
│   ├── Services/       # Internal logic (implementing Contracts)
│   ├── Events/         # Outbound notifications
│   ├── Listeners/      # Inbound reactions
│   ├── Http/
│   │   ├── Controllers/ # Use Domain naming, avoid "Admin" prefix
│   │   ├── Requests/    # Validation logic
│   └── Models/          # Database Schema & Scopes
├── database/
│   ├── migrations/      # Tier-prefixed tables (sys_, srv_, etc.)
│   ├── seeders/         # Factory-based seeders
```

## 7. The "Kernel" Bridge
The `Modules/System` module acts as the **Kernel**. It contains the base classes and interfaces that define how a module should behave. No module should depend on `Cms` or `School`, but all modules can depend on `System`.

---

> [!TIP]
> Always ask: "If I delete this module folder, will the rest of the application still boot?" 
> If the answer is **NO**, the coupling is too tight.
