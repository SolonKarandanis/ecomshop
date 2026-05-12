# EcomShop Project Documentation

## Project Overview
EcomShop is a modern, high-performance e-commerce platform built with the Laravel framework. It features a robust administration panel powered by Filament and a dynamic, interactive frontend using Livewire and Tailwind CSS. The application supports a full shopping lifecycle, including product discovery, shopping cart management (supporting both guest and authenticated users), secure checkout with Stripe integration, and comprehensive order tracking.

## Core Technologies
- **Backend Framework**: Laravel 12.x
- **Admin Panel**: Filament 4.x
- **Frontend Interactivity**: Livewire 3.x
- **Styling**: Tailwind CSS 4.x with Preline UI
- **Payments**: Stripe (via `stripe/stripe-php`)
- **State Management**: PHP 8.3 features (readonly properties, constructor promotion)
- **Testing**: Pest 3.x

## Architecture & Design Patterns
The project follows a layered architecture to ensure maintainability and testability:

- **Services**: Business logic is encapsulated in Service classes (located in `app/Services`). Examples include `CartService`, `OrderService`, and `StripeService`.
- **Repositories**: Data access logic is abstracted through Repositories (`app/Repositories`), ensuring that controllers and services remain decoupled from the specific data source.
- **DTOs (Data Transfer Objects)**: Structured data passed between layers uses DTOs (`app/Dtos`) to ensure type safety and clarity.
- **Enums**: Fixed sets of values like order statuses, payment methods, and product types are managed via PHP Enums (`app/Enums`).
- **Exceptions**: Domain-specific errors are handled using custom Exception classes (`app/Exceptions`).
- **Models**: Eloquent models (`app/Models`) define the data structure and relationships (Product, Category, Brand, Order, Cart, etc.).

## Building and Running
The project provides convenient composer scripts for common tasks:

- **Initial Setup**:
  ```bash
  composer run setup
  ```
  This command installs PHP and JS dependencies, sets up the `.env` file, generates the application key, runs migrations, and builds frontend assets.

- **Development Environment**:
  ```bash
  composer run dev
  ```
  Starts the local development server, Vite dev server, queue listener, and log tailing concurrently.

- **Testing**:
  ```bash
  composer run test
  ```
  Clears configuration cache and executes the Pest test suite.

## Development Conventions
- **Business Logic**: Always place business logic in Services, not in Models or Controllers.
- **Data Access**: Use Repositories for complex queries or data persistence logic.
- **Type Safety**: Leverage PHP 8.3 type hinting, `readonly` properties, and DTOs for all data transfers.
- **Testing**: Every new feature or bug fix should be accompanied by Pest tests (Feature or Unit as appropriate).
- **Styling**: Adhere to Tailwind CSS 4.x patterns and utilize Preline UI components for consistent design.
- **Naming**: Follow Laravel's naming conventions (CamelCase for classes, snake_case for variables and database columns).
- **State**: Use the provided Enums for any fields with a restricted set of valid values.
