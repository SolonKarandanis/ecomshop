# Single generic OrderNotification class for all order-status events

All six order-status Notification types (`ORDER_CREATED`, `ORDER_PAYMENT_CONFIRMED`, `ORDER_PAYMENT_FAILED`, `ORDER_SHIPPED`, `ORDER_DELIVERED`, `ORDER_CANCELLED`) share an identical payload shape: `event_type`, `order_id`, `order_url`, and `message`. We chose one generic `OrderNotification` class that takes the event type as a constructor argument rather than one class per event.

The Laravel convention is one class per notification, and a future reader may wonder why we deviated. The reason: the six events are structurally identical — there is no per-event channel, driver, or payload customisation to justify the extra classes. The event type is stored in the JSON `data` field and used by the broadcast listener to route display logic on the frontend.

## Considered options

- **One class per event** — idiomatic Laravel, self-documenting, each class independently customisable. Rejected because all six events are identical in structure and behaviour.
- **Single `OrderNotification` class** — chosen. One dispatch point shape, one broadcast event name (`App.Notifications.OrderNotification`), frontend filters by `event_type` in the payload.
