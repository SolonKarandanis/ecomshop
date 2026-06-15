# PRD: Order Status Notifications

## Problem Statement

Buyers have no in-app visibility into what is happening with their orders after checkout. They receive a confirmation email when an order is placed, but there is no persistent, real-time signal in the storefront when their payment is confirmed, their order ships, or their order is cancelled. Buyers must navigate to the My Orders page and refresh manually to see any updates.

## Solution

Add a real-time notification bell icon to the storefront Navbar. When an Order's state changes, the affected Buyer receives an in-app Notification that increments the unread badge on the bell. Clicking the bell opens a dropdown showing the last 5 unread Notifications, which are auto-marked as read on open, with a link to a dedicated `/notifications` page listing the full history.

## User Stories

1. As a Buyer, I want to see a bell icon in the Navbar, so that I always know where to find my notifications.
2. As a Buyer, I want the bell icon to show a badge with my unread notification count, so that I know at a glance whether anything needs my attention.
3. As a Buyer, I want the unread badge to update in real-time without refreshing the page, so that I don't miss a status change that happened while I was browsing.
4. As a Buyer, I want to click the bell and see a dropdown of my most recent unread notifications, so that I can quickly understand what changed without leaving the current page.
5. As a Buyer, I want each notification in the dropdown to link directly to the relevant Order, so that I can navigate to the order details in one click.
6. As a Buyer, I want the notifications in the dropdown to be automatically marked as read when I open it, so that the unread count accurately reflects what I haven't seen yet.
7. As a Buyer, I want a "see all" link in the dropdown, so that I can view my full notification history.
8. As a Buyer, I want a dedicated `/notifications` page that lists all my notifications, so that I can review past status changes at any time.
9. As a Buyer, I want to receive a notification when I successfully place an Order, so that I have in-app confirmation that my order was created.
10. As a Buyer, I want to receive a notification when my payment is confirmed by Stripe, so that I know my order is being processed.
11. As a Buyer, I want to receive a notification when my payment fails, so that I can take action to resolve the issue.
12. As a Buyer, I want to receive a notification when my Order is shipped, so that I know it is on its way.
13. As a Buyer, I want to receive a notification when my Order is delivered, so that I have a record of successful delivery.
14. As a Buyer, I want to receive a notification when my Order is cancelled, so that I am not left wondering what happened to my order.
15. As a Buyer, I want notification messages to be clear and human-readable (e.g. "Your order #42 has been shipped."), so that I understand the update without needing to interpret codes or statuses.
16. As a Buyer, I do not want to see notifications intended for other Buyers, so that my notification history remains private and relevant.
17. As a Buyer, I want the notification bell to only appear when I am logged in, so that the Navbar is not confusing for guests.

## Implementation Decisions

- **Storage**: Laravel's built-in database notification driver (`notifications` table, `php artisan notifications:table`). The `User` model already uses the `Notifiable` trait — no new infrastructure needed for persistence.

- **Notification class**: One generic `OrderNotification` class (implements `ShouldQueue` and `ShouldBroadcast`) rather than one class per event type. All six event types share the same payload shape; the event type is carried as a field in the JSON `data` column.

- **Payload shape**: Minimal — `event_type`, `order_id`, `order_url` (pre-rendered link to `/my-orders/{id}`), and `message` (pre-rendered human-readable string). No grand total or other order fields in the payload.

- **`NotificationEventTypeEnum`**: New enum with six cases following the existing `order.*` dot-notation convention:
  - `ORDER_CREATED = 'order.notification.created'`
  - `ORDER_PAYMENT_CONFIRMED = 'order.notification.payment_confirmed'`
  - `ORDER_PAYMENT_FAILED = 'order.notification.payment_failed'`
  - `ORDER_SHIPPED = 'order.notification.shipped'`
  - `ORDER_DELIVERED = 'order.notification.delivered'`
  - `ORDER_CANCELLED = 'order.notification.cancelled'`

