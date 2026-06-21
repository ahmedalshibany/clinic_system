# Clinic System — Product Identity & Design Register

## Identity

A clinic management dashboard for medical staff — administrators, doctors, receptionists, and nurses — to manage patients, appointments, invoices, medical records, and reporting. The tool must disappear into the clinical workflow.

## Register

**Product** — this is a task-oriented admin dashboard. Design serves the user's efficiency, not decoration. Familiarity is a feature; every component must feel standard and predictable.

## Brand

- **Name:** Clinic System
- **Domain:** Medical practice management (Arabic/English bilingual)
- **Audience:** Medical staff in a private clinic setting — expects reliability, clarity, and calm
- **Offline-capable:** XAMPP-hosted; typography must degrade gracefully when Google Fonts are unavailable

## Design System: Da Vinci

Inspired by Renaissance compositional principles applied to modern UI:

- **Proportions:** Golden Ratio (φ = 1.618) spacing scale
- **Typography:** Perfect Fourth (1.333) scale with a single sans family
- **Color:** Chiaroscuro — high-contrast chiaroscuro with warm parchment tones (light) and solid matte charcoal with steel blue accents (dark)
- **Shadows:** Sfumato — soft, layered, graduated shadows without harsh edges; dark mode uses pure black drop shadows, no colored glow
- **Radii:** Organic curves from 6px to 32px

### Color Register — Light Mode "Chiaroscuro"

| Token | Value | Usage |
|---|---|---|
| `--primary` | `#1a1a2e` | Headings, primary text, active states |
| `--secondary` | `#0f3d3e` | Accent surfaces, links, focus rings |
| `--accent` | `#a0522d` | Highlights, decorative elements |
| `--tertiary` | `#5c4033` | Muted decorative elements |
| `--white` | `#f5f0e8` | Card/surface backgrounds (aged parchment) |
| `--cream` | `#efe8dc` | Secondary surface, table headers |
| `--body-bg` | Gradient `#f5f0e8` → `#efe8dc` → `#e6ddd0` | Page background |
| `--text-primary` | `#2c2c2c` | Body text |
| `--text-secondary` | `#555555` | Secondary text |
| `--success` | `#2e5d34` | Positive states |
| `--warning` | `#bf8c30` | Warning states |
| `--danger` | `#8b3a3a` | Destructive states |
| `--info` | `#3d5a80` | Informational states |

### Color Register — Dark Mode "Matte Slate"

| Token | Value | Usage |
|---|---|---|
| `--primary` | `#e4e4e6` | Headings, primary text |
| `--secondary` | `#4a6a8a` | Accent surfaces, links, focus rings (flat steel blue) |
| `--accent` | `#8a8a8e` | Highlights, decorative elements (muted neutral) |
| `--tertiary` | `#5a5a5e` | Muted decorative elements |
| `--white` | `#121316` | Card surfaces (solid matte) |
| `--body-bg` | `#0c0d10` | Page background (deep charcoal matte) |
| `--text-primary` | `#d4d4d6` | Body text (soft bone white) |
| `--text-secondary` | `#9a9a9e` | Secondary text |
| `--success` | `#4a8a6a` | Positive states (desaturated) |
| `--warning` | `#b08a4a` | Warning states (desaturated) |
| `--danger` | `#b04a4a` | Destructive states (desaturated) |
| `--info` | `#4a7a9a` | Informational states (desaturated) |

### Typography

- **Single family:** Plus Jakarta Sans (English) + Tajawal (Arabic RTL), falling back to system font stack (`-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`)
- **No display serif** — this is a product UI, not a brand surface
- **Scale:** Perfect Fourth (1.333) from 0.75rem to 3.157rem
- **Line length for prose:** 65–75ch; data/tables can run denser

### Spacing

Golden Ratio scale: 4px → 6px → 10px → 16px → 26px → 42px → 68px

### Borders & Radii

| Token | Value |
|---|---|
| `--radius-sm` | 6px |
| `--radius` | 10px |
| `--radius-lg` | 16px |
| `--radius-xl` | 24px |
| `--radius-2xl` | 32px |
| `--radius-full` | 9999px |

### Motion

- Ease: `cubic-bezier(0.16, 1, 0.3, 1)` — exponential ease-out
- Duration: 150ms fast, 250ms base, 400ms slow
- Animate only transform + opacity — never layout properties

### Design Constraints

- All interactive components must define: default, hover, focus-visible, active, disabled, loading states
- Empty states must teach the interface
- Same button shape, form-control vocabulary, and icon style across all screens
- Icons: Font Awesome 6, single family throughout
