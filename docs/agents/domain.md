# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the codebase.

## Before exploring, read these

- **`CONTEXT.md`** at the repo root — single-context layout, one glossary for the whole app.
- **`docs/adr/`** — read ADRs that touch the area you're about to work in.

If either doesn't exist for a given topic yet, proceed silently — `/grill-with-docs` creates entries lazily as terms/decisions get resolved.

## File structure (single-context)

```
/
├── CONTEXT.md
├── docs/adr/
│   ├── 0001-user-status-enforced-via-middleware.md
│   ├── 0002-reverb-for-real-time-notifications.md
│   ├── 0003-single-generic-order-notification-class.md
│   ├── 0004-verified-purchase-requires-delivered-status.md
│   ├── 0005-cached-average-rating-columns.md
│   └── 0006-reviews-post-moderated-not-pre-approved.md
└── app/
```

## Use the glossary's vocabulary

When your output names a domain concept (in an issue title, a refactor proposal, a hypothesis, a test name), use the term as defined in `CONTEXT.md` (e.g. **Buyer** not customer, **Order Status** not order state). Don't drift to synonyms the glossary explicitly avoids.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing language the project doesn't use (reconsider) or there's a real gap (note it for `/grill-with-docs`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently overriding:

> _Contradicts ADR-0004 (Verified Purchase requires Delivered) — but worth reopening because…_
