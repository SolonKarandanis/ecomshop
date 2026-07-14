# Cart and Order are scoped to a single Supplier, not mixed

Once Products carry a `supplier_id`, a Cart is only allowed to hold Products from one Supplier at a time — adding a Product from a different Supplier than what's already in the Cart is rejected at add-to-cart time, not just at checkout. We chose this over allowing mixed-Supplier Carts because Order Status is a single field on `orders`, and a Supplier's ability to transition `Paid` → `Shipped`/`Cancelled` (see ADR-0009) needs one unambiguous owning Supplier per Order. A mixed-Supplier Order would have no single party authorized to act on it.

## Considered options

- **Allow mixed-Supplier Carts, defer the ownership problem** — maximizes Buyer convenience (one cart, any Products), but leaves "who can change this Order's status" undefined for any Order spanning multiple Suppliers.
- **Allow mixed-Supplier Carts, move status tracking to `OrderItem`** — each Supplier would control only the status of their own items within a shared Order. Correct long-term shape for a real multi-vendor marketplace, but a much larger redesign (Order Status semantics, admin UI, Notifications, `HasStatusClasses`, `OrderRepository` queries) than this feature calls for.
- **Single-Supplier Cart, enforced at add-to-cart (chosen)** — keeps `order_status` as one field with one clear owning Supplier. Costs Buyers the ability to check out from multiple Suppliers in one Order.
