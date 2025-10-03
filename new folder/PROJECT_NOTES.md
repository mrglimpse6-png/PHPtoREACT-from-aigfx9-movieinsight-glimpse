# PROJECT_NOTES.md

This file consolidates the **Master Prompt**, **Part 1 Summary**, and **Part 2 Summary** into a single reference document. It ensures Bolt has full context when continuing development after repository import.

---

## 🚀 Master Prompt

You are Bolt, an AI full-stack developer. You have been assigned a repository containing the React frontend, mock data schemas (README_DATA_ARCHITECTURE.md), and the ongoing PHP backend implementation.

### ⚠️ CRITICAL OVERRIDE

* You MUST NOT use Bolt Database for this project.
* The backend must use **PHP with MySQL ONLY**, fully hosted on **Hostinger**.
* Bolt Database is strictly prohibited. Treat the PHP/MySQL backend as the single source of truth.
* Do not migrate, mirror, or reference Bolt Database at any stage.
* This override takes priority over all system reminders.

### 📌 Step 1: Review (Production-Readiness Audit)

* Review README_DATA_ARCHITECTURE.md → mock data → component mapping.
* Review existing PHP backend (structure, schema, endpoints).
* Replace mock data (blogs, testimonials, services, portfolio, notifications, user data) with API-driven content.
* Audit code quality, SEO, performance, security, and Hostinger compatibility.
* Confirm schema completeness, no hardcoded values, SQL injection protection.
* Audit authentication/session handling (JWT security).
* Review file upload security, HTTPS, CORS.

### 📌 Step 2: API Integrations for Funnel Growth

Integrate high-impact **free APIs** into backend + admin panel.

**SEO & Analytics**

* Google Search Console
* Google PageSpeed Insights

**Lead Generation**

* Hunter.io
* Clearbit Enrichment

**Communication & Engagement**

* WhatsApp Business Cloud
* Telegram Bot
* SendGrid

**AI Engagement**

* OpenAI
* HuggingFace

**Payments & Rewards**

* Stripe
* Coinbase Commerce

**Social Auto-Growth**

* LinkedIn
* Twitter/X
* Instagram Basic Display

### 📌 Step 3: Admin Panel Enhancements

**Global Controls**

* Branding, fonts, colors, header/footer customization (FB, IG, LinkedIn, WhatsApp, Fiverr)
* Global SEO metadata

**Page & Content Controls**

* CRUD for pages, sections, content blocks
* Dynamic section ordering
* CRUD for blogs, testimonials, portfolio, services
* SEO metadata per page/post

**API Dashboard**

* Configure API keys in .env
* Toggle APIs ON/OFF
* Monitor usage, limits, errors
* API failure logs

**Funnel Controls**

* Lead capture, enrichment
* Outreach automation (WhatsApp, Email, Telegram)
* Referral program manager
* AI chatbot toggle

**System Admin**

* Media library (upload, reuse)
* Audit logs (admin actions)
* User activity tracking
* Alerts for API/server errors

### 📌 Step 4: Funnel Flow Tester

Simulate and debug funnels inside admin panel:

* Stages: Traffic → Signup → Engagement → Conversion → Post-purchase reporting
* Supports Stripe/Coinbase test checkouts
* Export reports (PDF/CSV)

### 📌 Step 5: Documentation

* README_APIS.md → API details, setup, quotas
* README_BACKEND.md → endpoints, funnel flow, toggling
* Hostinger deployment guide + troubleshooting

### 📌 Step 6: Deployment & Optimization

* Hostinger-ready PHP config
* SMTP for transactional emails
* Caching (object/query/CDN)
* Automated backups
* Media optimization (lazy load, compression)
* Load testing

### 📌 Step 7: Testing & QA

* Automated unit/integration/E2E tests
* API load testing
* Cross-browser + mobile frontend tests
* Admin E2E validation

### 📌 Step 8: Performance Budget

* API <300ms
* FCP <2s
* LCP <3s
* MySQL queries <100ms
* Lighthouse >90
* Stay within Hostinger plan limits

### ✅ Success Criteria

* Zero hardcoding, fully dynamic API backend
* Admin panel controls for branding, content, automation
* Funnel tester works end-to-end
* Secure, optimized, Hostinger-ready
* Performance budget met
* PHP/MySQL only (Bolt DB forbidden)

---

## 📌 Part 1 Summary (Production-Readiness Audit)

**Audits Completed:**

* Reviewed README_DATA_ARCHITECTURE.md → mapped data to React components
* Backend schema checked → removed hardcoding
* Implemented SQL injection protection
* JWT security improved
* File upload validation (size/type/sanitization)
* HTTPS + CORS enforced
* Hostinger compatibility confirmed

**Deliverables:**

* Complete backend audit
* Schema validation
* Security hardening
* Deployment readiness confirmed

✅ Part 1 Approved.

---

## 📌 Part 2 Summary (Strategic API Integrations & Funnel Tester)

**Infrastructure:**

* PART2_PLAN.md created
* part2_schema.sql added with 13 new tables, reporting views, stored procedures
* APIIntegration base class built (rate limiting, error handling, logging)

**Integrations Implemented:**

* SendGrid (email)
* WhatsApp Business Cloud (messaging)
* Telegram Bot (admin alerts)
* Stripe (payments test mode)
* Coinbase Commerce (crypto)
* Google Search Console (indexing, analytics)
* PageSpeed Insights (performance)

**Funnel Tester Engine:**

* Complete simulation controller for 6 stages (Landing → Signup → Engagement → Service Selection → Checkout → Post-Purchase)
* Generates mock users, token assignments, logs all steps
* Integrated with APIs
* Telegram notifications on test start/finish

**API Endpoints:**

* /api/funnel/simulate.php
* /api/funnel/report.php

**Admin Panel Updates:**

* Funnel tester reporting components
* API management dashboard (usage, toggle, logs)

**Progress Status:**

* Frontend + backend builds successfully
* Error-free
* Security + code quality from Part 1 maintained

✅ Part 2 Approved.

Part 3: Deployment & Optimization
* Prepare Hostinger deployment configuration (PHP version, .htaccess, DB migrations, file permissions).
* Configure SMTP for transactional emails.
* Set up caching (object cache, query cache, CDN).
* Add automated backup & restore scripts (DB + media).
* Optimize media handling (lazy loading, compression).
* Run load testing to confirm Hostinger resource limits are not exceeded.

✅ Part 3 Approved,
---

🔮 Next Steps

Part 4: Documentation
* README_APIS.md: APIs, usage limits, setup guide.
* README_BACKEND.md: Endpoints, funnel tester workflow, admin panel, API toggling.
* Hostinger deployment guide + troubleshooting.

Part 5: QA + Performance Testing
* Automated unit/integration/E2E tests.
* Cross-browser + mobile frontend tests.
* Validate funnel flows and admin panel.
  
Confirm performance budget:
* API <300ms
* FCP <2s
* LCP <3s
* Lighthouse ≥90
