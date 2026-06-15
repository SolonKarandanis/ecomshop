# Order Notifications — GitHub Issues

Create these in the order listed. Once you create each issue, note its number and fill it in wherever "Blocked by #N" appears in later issues.

---

## Issue 1: Reverb + notifications table infrastructure

### Labels: `ready-for-agent`

## What to build

Set up the two infrastructure prerequisites for the order notification system end-to-end:

1. Run `php artisan notifications:table` and commit the resulting migration so the `notifications` table exists for Laravel's database notification driver.
2. Run `php artisan install:broadcasting` to scaffold Reverb configuration. Configure `.env` with Reverb host, port, and app credentials. Ensure the broadcasting driver is set to `reverb`.
3. Add the Reverb server process to the `composer run dev` concurrent process list so it runs alongside the existing `serve`, `queue:listen`, `pail`, and `vite` processes.

No UI changes. No notification dispatching. This slice is purely infrastructure — the goal is that the `notifications` table exists and a Reverb server can start.

## Acceptance criteria

- [ ] `notifications` table migration exists and runs cleanly via `php artisan migrate`
- [ ] `config/broadcasting.php` has a `reverb` driver configured
- [ ] `.env.example` is updated with the required Reverb environment variable keys (no real credentials — placeholders only)
- [ ] `composer run dev` starts a Reverb process alongside the existing processes
- [ ] `php artisan reverb:start` starts without error in a clean environment

## Blocked by

None — can start immediately.

---

## Issue 2: `NotificationEventTypeEnum` + generic `OrderNotification` class

### Labels: `ready-for-agent`

## What to build

Create the two core building blocks that all notification dispatching depends on:

1. A new `NotificationEventTypeEnum` with six cases following the existing `order.*` dot-notation convention:
   - `ORDER_CREATED = 'order.notification.created'`
   - `ORDER_PAYMENT_CONFIRMED = 'order.notification.payment_confirmed'`
   - `ORDER_PAYMENT_FAILED = 'order.notification.payment_failed'`
   - `ORDER_SHIPPED = 'order.notification.shipped'`
   - `ORDER_DELIVERED = 'order.notification.delivered'`
   - `ORDER_CANCELLED = 'order.notification.cancelled'`

2. A single generic `OrderNotification` class (implements `ShouldQueue` and `ShouldBroadcast`) that accepts a `NotificationEventTypeEnum` case, an order ID, and a pre-rendered message string at construction time. It sends via the `database` and `broadcast` channels. The `toDatabase()` payload carries: `event_type`, `order_id`, `order_url` (pre-rendered link to `/my-orders/{id}`), and `message`. The `toBroadcast()` payload mirrors `toDatabase()`.

3. A private broadcast channel `user.{id}` authenticated to the channel's owner, so Buyers only receive their own broadcasts.

No dispatching yet — that comes in later slices.

## Acceptance criteria

- [ ] `NotificationEventTypeEnum` exists with all six cases using `order.notification.*` values
- [ ] `OrderNotification` accepts `NotificationEventTypeEnum`, `order_id`, and `message` as constructor arguments
- [ ] `OrderNotification` sends on both `database` and `broadcast` channels
- [ ] The broadcast channel is private and scoped to the authenticated Buyer (`private-user.{id}`)
- [ ] `toDatabase()` payload contains `event_type`, `order_id`, `order_url`, and `message`
- [ ] `toBroadcast()` payload mirrors `toDatabase()`

## Blocked by

- #1

---

## Issue 3: ORDER_CREATED notification + Navbar bell icon + dropdown

### Labels: `ready-for-agent`

## What to build

The first fully demoable vertical slice: place an order and the Navbar bell lights up.

1. Dispatch an `OrderNotification` with `ORDER_CREATED` event type from `OrderService::checkout()` immediately after the order is committed (alongside the existing `OrderPlaced` mail).

