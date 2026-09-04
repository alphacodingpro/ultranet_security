============================================================
  UPDATE: CCTV System Calculator + Package Builder
============================================================

WHAT'S NEW
----------
✓ New public page: /calculator.php — clients enter camera count,
  system type (IP/Analog), resolution, recording days, cable
  length etc. and instantly see:
    - Recommended NVR/DVR (real product from your catalog)
    - Required HDD storage size
    - Required PoE switch wattage
    - Total cable length + warning if over 100m
✓ Two modes:
    "Just Calculate"  → shows technical numbers only, no pricing
    "Build My Package" → shows actual matching products (with
                          images) from YOUR catalog, real prices
                          you set in Admin, a package discount,
                          and a running total
✓ When a client submits a package, they enter Name, Phone, Email
  — this is saved as a lead and a unique estimate is generated
✓ /estimate.php?ref=XXXX — a clean, branded, printable estimate
  page. Client clicks "Download / Print PDF" → browser print
  dialog opens → they choose "Save as PDF". No extra PHP
  libraries needed, works out of the box on any host.
✓ New Admin section "CALCULATOR":
    - Client Requests  → list of every calculator submission,
      with status (New/Contacted/Closed), call/WhatsApp buttons,
      and a link to view the full estimate PDF
    - Calculator Settings → control the package discount %,
      PoE safety headroom %, and default camera wattage
✓ Admin → Add/Edit Product now has a "Calculator Specs" box where
  you tag each product's role (Camera / NVR / DVR / HDD / PoE
  Switch / Cable) and its technical spec (channels, storage,
  PoE ports+wattage, camera wattage, price-per-metre for cable).
  This is how the calculator picks the RIGHT real product.

DESIGN: unchanged. All new pages reuse your existing colours,
fonts, and component styles (btn-red, cards, section-title, etc).


============================================================
  FILES CHANGED / ADDED
============================================================

  database/schema.sql                    ← updated (fresh installs)
  patch/migration-calculator.sql         ← NEW — run once if upgrading
  includes/functions.php                 ← appended calculator functions
  includes/header.php                    ← added "Calculator" nav link
  index.php                              ← added calculator promo banner
  calculator.php                         ← NEW — main calculator page
  calculator-submit.php                  ← NEW — saves lead (AJAX)
  estimate.php                           ← NEW — printable estimate/PDF
  assets/js/calculator.js                ← NEW — calculation logic
  admin/includes/header.php              ← added Calculator sidebar menu
  admin/index.php                        ← added Calculator Leads stat
  admin/products.php                     ← added "Calc Type" column
  admin/product-add.php                  ← added Calculator Specs box
  admin/product-edit.php                 ← added Calculator Specs box
  admin/calculator-requests.php          ← NEW — leads list
  admin/calculator-request-view.php      ← NEW — lead detail view
  admin/calculator-settings.php          ← NEW — discount/settings


============================================================
  HOW TO APPLY THIS UPDATE
============================================================

IF THIS IS A FRESH INSTALL
---------------------------
Just import database/schema.sql — everything is already included.


IF YOU ALREADY HAVE THE SITE RUNNING
--------------------------------------
1. Open phpMyAdmin → select your `ultranet_security` database
2. Click "SQL" tab
3. Open  database/patch/migration-calculator.sql  in a text editor,
   copy all contents, paste into the SQL box, click "Go"

   This adds:
     - product_type, channels, storage_gb, poe_ports,
       poe_budget_watts, camera_watts, price_per_meter
       columns to the `products` table (all nullable —
       your existing products are not affected)
     - a `settings` table (discount %, etc.)
     - a `calculator_requests` table (client leads)
     - tags your existing sample products (if using demo data)
     - adds 2 sample PoE switches + 1 cable product so the
       calculator works immediately

4. Copy ALL the changed/new files listed above into your project,
   overwriting the old ones.

5. Done! Visit /calculator.php to test it.


============================================================
  HOW TO SET UP YOUR PRODUCTS FOR THE CALCULATOR
============================================================

For the calculator to recommend YOUR real products, go to
Admin → Products → Add/Edit each relevant product and set:

  CAMERAS:
    Product Type = Camera
    Camera Power Draw (Watts) = e.g. 7.5

  NVR (for IP systems):
    Product Type = NVR
    Number of Channels = e.g. 8, 16, 32

  DVR (for Analog systems):
    Product Type = DVR
    Number of Channels = e.g. 4, 8, 16

  HARD DISKS:
    Product Type = Hard Disk (HDD)
    Storage Capacity (GB) = e.g. 1000 (for 1TB), 2000 (2TB)

  PoE SWITCHES:
    Product Type = PoE Switch
    PoE Ports = e.g. 8, 16, 24
    Power Budget (Watts) = e.g. 120, 250, 400

  CABLE:
    Product Type = Cable
    Price per Metre (PKR) = e.g. 35

Check Admin → Calculator Settings to see a live status of how
many products you have tagged for each type — it will warn you
if a type has zero products (the calculator will show "no
matching product" for that category until you add one).


============================================================
  HOW THE CALCULATION WORKS (for your reference)
============================================================

HDD Storage:
  Uses standard H.265 bitrate estimates per resolution:
    2MP ≈ 2 Mbps · 4MP ≈ 4 Mbps · 5MP ≈ 5 Mbps · 8MP ≈ 8 Mbps
  Motion-only recording assumes ~40% of the day is actively
  recording; Continuous assumes 24 hours/day.
  Formula: GB = (bitrate/8) × 3600 × hours/day × days × cameras / 1024

PoE Switch Wattage:
  Total = camera_count × camera_watts × (1 + headroom%)
  Headroom defaults to 20% (adjustable in Calculator Settings)

NVR/DVR:
  Picks the smallest channel-count product that still covers
  your camera count (e.g. 6 cameras → 8-channel NVR, not 16).

Package Discount:
  A flat % (default 5%, adjustable) applied to the subtotal
  ONLY when a client uses "Build My Package" mode.

Prices are ALWAYS pulled live from your products table — never
hardcoded — so if you change a product's price in Admin, the
calculator and every new estimate reflect it immediately.


============================================================
  VIEWING CLIENT LEADS
============================================================

Admin → Client Requests shows every submitted package with:
  - Client name, phone, email
  - Full requirement breakdown (cameras, storage, PoE wattage)
  - Itemised product list with images and prices
  - One-click Call / WhatsApp / Email buttons
  - Status dropdown: New → Contacted → Closed
  - Direct link to view/print the exact PDF estimate they got

============================================================
