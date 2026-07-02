# Cached Average Rating columns on products, not live aggregation

A Product's Average Rating and review count are stored as columns on `products` (`average_rating`, `reviews_count`) and recalculated whenever a Review is created, edited, or its Review Status changes, rather than computed on demand with `AVG()`/`COUNT()` over the `reviews` table. `ProductsPage` already lists and sorts/filters the full catalog via `ProductSearchFilterDto`, and rating is expected to join that same sort/filter surface — live aggregation across every listed Product on every request would be materially more expensive than reading two columns already on the row.

The trade-off: cached columns can drift from the underlying Reviews if the recalculation step is ever skipped (e.g. a direct DB write that bypasses `ReviewService`). All Review writes must go through `ReviewService` so recalculation isn't optional.

## Considered options

- **Live aggregation** — always correct by construction, no recalculation logic to maintain, but expensive to repeat across every Product in a listing/sort context.
- **Cached columns, recalculated on write** — chosen. Cheap reads everywhere including catalog sort/filter; the cost is a recalculation obligation on every path that writes a Review.
