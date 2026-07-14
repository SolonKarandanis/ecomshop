# products.supplier_id is NOT NULL, defaulting to the acting Admin when Suppliers are disabled

Every Product has an owning `supplier_id` (FK to `users`), and the column is `NOT NULL` rather than nullable. Products are only ever created or edited by an Admin via the existing Filament `ProductResource` — Suppliers have no product-management UI. When the Suppliers Feature (`SUPPLIERS_ENABLED`) is on, the Admin picks the owning Supplier from a Select field; when it's off, `supplier_id` is set to the id of the Admin doing the save. Existing Products, created before this feature existed, are backfilled to the first Admin found by id. We chose "no supplier is a valid absence" over a nullable column because supplier scoping (ADR-0008, ADR-0009) needs an owning party for every Product unconditionally — a nullable column would require every downstream query and authorization check to handle a null case that should never actually occur in practice.

## Considered options

- **Nullable `supplier_id`, null meaning "unassigned"** — simpler migration (no backfill required), but pushes a null-check into every place that reads Product ownership, for a state the business never actually wants.
- **NOT NULL, Admin as fallback owner (chosen)** — `supplier_id` always resolves to a real User; the migration backfill is a one-time cost. The Admin owning a Product isn't a "real" Supplier, so an Admin viewing OrderDetailsPage relies on their own role-based access rather than the Supplier-ownership check, which only Suppliers go through.
