# Ecomshop

A Laravel/Livewire storefront with a Filament admin panel. Buyers browse products, place orders, and pay via Stripe. Admins manage the catalogue, users, and orders.

## Language

**User**:
Any authenticated account on the platform — can be a Buyer, Admin, or Supplier depending on their role.
_Avoid_: Account, member

**Buyer**:
A User with the `buyer` role. The only role that can add to cart and place orders.
_Avoid_: Customer, shopper, client

**Supplier**:
A User with the `supplier` role. Owns one or more Products, and is the only role (besides Admin) able to transition the Order Status of an Order containing their Products, from `Paid` onward. Only choosable at registration, and only while the Suppliers feature is enabled — see **Suppliers Feature**.
_Avoid_: Vendor, seller, merchant

**User Status**:
A two-state flag on a User — `active` or `inactive`. An inactive User cannot authenticate on the storefront or access the admin panel. Status is admin-controlled only; Users cannot change their own status. New Users default to `active`.
_Avoid_: User state, account status, enabled/disabled

**Profile**:
The storefront page at `/profile` where an authenticated User updates their name, email, and password, and views past shipping addresses. Admins additionally see their own User Status here (read-only).
_Avoid_: Account page, settings page, user page

**Address**:
A shipping address snapshot recorded at the time an Order is placed. Belongs to both an Order and a User. It is a point-in-time record of where an Order was shipped — not a reusable saved address. The same physical address appearing on multiple orders produces multiple Address records.
_Avoid_: Saved address, contact, shipping profile

**Order**:
A completed purchase placed by a Buyer. Has an Order Status and a Payment Status tracked as separate enum fields.
_Avoid_: Purchase, transaction, booking

**Cart**:
A temporary collection of items a Buyer intends to purchase. Stored in cookies for guests and in the database for authenticated Buyers. May only contain Products from a single Supplier — mixed-Supplier Carts are rejected at add-to-cart time.
_Avoid_: Basket, bag

**Suppliers Feature**:
A system-wide toggle (`SUPPLIERS_ENABLED`) that controls whether the Supplier role can be chosen at registration and whether existing Suppliers can act on Order Status. Checked live on every request — disabling it does not alter any existing User's role or any Product's ownership, it only withdraws the ability to act while off. When disabled, every Product's owning Supplier is instead whichever Admin created or last edited it.
_Avoid_: Feature flag, supplier mode

**Review**:
Feedback a Buyer leaves on a Product: a required 1–5 star Rating plus optional written text. Eligible only for a Buyer with a **Verified Purchase** of that Product. Exactly one Review per Buyer per Product, ever. The Buyer may edit their Rating/text at any time; only an Admin can remove a Review (by hiding it — see Review Status), the Buyer cannot delete their own. Auto-published on submission.
_Avoid_: Rating (alone), comment, feedback

**Review Status**:
A two-state flag on a Review — `published` or `hidden`. New Reviews default to `published` (no approval queue); an Admin can hide a Review after the fact for moderation reasons. Hidden Reviews are excluded from public display and from the Product's Average Rating.
_Avoid_: Approved/pending, review state

**Average Rating**:
A Product's mean star Rating across its `published` Reviews, stored as a cached value on the Product and recalculated whenever a Review is created, edited, or its Review Status changes. Paired with a cached Review count.
_Avoid_: Score, rank, review summary

**Admin Reply**:
A single optional public reply an Admin can attach to a Review, shown alongside it on the Product page. Not available to Buyers or Suppliers. No "helpful" voting exists on Reviews — out of scope for this iteration.
_Avoid_: Response, comment, seller reply

**Verified Purchase**:
The condition that qualifies a Buyer to leave a Review on a Product: the Buyer has an OrderItem for that Product on an Order whose Order Status is `Delivered`.
_Avoid_: Confirmed purchase, eligible buyer

**Notification**:
An in-app alert delivered to a Buyer when their Order changes state. Stored via Laravel's database notification driver (`notifications` table). Surfaced in the storefront Navbar via a bell-icon dropdown and on a dedicated `/notifications` page.
_Avoid_: Alert, message, push notification

**Unread Notification**:
A Notification with a null `read_at` timestamp — not yet viewed by the Buyer. Auto-marked as read when the Buyer opens the notification dropdown.
_Avoid_: New notification, unseen notification

## Relationships

- A **User** has exactly one **User Status**
- A **Buyer** has a **Cart** and can place one or more **Orders**
- A **Buyer** has zero or more **Notifications**
- An **Order** has exactly one **Address** (the shipping address at the time of purchase)
- An **Address** belongs to one **Order** and one **User**
- A **Profile** belongs to one **User**
- Every Product has exactly one owning **Supplier** (or, when the **Suppliers Feature** is off, the Admin who created/edited it)
- A **Supplier** may transition an Order's Order Status from `Paid` to `Shipped` or `Cancelled`, and from `Shipped` to `Delivered`, only for Orders containing their Products. `Delivered` and `Cancelled` are terminal for every role, including Admin.

## Example dialogue

> **Dev:** "Should we let the User save an **Address** for reuse at checkout?"
> **Domain expert:** "No — an **Address** is a snapshot of where a specific **Order** was shipped. The **Profile** shows past addresses read-only, tied to their **Order**. There's no saved address book."

> **Dev:** "If a **Buyer** is set to inactive, can they still browse products?"
> **Domain expert:** "They can't even log in. **User Status** is enforced at authentication — inactive means the session is terminated before they reach any page."

## Flagged ambiguities

- "address" could mean a reusable saved contact or a per-order snapshot — resolved: in this codebase, **Address** is always a per-order snapshot tied to a specific Order.
