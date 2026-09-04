============================================================
  UltraNet Security – PHP + MySQL Website with Admin Panel
  Complete Setup Guide
============================================================

FOLDER STRUCTURE
----------------
ultranet-security-php/
│
├── index.php               ← Homepage
├── products.php            ← Product listing (filter, category, brand)
├── product.php             ← Single product detail page
├── search.php              ← Search results page
├── .htaccess               ← URL rewriting & security rules
├── .env                    ← ⚠ Your credentials (KEEP PRIVATE)
├── robots.txt              ← Search engine crawl rules
├── sitemap.xml             ← SEO sitemap (update with your domain)
├── site.webmanifest        ← PWA / mobile manifest
│
├── config/
│   └── config.php          ← Loads .env, sets constants
│
├── includes/
│   ├── db.php              ← PDO database connection
│   ├── functions.php       ← All helper functions
│   ├── header.php          ← Site header (navbar, meta tags)
│   ├── footer.php          ← Site footer + CTA
│   ├── product-card.php    ← Reusable product card partial
│   └── 404.php             ← 404 error page
│
├── admin/
│   ├── login.php           ← Admin login (credentials from .env)
│   ├── logout.php          ← Clears session
│   ├── index.php           ← Dashboard with stats
│   ├── products.php        ← List / filter / search products
│   ├── product-add.php     ← Add new product with image upload
│   ├── product-edit.php    ← Edit existing product
│   ├── product-delete.php  ← Delete product + remove image
│   ├── categories.php      ← List categories
│   ├── category-add.php    ← Add new category
│   ├── category-edit.php   ← Edit category
│   ├── category-delete.php ← Delete category (cascades products)
│   └── includes/
│       ├── header.php      ← Admin topbar + sidebar
│       └── footer.php      ← Admin footer + JS
│
├── assets/
│   ├── css/
│   │   ├── style.css       ← Public site CSS
│   │   └── admin.css       ← Admin panel CSS
│   ├── js/
│   │   └── main.js         ← Public site JS (reveal, counter, smooth scroll)
│   └── img/                ← Static images (replace with your real photos)
│
├── uploads/
│   └── products/           ← Admin-uploaded product images (auto-created)
│
└── database/
    └── schema.sql          ← Run this in phpMyAdmin to set up the database


============================================================
  STEP-BY-STEP SETUP
============================================================

STEP 1 – Set Up MySQL Database
-------------------------------
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named: ultranet_security
3. Click "Import", choose: database/schema.sql
4. Click "Go" – tables + sample data will be created


STEP 2 – Configure Your Credentials
-------------------------------------
Open the .env file and update:

  DB_HOST=localhost
  DB_NAME=ultranet_security
  DB_USER=root                  ← your MySQL username
  DB_PASS=your_password         ← your MySQL password

  ADMIN_USERNAME=admin          ← change this
  ADMIN_PASSWORD=Admin@12345    ← change this to something strong

  SITE_URL=http://localhost/ultranet-security-php   ← your URL


STEP 3 – Place Files on Server
--------------------------------
LOCAL (XAMPP / WAMP):
  Copy the "ultranet-security-php" folder into:
    XAMPP: C:\xampp\htdocs\
    WAMP:  C:\wamp64\www\
  Access at: http://localhost/ultranet-security-php/

LIVE HOSTING (cPanel):
  Upload ALL files to public_html/ via File Manager or FTP
  Run schema.sql via phpMyAdmin in your hosting cPanel
  Update SITE_URL in .env to: https://www.yourdomainname.pk


STEP 4 – Test the Site
-----------------------
  Homepage:      http://localhost/ultranet-security-php/
  Products:      http://localhost/ultranet-security-php/products.php
  Search:        http://localhost/ultranet-security-php/search.php?q=hikvision
  Admin Login:   http://localhost/ultranet-security-php/admin/login.php

  Default Admin Login:
    Username: admin
    Password: Admin@12345


============================================================
  ADDING REAL IMAGES
============================================================

Replace placeholder images in assets/img/ with your real photos.
Keep the same filenames:

  hero-bg.jpg       → Wide shot of installed cameras or building (1600×900)
  hero-camera.jpg   → Close-up of a CCTV camera (800×600)
  service-*.jpg     → Service category photos (600×400 each)
  why-us.jpg        → Your team or control room (700×480)

For PRODUCT images:
  Upload them through the Admin Panel (Admin → Add/Edit Product → Image)
  They are stored in: uploads/products/

Compress before uploading: https://squoosh.app or https://tinypng.com
Target: under 200KB per image for fast loading.


============================================================
  ADMIN PANEL FEATURES
============================================================

Dashboard      → Stats: total products, categories, featured count
Products       → List, search, filter by category/status
Add Product    → Name, brand, SKU, description, price, old price,
                 stock status, category, image upload, SEO meta
Edit Product   → All fields + change image (old image auto-deleted)
Delete Product → Removes DB record + uploaded image file
Categories     → List with product count
Add Category   → Name, SEO slug, description, Font Awesome icon
Edit Category  → All fields
Delete Category→ Cascades: deletes all products in that category

SEO per product:
  - Custom meta title
  - Custom meta description
  - Auto-generated SEO slug from product name
  - Category breadcrumbs


============================================================
  SEO FEATURES
============================================================

✓ Dynamic meta title + description per page
✓ Open Graph (WhatsApp/Facebook link preview)
✓ Schema.org LocalBusiness JSON-LD (Google Maps info)
✓ Schema.org FAQPage (FAQ rich results)
✓ Canonical URLs
✓ SEO-friendly product URLs: /product/hikvision-2mp-dome-camera
✓ sitemap.xml + robots.txt
✓ Image alt tags with keywords
✓ Lazy loading on images
✓ Breadcrumb navigation
✓ robots.txt blocks /admin/ and /config/ from indexing


============================================================
  CUSTOMIZATION QUICK REFERENCE
============================================================

What to change         Where
---------------------  --------------------------------
Phone number           includes/header.php, footer.php, index.php
Address                includes/footer.php, index.php (contact section)
WhatsApp link          includes/header.php, footer.php, product-card.php
Colors (accent red)    assets/css/style.css  → :root --accent
Domain URL             .env → SITE_URL, also update sitemap.xml
Admin credentials      .env → ADMIN_USERNAME, ADMIN_PASSWORD
Google Maps link       index.php contact section
Social media links     includes/footer.php


============================================================
  PRODUCTION CHECKLIST
============================================================

Before going live:
[ ] Change ADMIN_USERNAME and ADMIN_PASSWORD in .env
[ ] Update SITE_URL in .env to your real domain
[ ] Update sitemap.xml with your real domain
[ ] Update canonical URLs in config/config.php area
[ ] Set  ini_set('display_errors', 0)  in config/config.php
[ ] Ensure uploads/products/ is writable (chmod 755)
[ ] Submit sitemap to Google Search Console
[ ] Create Google Business Profile at business.google.com
[ ] Replace all placeholder images in assets/img/

============================================================
Built for UltraNet Security, Karachi | 2025
============================================================