2. Extend the `Navbar` Livewire component with:
   - A bell icon visible only to authenticated Buyers
   - An unread count badge driven by `auth()->user()->unreadNotifications()->count()`
   - A dropdown (opened by clicking the bell) that loads the 5 most recent unread notifications, marks them all as read on open (`markAsRead()`), and renders each as a linked message pointing to `/my-orders/{order_id}`
   - A "see all" link at the bottom of the dropdown pointing to `/notifications`

The unread count is read from the database on component mount and on dropdown close. Real-time badge updating via WebSockets is handled in a later slice.

**Tests:**
- `Notification::fake()` — assert `OrderNotification` is dispatched to the Buyer with `event_type = ORDER_CREATED` when `checkout()` completes
- Navbar Livewire test — assert the bell badge shows the correct unread count for a Buyer with unread notifications, and is absent for guests
- Navbar Livewire test — assert calling the open-dropdown action marks notifications as read and the count drops to zero

## Acceptance criteria

- [ ] `OrderNotification` with `ORDER_CREATED` is dispatched when `checkout()` completes successfully
- [ ] Bell icon is visible in the Navbar for authenticated Buyers and hidden for guests
- [ ] Bell badge shows the correct unread notification count
- [ ] Opening the dropdown renders the 5 most recent unread notifications
- [ ] Each notification in the dropdown links to `/my-orders/{order_id}`
- [ ] Opening the dropdown marks all shown notifications as read
- [ ] A "see all" link in the dropdown points to `/notifications`
- [ ] Tests pass: dispatch assertion, badge count, mark-as-read behaviour

## Blocked by

- #2

---

## Issue 4: Payment notifications + `OrderStatusEnum::Paid` bug fix

### Labels: `ready-for-agent`

## What to build

Wire up payment-outcome notifications and fix a latent bug where `OrderStatusEnum::Paid` is never set on successful Stripe payment.

1. In `OrderService::successOrFailStripeOrder()`: when the Stripe session `payment_status` is `paid`, also set `order_status = OrderStatusEnum::Paid` on the order (currently only `payment_status` is updated — this is a bug).

2. Dispatch `OrderNotification` with `ORDER_PAYMENT_CONFIRMED` after a successful payment save.

3. Dispatch `OrderNotification` with `ORDER_PAYMENT_FAILED` after a failed payment save.

**Tests:**
- Assert `OrderStatusEnum::Paid` is set on the order when Stripe returns a paid status
- `Notification::fake()` — assert `ORDER_PAYMENT_CONFIRMED` is dispatched to the Buyer on successful payment
- `Notification::fake()` — assert `ORDER_PAYMENT_FAILED` is dispatched to the Buyer on failed payment

## Acceptance criteria

- [ ] `order_status` is set to `OrderStatusEnum::Paid` when Stripe payment succeeds
- [ ] `ORDER_PAYMENT_CONFIRMED` notification is dispatched to the Buyer on payment success
- [ ] `ORDER_PAYMENT_FAILED` notification is dispatched to the Buyer on payment failure
- [ ] No notification is dispatched when `successOrFailStripeOrder()` throws (DB rollback path)
- [ ] Tests pass for both payment outcomes and the bug fix

## Blocked by

- #2

---

## Issue 5: Admin-triggered notifications via `OrderObserver`

### Labels: `ready-for-agent`

## What to build

Notify Buyers when an admin changes an order's status to Shipped, Delivered, or Cancelled — regardless of whether the change comes from the inline table `SelectColumn` or the edit form.

