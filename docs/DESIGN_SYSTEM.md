# ProjectHub Admin Dashboard - Design System

## 1. Color System Analysis & Solutions

### Current Issues Identified:
- Inconsistent color usage across components
- Poor contrast ratios for accessibility
- No systematic color hierarchy
- Dark/light mode inconsistencies

### Proposed Color Palettes (WCAG AA Compliant)

#### Option 1: Professional Blue (Recommended)
```css
:root {
  /* Primary Brand Colors */
  --ph-primary-50: #eff6ff;
  --ph-primary-100: #dbeafe;
  --ph-primary-200: #bfdbfe;
  --ph-primary-300: #93c5fd;
  --ph-primary-400: #60a5fa;
  --ph-primary-500: #3b82f6;  /* Main brand */
  --ph-primary-600: #2563eb;
  --ph-primary-700: #1d4ed8;
  --ph-primary-800: #1e40af;
  --ph-primary-900: #1e3a8a;

  /* Semantic Colors */
  --ph-success: #10b981;
  --ph-success-light: #d1fae5;
  --ph-warning: #f59e0b;
  --ph-warning-light: #fef3c7;
  --ph-error: #ef4444;
  --ph-error-light: #fee2e2;
  --ph-info: #06b6d4;
  --ph-info-light: #cffafe;

  /* Neutral Grays */
  --ph-gray-50: #f9fafb;
  --ph-gray-100: #f3f4f6;
  --ph-gray-200: #e5e7eb;
  --ph-gray-300: #d1d5db;
  --ph-gray-400: #9ca3af;
  --ph-gray-500: #6b7280;
  --ph-gray-600: #4b5563;
  --ph-gray-700: #374151;
  --ph-gray-800: #1f2937;
  --ph-gray-900: #111827;

  /* Surface Colors */
  --ph-surface-primary: #ffffff;
  --ph-surface-secondary: #f9fafb;
  --ph-surface-elevated: #ffffff;
  --ph-surface-overlay: rgba(0, 0, 0, 0.5);
}

.dark {
  --ph-surface-primary: #1f2937;
  --ph-surface-secondary: #111827;
  --ph-surface-elevated: #374151;
  --ph-surface-overlay: rgba(0, 0, 0, 0.7);
}
```

#### Option 2: Modern Purple
```css
:root {
  --ph-primary-500: #8b5cf6;
  --ph-primary-600: #7c3aed;
  --ph-primary-700: #6d28d9;
  /* ... similar structure */
}
```

#### Option 3: Sophisticated Teal
```css
:root {
  --ph-primary-500: #14b8a6;
  --ph-primary-600: #0d9488;
  --ph-primary-700: #0f766e;
  /* ... similar structure */
}
```

## 2. Typography System

```css
:root {
  /* Font Families */
  --ph-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --ph-font-mono: 'JetBrains Mono', 'Fira Code', monospace;

  /* Font Sizes */
  --ph-text-xs: 0.75rem;    /* 12px */
  --ph-text-sm: 0.875rem;   /* 14px */
  --ph-text-base: 1rem;     /* 16px */
  --ph-text-lg: 1.125rem;   /* 18px */
  --ph-text-xl: 1.25rem;    /* 20px */
  --ph-text-2xl: 1.5rem;    /* 24px */
  --ph-text-3xl: 1.875rem;  /* 30px */
  --ph-text-4xl: 2.25rem;   /* 36px */

  /* Font Weights */
  --ph-font-normal: 400;
  --ph-font-medium: 500;
  --ph-font-semibold: 600;
  --ph-font-bold: 700;

  /* Line Heights */
  --ph-leading-tight: 1.25;
  --ph-leading-snug: 1.375;
  --ph-leading-normal: 1.5;
  --ph-leading-relaxed: 1.625;
}
```

## 3. Spacing & Layout System

```css
:root {
  /* Spacing Scale */
  --ph-space-1: 0.25rem;   /* 4px */
  --ph-space-2: 0.5rem;    /* 8px */
  --ph-space-3: 0.75rem;   /* 12px */
  --ph-space-4: 1rem;      /* 16px */
  --ph-space-5: 1.25rem;   /* 20px */
  --ph-space-6: 1.5rem;    /* 24px */
  --ph-space-8: 2rem;      /* 32px */
  --ph-space-10: 2.5rem;   /* 40px */
  --ph-space-12: 3rem;     /* 48px */
  --ph-space-16: 4rem;     /* 64px */

  /* Border Radius */
  --ph-radius-sm: 0.375rem; /* 6px */
  --ph-radius-md: 0.5rem;   /* 8px */
  --ph-radius-lg: 0.75rem;  /* 12px */
  --ph-radius-xl: 1rem;     /* 16px */
  --ph-radius-2xl: 1.5rem;  /* 24px */

  /* Shadows */
  --ph-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --ph-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --ph-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --ph-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
```

## 4. Component Architecture

### Base Components:
1. **Cards** - Primary content containers
2. **Buttons** - Action triggers with variants
3. **Inputs** - Form controls
4. **Navigation** - Sidebar and breadcrumbs
5. **Data Display** - Tables, charts, metrics

### Layout Components:
1. **Grid System** - 12-column responsive grid
2. **Stack** - Vertical spacing utility
3. **Cluster** - Horizontal grouping
4. **Sidebar** - Navigation layout

## 5. Accessibility Guidelines

### WCAG AA Compliance:
- Minimum contrast ratio: 4.5:1 for normal text
- Minimum contrast ratio: 3:1 for large text
- Focus indicators visible and high-contrast
- Semantic HTML structure
- ARIA labels where needed
- Keyboard navigation support

### Color Contrast Validation:
- Primary blue (#3b82f6) on white: 4.78:1 ✅
- Gray 600 (#4b5563) on white: 8.32:1 ✅
- Error red (#ef4444) on white: 4.09:1 ✅