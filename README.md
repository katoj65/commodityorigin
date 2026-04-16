# Commodity Origin 🫘

> **Crop to cup, fully traceable.** Commodity Origin is a coffee traceability platform that connects Ugandan coffee farmers to international buyers — with full supply chain transparency, compliance documentation, and origin verification at every step.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-4FC08D?style=flat&logo=vuedotjs&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-7C3AED?style=flat&logo=inertia&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38BDF8?style=flat&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)

🌐 **Live:** [beanorigin.com](https://beanorigin.com) &nbsp;|&nbsp; 📁 **Repo:** [github.com/yourusername/commodity-origin](https://github.com/yourusername/commodity-origin)

---

## The Problem

Uganda is one of Africa's top coffee producers, yet most farmers have no visibility beyond the farm gate — and buyers have no reliable way to verify the origin, quality, or compliance status of the coffee they purchase. This disconnect erodes trust, reduces farmer income, and makes export compliance a manual, error-prone process.

## What Commodity Origin Does

Commodity Origin digitises the entire coffee supply chain — from farm registration to export documentation — giving every stakeholder a single source of truth.

| Who | What they get |
|---|---|
| **Farmers** | Farm registration, harvest logging, income tracking |
| **Exporters / Buyers** | Origin-verified batches, compliance documents, direct farmer profiles |
| **Regulators / Auditors** | Full traceability records and export compliance reports |

### Core Features

- **Farm & Farmer Registry** — Onboard farmers with geo-tagged farm profiles, variety and acreage data
- **Harvest & Batch Tracking** — Log harvests, processing methods, and batch weights at every stage
- **Origin Verification** — Each coffee batch gets a unique traceability ID linking it back to a specific farm and farmer
- **Buyer–Farmer Marketplace** — Connects verified farmers directly to buyers with transparent pricing
- **Export Compliance** — Generates and manages export documentation to meet Uganda Coffee Development Authority (UCDA) standards
- **Reporting & Analytics** — Dashboard for production trends, batch histories, and farmer earnings

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 10 |
| Frontend | Vue.js 3 + Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL |
| Architecture | Monolith SPA via Inertia (no separate API layer) |

---

## Screenshots

> *(Add screenshots here — farm dashboard, batch tracking view, buyer marketplace)*

```
/screenshots
  ├── dashboard.png
  ├── farm-profile.png
  ├── batch-tracking.png
  └── marketplace.png
```

---

## Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/commodity-origin.git
cd commodity-origin

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env
DB_DATABASE=bean_origin
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# 6. Run migrations and seed sample data
php artisan migrate --seed

# 7. Build frontend assets
npm run dev

# 8. Start the development server
php artisan serve
```

Visit `http://localhost:8000`

---

## Project Structure

```
commodity-origin/
├── app/
│   ├── Http/Controllers/     # Laravel controllers
│   ├── Models/               # Eloquent models (Farmer, Batch, Farm, etc.)
│   └── Services/             # Business logic layer
├── resources/
│   ├── js/
│   │   ├── Pages/            # Vue.js page components (Inertia)
│   │   └── Components/       # Reusable UI components
│   └── css/                  # Tailwind CSS entry
├── routes/
│   └── web.php               # All routes (Inertia-driven)
└── database/
    ├── migrations/
    └── seeders/
```

---

## Roadmap

- [ ] Mobile app (Ionic + Capacitor) for farmer field agents
- [ ] QR code scanning for batch verification by end buyers
- [ ] SMS notifications for farmers (via Africa's Talking API)
- [ ] Integration with UCDA export registry API
- [ ] Multi-language support (English + Luganda)

---

## About the Developer

Built by **[Your Name]** — a full-stack developer based in Uganda with 6 years of experience building web and mobile applications for African markets.

- 🌍 [yourportfolio.dev](https://yourportfolio.dev)
- 💼 [linkedin.com/in/yourprofile](https://linkedin.com/in/yourprofile)
- 🐙 [github.com/yourusername](https://github.com/yourusername)

---

## License

This project is proprietary software. All rights reserved © 2024 Commodity Origin.

---

> *"Traceability is not just about compliance — it's about dignity for the farmer and trust for the buyer."*
