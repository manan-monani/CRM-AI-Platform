# Extracted Frontend Design Specifications

- **Date**: 2026-07-05
- **Task**: Extract design tokens, color palette, typography guidelines, and screen specifications to a root `design.md` file for Figma mockup references.
- **Reference File**: [design.md](file:///d:/Projects/6amtech project/ai-crm-demo-v1.0/ai-crm-demo/design.md)

## Summary of Findings

1. **Color System**: Default styles fallback to variables in `config/branding/colors.php` and dynamically map to CSS custom properties defined in the HTML head. Supports dynamic branding updates from settings.
2. **Typography**: Utilizes `"Instrument Sans"` (400, 500, 600 weight) via Bunny Fonts as the primary font, and `"Roboto"` as the secondary fallback font.
3. **Screen Archetypes**: Identified Guest (Auth, Landing, Lead Capture), Customer (Dashboard, Profile, Deals index/creation/details), and Admin (Pipelines for leads/customers/deals, settings for custom branding, role management, and team directories) categories.
