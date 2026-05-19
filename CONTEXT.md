# Ecomshop

A Laravel/Livewire storefront with a Filament admin panel. Buyers browse products, place orders, and pay via Stripe. Admins manage the catalogue, users, and orders.

## Language

**User**:
Any authenticated account on the platform — can be a Buyer, Admin, or Supplier depending on their role.
_Avoid_: Account, member

**Buyer**:
A User with the `buyer` role. The only role that can add to cart and place orders.
_Avoid_: Customer, shopper, client

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
A temporary collection of items a Buyer intends to purchase. Stored in cookies for guests and in the database for authenticated Buyers.
_Avoid_: Basket, bag

## Relationships

- A **User** has exactly one **User Status**
- A **Buyer** has a **Cart** and can place one or more **Orders**
- An **Order** has exactly one **Address** (the shipping address at the time of purchase)
- An **Address** belongs to one **Order** and one **User**
- A **Profile** belongs to one **User**

## Example dialogue

> **Dev:** "Should we let the User save an **Address** for reuse at checkout?"
> **Domain expert:** "No — an **Address** is a snapshot of where a specific **Order** was shipped. The **Profile** shows past addresses read-only, tied to their **Order**. There's no saved address book."

> **Dev:** "If a **Buyer** is set to inactive, can they still browse products?"
> **Domain expert:** "They can't even log in. **User Status** is enforced at authentication — inactive means the session is terminated before they reach any page."

## Flagged ambiguities

- "address" could mean a reusable saved contact or a per-order snapshot — resolved: in this codebase, **Address** is always a per-order snapshot tied to a specific Order.
