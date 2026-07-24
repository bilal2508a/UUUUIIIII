# Mehmaan Hub

Pakistan's premier property booking platform built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript.

## Features

- **User Authentication** — Login/Register with role-based access (Tenant, Owner, Admin)
- **Property Listings** — Browse, filter, and search properties across Pakistan
- **Property Details** — Image gallery, amenities, reviews, and booking sidebar
- **Booking System** — Date selection, guest details, Smart Travel Checklist, price calculation
- **Payment** — Multiple payment methods (Card, Wallet, Bank Transfer) with coupon codes
- **Wishlist** — Save properties with category stats (Luxury, Budget, Family)
- **Tenant Dashboard** — Travel analytics, top destinations, recent bookings
- **Owner Dashboard** — Manage properties, approve/reject bookings, earnings tracking
- **Admin Dashboard** — Platform overview, user/property/booking management
- **Profile** — Edit profile with travel personality selection
- **Contact & FAQ** — Contact form and searchable FAQ

## Tech Stack

- **Backend:** PHP 8+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3, Bootstrap Icons
- **Font:** Plus Jakarta Sans (Google Fonts)
- **Design:** Modern card-based UI with glass morphism, gradients, and animations

## Setup

1. Import `database/schema.sql` into MySQL
2. Update database credentials in `includes/config.php`
3. Serve the project with PHP's built-in server or XAMPP/WAMP/Laragon

```bash
php -S localhost:8000
```

4. Visit `http://localhost:8000`

## Demo Accounts

| Role    | Email                  |
|---------|------------------------|
| Admin   | admin@mehmaanhub.pk    |
| Owner   | owner@mehmaanhub.pk    |
| Tenant  | tenant@mehmaanhub.pk   |

## Project Structure

```
├── api/                    # API endpoints (booking, payment, wishlist, etc.)
├── assets/
│   ├── css/style.css       # Design system + components
│   └── js/main.js          # Interactive features
├── database/schema.sql     # Database schema + seed data
├── includes/               # Shared components (config, auth, header, footer)
├── index.php               # Landing page
├── properties.php          # Property listing
├── property-details.php    # Single property
├── booking.php             # Booking form
├── bookings.php            # My bookings
├── payment.php             # Payment page
├── wishlist.php            # Saved properties
├── dashboard.php           # Tenant dashboard
├── owner-dashboard.php     # Owner dashboard
├── add-property.php        # Add property form
├── profile.php             # User profile
├── admin.php               # Admin dashboard
├── about.php               # About page
├── contact.php             # Contact page
├── faq.php                 # FAQ page
├── login.php               # Login page
├── register.php            # Registration page
└── logout.php              # Logout handler
```

## License

MIT
