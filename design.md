# CRM AI Platform — Frontend Design Specifications

This document extracts the design system, color palette, typography guidelines, layout wrappers, and screen summaries for the **CRM AI Platform (AI CRM Demo)** to facilitate creating Figma copies.

---

## 🎨 Design Tokens & Color Palette

The color theme supports both **Light Mode** and **Dark Mode** (toggled dynamically via the `html` element's `.dark` class). 

The primary and brand values below show the static defaults fallback values from `config/branding/colors.php`. They map to CSS variables exposed on `:root`.

### Core Color Palette

| CSS Variable | Default Value | Description | Role / Usage |
| :--- | :--- | :--- | :--- |
| `--brand-primary` | `#2563eb` | Blue 600 | Primary Brand / Call-to-action buttons, links, active states |
| `--brand-secondary` | `#64748b` | Slate 500 | Secondary actions, inactive states, subtler labels |
| `--brand-success` | `#22c55e` | Green 500 | Success indicators, won deals, active statuses |
| `--brand-danger` | `#ef4444` | Red 500 | Error states, lost deals, destructive actions |
| `--brand-warning` | `#f59e0b` | Amber 500 | Pending items, warnings, attention states |
| `--brand-info` | `#06b6d4` | Cyan 500 | Dynamic info badges, system logs |

### Admin Theme Custom Colors

The administration dashboard uses a specialized layout palette. Colors dynamically shift when Dark Mode is active:

| Area | CSS Variable | Light Default | Dark Default |
| :--- | :--- | :--- | :--- |
| **Sidebar Background** | `--admin-sidebar-bg` | `#ffffff` | `#0f172a` (Slate 900) |
| **Sidebar Border** | `--admin-sidebar-border` | `#e2e8f0` | `#1e293b` (Slate 800) |
| **Sidebar Rail Background** | `--admin-sidebar-rail-bg` | `#1e3a8a` (Blue 900) | `#172554` (Blue 950) |
| **Active Navigation Item BG** | `--admin-active-item-bg` | `#eff6ff` (Blue 50) | `#2563eb20` (~12% Blue opacity) |
| **Active Navigation Text** | `--admin-active-item-text` | `#2563eb` (Blue 600) | `#60a5fa` (Blue 400) |
| **Header Background** | `--admin-header-bg` | `#ffffffcc` (80% opacity) | `#0f172acc` (80% opacity) |
| **Content Background** | `--admin-content-bg` | `#f8fafc` (Slate 50) | `#020617` (Slate 950) |
| **Card Background** | `--admin-card-bg` | `#ffffff` | `#0f172a` (Slate 900) |
| **Card Border** | `--admin-card-border` | `#f1f5f9` (Slate 100) | `#1e293b` (Slate 800) |

---

## 🔤 Typography & Font Rules

Fonts are imported via Bunny Fonts and set dynamically on the primary body class.

* **Primary Font Face**: `var(--font-primary)`
  * **Default**: `"Instrument Sans", sans-serif`
  * **Weights Used**: `400` (Regular), `500` (Medium), `600` (Semi-Bold)
  * **Source**: `https://fonts.bunny.net/css?family=instrument-sans:400,500,600`
* **Secondary Font Face**: `var(--font-secondary)`
  * **Default**: `"Roboto", sans-serif`
* **Utility Classes**:
  * Root HTML contains `antialiased` rendering.
  * Standard link/button layouts have pointer override rules applied globally: `a, button, [role="button"], .cursor-pointer { cursor: pointer !important; }`.

---

## 🗂️ Layout Structure Wrappers

The UI is divided into 4 specific structural wrappers located in `resources/js/Layouts/`:

1. **GuestLayout.vue**: Standard auth screens (Login, Register).
2. **PublicLayout.vue**: Shareable public forms and public facing sections.
3. **CustomerLayout.vue**: Client dashboard panel containing client-specific options, deal reviews, and updates.
4. **AdminLayout.vue**: Admin panel layout with a dual-navigation structure:
   * **Left Sidebar Rail**: Fixed blue rail for top-level category icons (CRM, Marketing, Configuration).
   * **Expandable Secondary Sidebar**: Context-specific menu options based on the rail category.
   * **Sticky Blurred Top Header**: Search bar, notification alerts, profile selector.

---

## 🖥️ Screen Summary Specification

Below is the directory list of all Vue pages in `resources/js/Pages/` and their respective purposes:

### 1. Guest & Public Screens (`Guest/` & `Public/`)

| Page Component | Path / Location | Screen Summary |
| :--- | :--- | :--- |
| **Marketing Homepage** | `Guest/Pages/Home.vue` or `Guest/Landing.vue` | Standard marketing home page introducing CRM features, benefits, and call-to-actions. |
| **Public Lead Generation** | `Guest/LeadGeneration.vue` | Standalone landing page built for converting visitors into leads. |
| **Public Contact Form** | `Guest/Contact/Show.vue` | Public contact page containing contact information and message submissions. |
| **Legal Agreements** | `Guest/Legal/Show.vue` | Terms of Service, Privacy Policy, and GDPR consent templates. |
| **Sign In** | `Guest/Pages/Login.vue` | Universal login interface for both CRM Members and Portal Customers. |
| **Sign Up** | `Guest/Pages/Register.vue` | Customer self-registration form. |
| **Public Lead Form** | `Public/LeadForm.vue` | Dynamic public-facing lead capture form rendered from user-created form builders. |

### 2. Client Portal / Customer Screens (`Customer/`)

| Page Component | Path / Location | Screen Summary |
| :--- | :--- | :--- |
| **Customer Dashboard** | `Customer/Pages/Dashboard.vue` | Summary of ongoing client deals, tasks assigned, and latest status highlights. |
| **Deals Index / List** | `Customer/Deals/Index.vue` | Tabbed view of all customer projects, contracts, and proposed deals. |
| **Create Deal Request** | `Customer/Deals/Create.vue` | Interactive wizard allowing clients to draft and request a new deal proposal. |
| **Deal Detail View** | `Customer/Deals/Show.vue` | Full detail pane showing stage tracking bar (kanban step style), checklist, documents, and messages. |
| **Customer Profile** | `Customer/Pages/Profile.vue` | Personal information update form, password security fields, and notification settings. |

### 3. CRM Administration Panel (`Admin/`)

#### Dashboards & Logs
* **Admin Dashboard (`Pages/Dashboard.vue`)**: Data visualization Hub displaying total value, pipeline progress, task status pie charts, and recent activity logs.
* **Activity Logs (`ActivityLog/Index.vue`, `ActivityLog/Show.vue`, `Pages/ActivityLogs.vue`)**: Searchable audit tables highlighting user action timelines and system events.

#### Core CRM Pipeline Entities
* **Customers (`Customers/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`)**: Full directory table, customer timeline details, billing history, and contact metadata.
* **Leads Pipeline (`Leads/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`)**: Lead cards display, contact details, conversion buttons to turn leads into deals.
* **Deals Manager (`Deals/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`)**: Kanban-style sales board allowing drag-and-drop actions across pipeline columns (e.g., *Draft*, *Contacted*, *Negotiating*, *Closed Won*, *Closed Lost*).
* **Tasks (`Tasks/Index.vue`, `Show.vue`)**: Task list with prioritize status labels (`High`, `Medium`, `Low`), checkbox checkoffs, and date selectors.
* **Reminders (`Reminders/Index.vue`)**: Pop-up modal triggers, scheduled event timelines.

#### Lead Capture & Marketing Tools
* **Lead Forms Index (`LeadForms/Index.vue`)**: Table of custom created public forms with active counters and embed keys.
* **Lead Form Builder (`LeadForms/Builder.vue`)**: Interactive visual drag-and-drop builder with left-sidebar fields (Input, Dropdown, Textarea) and center preview canvas to build custom forms.

#### Settings & Customization
* **Branding (`Business/Branding.vue` / `Pages/Branding.vue`)**: Real-time styling customizer where Admins change primary/secondary colors, upload logo assets, and toggle dark mode behaviors.
* **Business Logic (`Business/BusinessLogic.vue`)**: Setup form for currency controls, default lead stages, and automated settings.
* **Business Landing Builder (`Business/HomePageBuilder.vue`, `PageBuilder.vue`)**: Section editing cards where admins customize marketing homepage hero, features, and pricing text blocks.
* **Roles & Permissions (`Roles/Index.vue`, `Create.vue`, `Edit.vue`, `Pages/Roles.vue`)**: Checkbox matrix allowing granular control over CRM functions.
* **Team Members (`Members/Index.vue`, `Create.vue`, `Edit.vue`, `Pages/Users.vue`)**: Directory of administrators and sales representatives with profile edits and invite links.
