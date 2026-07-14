# Delivered and Cancelled are terminal Order Statuses, with no override for any role

Once an Order reaches `Delivered` or `Cancelled`, no further Order Status transition is permitted by anyone — not the owning Supplier, and not an Admin. Filament's `OrderForm` `ToggleButtons` field previously let an Admin set `order_status` to any value at any time; that unrestricted override is removed as part of this feature. We chose a hard stop over an Admin escape hatch because Order Status now drives Supplier authorization (ADR-0008) and downstream effects like Notifications and Verified Purchase eligibility (ADR-0004) — allowing a terminal status to be silently reopened undermines the guarantee that `Delivered` means the Buyer actually received the Product.

A future reader who finds a wrongly-cancelled or wrongly-delivered Order stuck in that state, with no in-app way to fix it, may wonder why there's no admin override. There isn't one, deliberately; correcting a terminal Order today requires a direct database change.

## Considered options

- **Admin keeps an override, even on terminal statuses** — operationally flexible, lets support staff correct mistakes without touching the database. Rejected: an Order silently reopened after `Delivered` would corrupt Verified Purchase eligibility and Order Status history that other features have started relying on as trustworthy.
- **Terminal statuses, no override for anyone (chosen)** — trustworthy and simple to reason about; the cost is no in-app path to fix a mis-transitioned Order.
