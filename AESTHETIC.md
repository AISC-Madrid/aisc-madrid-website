# AISC Madrid — Color Guide

The site is light-themed: near-white backgrounds, dark text, and two bright brand colors used for accents, buttons and links.

## Brand colors

| Name | Hex | Use |
| --- | --- | --- |
| Pink (primary) | `#EB178E` | Buttons, links, hovers, footer gradient, highlights |
| Cyan (secondary) | `#20CCF1` | Secondary buttons, form buttons, hover state of pink buttons |
| Pink dark | `#D43089` / `#C4106F` | Hover/pressed shades of the pink |

Defined in `css/styles3.css` as CSS variables:

```css
:root {
  --background: hsl(300, 3%, 7%);   /* near-black */
  --foreground: hsl(240, 7%, 97%);  /* near-white — used as page background */
  --primary:   #EB178E;             /* pink */
  --secondary: #20CCF1;             /* cyan */
  --muted:     hsl(180, 2%, 10%);
}
```

## Neutrals

| Hex | Use |
| --- | --- |
| `#FFFFFF` | Cards, navbar, panels |
| `#FAFAFA` `#F9F9F9` `#F8F8F8` `#F5F5F5` `#F4F4F4` | Section and card backgrounds |
| `#EEEEEE` `#E2E2E2` `#CCCCCC` | Borders and dividers |
| `#A5A5A5` `#757575` | Muted / secondary text |
| `#555555` `#495057` | Body text |
| `#333333` | Headings and strong text |

Soft tints of the brand colors are used for backgrounds:
`rgba(235, 23, 142, .05–.15)` (pink), `rgba(32, 204, 241, .15)` (cyan),
plus `#FCE4F3` (pink tint), `#E8F7FF` (cyan tint), `#F8F4FF` (violet tint).

Shadows are always plain black at low opacity: `rgba(0, 0, 0, 0.05 / 0.06 / 0.2)`.

## Category badges

| Hex | Use |
| --- | --- |
| `#25D366` | WhatsApp button |
| `#0B66C3` | LinkedIn button |
| `#CC0000` / `#FF6B7A` | Error and danger text |

## Rules of thumb

- Pink is the main action color; cyan is the hover/alternate.
- Keep text dark on light backgrounds — never brand pink on cyan or vice versa.
- New accents should reuse `var(--primary)` / `var(--secondary)` rather than new hex values.
- The footer uses a left-to-right gradient from near-white through a pink tint to full `#EB178E`.