1. Create an `OrderObserver` registered on the `Order` model. In its `updated()` method, check whether `order_status` was changed. If so, dispatch `OrderNotification` for the new status:
   - `OrderStatusEnum::Shipped` → `ORDER_SHIPPED`
   - `OrderStatusEnum::Delivered` → `ORDER_DELIVERED`
   - `OrderStatusEnum::Cancelled` → `ORDER_CANCELLED`
   - `OrderStatusEnum::Paid` → **no notification** (handled by `OrderService` in issue #4 to prevent double-firing)

2. Register the observer in `AppServiceProvider` (or `EventServiceProvider` if preferred) to keep service wiring explicit, consistent with how services are registered.

**Tests:**
- `Notification::fake()` — assert `ORDER_SHIPPED` is dispatched to the Buyer when `order_status` transitions to `Shipped`
- `Notification::fake()` — same for `ORDER_DELIVERED` and `ORDER_CANCELLED`
- Assert that transitioning `order_status` to `Paid` does **not** dispatch an additional notification (the double-fire guard)
- Assert that non-status field changes on `Order` do not trigger any notification

## Acceptance criteria

- [ ] `OrderObserver` dispatches `ORDER_SHIPPED`, `ORDER_DELIVERED`, `ORDER_CANCELLED` on the corresponding `order_status` transitions
- [ ] `OrderStatusEnum::Paid` transition in the observer dispatches no notification
- [ ] Non-status field changes do not trigger notifications
- [ ] Observer catches changes made via both the Filament table `SelectColumn` and the edit form
- [ ] Tests pass for all four cases (3 notified + 1 guard)

## Blocked by

- #2

---

## Issue 6: Real-time Navbar badge update via Echo + Reverb

### Labels: `ready-for-agent`

## What to build

Make the Navbar bell badge increment in real-time when a notification arrives — no page reload required.

1. In the storefront JS (`resources/js/app.js` or a dedicated file), initialise Laravel Echo with the Reverb driver. For authenticated Buyers, subscribe to the private channel `user.{id}` (the user ID should be injected from the Blade layout into the JS scope).

2. On receiving a broadcast event on that channel, increment the Navbar badge count client-side. The Navbar Livewire component should expose a way for JS to trigger a re-render or accept an `$dispatch` event (following the existing `cartUpdated` Livewire event pattern) so the badge stays consistent with server state.

3. The Echo subscription should only be initialised when a Buyer is authenticated — guests receive no subscription.

**Tests:**
- Navbar Livewire test — assert the component responds to a `notificationReceived` Livewire event by incrementing the unread count (mirrors the existing `cartUpdated` / `#[On]` pattern in `Navbar.php`)

## Acceptance criteria

- [ ] Laravel Echo is initialised with the Reverb driver in the storefront JS
- [ ] Authenticated Buyers subscribe to `private-user.{id}` on page load
- [ ] Receiving a broadcast on that channel increments the Navbar badge count without a page reload
- [ ] Guests do not initialise an Echo subscription
- [ ] The Navbar Livewire component handles a `notificationReceived` event to re-sync its count from the server
- [ ] Test passes: `notificationReceived` event increments the badge

## Blocked by

- #1
- #3

---

## Issue 7: `/notifications` full-history page

### Labels: `ready-for-agent`

## What to build

A dedicated page where Buyers can review their full notification history.

1. Create a new full-page Livewire component `NotificationsPage` mounted at `/notifications`. Add the route to the existing `auth` + `CheckUserStatus` middleware group (consistent with `/profile`, `/my-orders`, etc.).

2. The page lists all of the authenticated Buyer's notifications in reverse chronological order, paginated. Each row shows the notification message, a link to the relevant order (`/my-orders/{order_id}`), and the timestamp. Unread notifications are visually distinguished from read ones (e.g. a dot or bold style — the exact style is an implementation choice).

3. The "see all" link in the Navbar dropdown (added in issue #3) points to this page.

**Tests:**
- Assert `/notifications` redirects unauthenticated requests to `/login`
- Assert the page renders successfully for an authenticated Buyer
- Assert notifications belonging to other Buyers are not visible

## Acceptance criteria

- [ ] `/notifications` route exists under the `auth` + `CheckUserStatus` middleware group
- [ ] Page renders for authenticated Buyers and lists their notifications paginated
- [ ] Each notification links to the correct order detail page
- [ ] Read and unread notifications are visually distinguished
- [ ] Notifications from other Buyers are not shown
- [ ] Tests pass: guest redirect, authenticated render, privacy guard

## Blocked by

- #3
