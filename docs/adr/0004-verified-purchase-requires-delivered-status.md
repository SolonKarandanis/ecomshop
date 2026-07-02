# Verified Purchase requires Delivered, not just Paid

A Buyer becomes eligible to leave a Review only once one of their Orders containing the Product reaches `OrderStatusEnum::Delivered` — not as soon as it's `Paid`. We chose the stricter gate deliberately: a Review is meant to reflect experience with the physical product, and a Buyer who has only paid hasn't received it yet. Gating on `Delivered` keeps "Verified Purchase" a meaningful trust signal instead of a rubber stamp on checkout completion.

## Considered options

- **Gate on `Paid`** — lets Buyers review immediately after checkout, maximizing review volume and speed. Rejected because reviews written before delivery can't speak to the product itself (only to checkout/payment), which undermines the "Verified Purchase" label.
- **Gate on `Delivered`** — chosen. Slower to accumulate reviews, but every Review implies the Buyer actually had the product in hand.
