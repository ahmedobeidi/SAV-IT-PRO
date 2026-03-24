# Organized source structure

This version keeps the project behavior intact while making the structure more coherent:

- `app/`: app bootstrap, routes, layouts, and centralized global styles
- `features/`: domain modules (auth, clients, equipment, profile, repairs, users)
- `shared/`: reusable API helpers, shared UI components, pagination, flash helpers, and errors

## CSS organization

Global styling was grouped under `src/app/styles/`:

- `theme.css`: design tokens, colors, base theme variables
- `layout.css`: base UI elements like buttons, cards, inputs, labels
- `pages.css`: page layouts, headers, form shells, overlays, page toolbars
- `components.css`: reusable component styles such as tables, pagination, loading states, and topbar helpers

## Main cleanup done

- kept the existing feature-based architecture
- centralized repeated page and component inline styles into global CSS files
- made layouts use reusable CSS classes instead of inline layout objects
- standardized common table, toolbar, modal, and form page patterns
- preserved the existing business logic and routing structure

No business logic was intentionally changed.