- **Real-time delivery**: Laravel Reverb (self-hosted WebSockets) + Laravel Echo on the client. The `OrderNotification` broadcasts on a private channel scoped to the authenticated Buyer (`private-user.{id}`). See ADR 0002.

- **Trigger boundaries** (two separate layers to avoid double-firing on the `Paid` status transition):
  - `OrderService::checkout()` dispatches `ORDER_CREATED`
  - `OrderService::successOrFailStripeOrder()` dispatches `ORDER_PAYMENT_CONFIRMED` or `ORDER_PAYMENT_FAILED`, and also sets `OrderStatusEnum::Paid` on success (bug fix — currently only `OrderPaymentStatusEnum::PAID` is set)
  - A new `OrderObserver` on `Order::updated()` dispatches `ORDER_SHIPPED`, `ORDER_DELIVERED`, and `ORDER_CANCELLED` when `order_status` changes to those values — explicitly ignoring the `Paid` transition to prevent double-firing

- **Navbar integration**: The existing `Navbar` Livewire component is extended with an unread notification count property. The bell badge is driven by the Echo WebSocket listener updating the count client-side on broadcast receipt, with the Livewire component as the fallback render target.

- **Dropdown behaviour**: Shows the 5 most recent unread Notifications. Calling the open action runs `auth()->user()->unreadNotifications()->latest()->take(5)->get()->markAsRead()`. A "see all" link points to `/notifications`.

- **Notifications page**: A new full-page Livewire component at `/notifications` (authenticated route), listing all Notifications for the Buyer with `read_at` timestamps. Paginated.

- **Recipient scope**: Buyers only. Admins receive no Notifications via this system — admin-side order alerts remain in Filament's own notification layer.

## Testing Decisions

Good tests assert observable behaviour — what the Buyer sees and what ends up in the database — not which internal class dispatched a notification or which observer method ran.

**Seam 1 — Notification dispatch**
Using `Notification::fake()`, assert that `OrderNotification` is sent to the correct Buyer with the correct `event_type` data when each trigger fires: `OrderService::checkout()`, `OrderService::successOrFailStripeOrder()` for both PAID and FAILED Stripe statuses, and direct `order_status` model updates to Shipped, Delivered, and Cancelled. Also assert that updating `order_status` to Paid does NOT dispatch a second notification (the double-fire guard). Prior art: Feature test pattern from `ProfilePageTest`.

**Seam 2 — Navbar Livewire component**
Assert the bell badge renders the correct unread count for an authenticated Buyer who has unread Notifications. Assert the badge is absent for guests. Assert that calling the open action marks the Notifications as read and the count drops to zero. Prior art: `NavbarTest.php`.

**Seam 3 — `/notifications` page**
Assert the page redirects unauthenticated requests to `/login`. Assert it renders for an authenticated Buyer and lists their Notifications. Prior art: `ProfilePageTest.php` (authenticated Livewire full-page component pattern).

## Out of Scope

- Admin-panel notifications (Filament has its own notification system; this PRD does not touch it)
- Email notifications (the existing `OrderPlaced` mailable covers order confirmation email; no new email notifications are added here)
- SMS or mobile push notifications
- Notification preferences or opt-out controls per event type
- Pagination on the Navbar dropdown (shows 5 most recent only; full list is on the `/notifications` page)
- Bulk delete or archive of notifications
- Supplier role notifications

## Further Notes

- The `gh` CLI is not installed in this environment. Publish this PRD to GitHub Issues at `https://github.com/SolonKarandanis/ecomshop` manually.
- ADR 0002 (`docs/adr/0002-reverb-for-real-time-notifications.md`) records the Reverb vs polling decision.
- ADR 0003 (`docs/adr/0003-single-generic-order-notification-class.md`) records the single notification class decision.
- `CONTEXT.md` has been updated with the `Notification` and `Unread Notification` domain terms.
