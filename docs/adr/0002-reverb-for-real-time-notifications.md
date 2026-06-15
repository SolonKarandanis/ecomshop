# Reverb (WebSockets) for real-time notification delivery

Order-status Notifications needed to appear in the Buyer's Navbar bell icon without a page reload. We chose Laravel Reverb (self-hosted WebSockets) over polling because payment confirmation and shipment events happen within seconds of user action — a 30-second polling lag is noticeable at those moments.

## Considered options

- **Polling (`wire:poll`)** — zero extra infrastructure, fits the Livewire-first stack. Dropped because the lag on `ORDER_PAYMENT_CONFIRMED` is perceptible UX.
- **Pusher** — same WebSocket protocol as Reverb but hosted by a third party. Rejected to avoid per-message costs and an external runtime dependency.
- **Laravel Reverb (self-hosted)** — chosen. One extra process in production, no third-party costs, uses the same Laravel Echo client as Pusher.
