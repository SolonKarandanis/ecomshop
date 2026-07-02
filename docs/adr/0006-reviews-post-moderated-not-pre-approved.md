# Reviews are post-moderated (auto-publish + Admin hide), not pre-approved

A Review is published immediately on submission; there is no `pending` state and no approval queue. An Admin can hide a Review afterward via the `Reviews` Filament resource, but nothing blocks it from being publicly visible first. We chose this over a pre-approval workflow because pre-approval adds real friction — reviews feel stale to Buyers waiting on approval, and someone has to actively staff the queue — for a store at this scale, while auto-publish plus Admin takedown still gives full abuse/spam protection, just reactively.

A future reader seeing Admin tooling for Reviews might assume there's an approval step gating visibility; there isn't. Visibility is controlled entirely by Review Status (`published`/`hidden`) applied after the fact.

## Considered options

- **Pre-moderation (approval queue)** — Admin must approve before a Review is visible. Rejected: adds latency and an ongoing staffing cost with no corresponding trust benefit for a store this size.
- **Post-moderation (auto-publish + Admin hide)** — chosen. Reviews are visible instantly; Admins moderate reactively.
