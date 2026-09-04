============================================================
  UPDATE: Multiple Images + Product Video
============================================================

WHAT'S NEW
----------
✓ Upload up to 5 images per product (1 main + 4 additional)
✓ Add a YouTube video URL per product
✓ Product page now shows an image SLIDER (Swiper.js) with
  thumbnails, arrows and swipe support
✓ If a video is added, a "Photos / Video" tab appears above
  the slider — clicking "Video" plays the YouTube video inline
✓ Admin panel: drag-and-drop multi-image upload with live
  preview, and a delete (✕) button on each extra image
✓ Design of the rest of the site is UNCHANGED — only the
  product-add, product-edit, and single product page were
  updated.

FILES CHANGED
-------------
  database/migration.sql          ← NEW — run this once
  includes/functions.php          ← appended new helper functions
  admin/product-add.php           ← rewritten (multi-image + video)
  admin/product-edit.php          ← rewritten (multi-image + video)
  product.php                     ← rewritten (Swiper slider + video tab)

Nothing else was touched. Your homepage, products.php grid,
search.php, categories, admin dashboard, and design/colors
remain exactly as before.


============================================================
  HOW TO APPLY THIS UPDATE
============================================================

IF THIS IS A FRESH INSTALL
---------------------------
Just import database/schema.sql AND THEN database/migration.sql
(in that order) in phpMyAdmin. Everything will already be set up.


IF YOU ALREADY HAVE THE SITE RUNNING (existing database)
-----------------------------------------------------------
1. Open phpMyAdmin → select your `ultranet_security` database
2. Click the "SQL" tab
3. Open the file  database/migration.sql  in a text editor,
   copy all its contents, paste into the SQL box, and click "Go"

   This adds:
     - a new column `video_url` to the `products` table
     - a new table `product_images` (for the extra images)

   Your existing products and images are NOT affected or deleted.

4. Copy these updated files into your project, overwriting the old ones:
     includes/functions.php
     admin/product-add.php
     admin/product-edit.php
     product.php

5. Done! Go to Admin → Edit any product to add more images or a video.


============================================================
  HOW TO USE
============================================================

ADDING A PRODUCT WITH MULTIPLE IMAGES
---------------------------------------
1. Admin → Add Product
2. In "Product Images" box, click or drag up to 5 image files
   at once (hold Ctrl/Cmd while selecting in the file picker)
3. The FIRST image selected becomes the main product image
   (shown on product cards, category grids, etc.)
4. The rest become the additional slider images

ADDING A PRODUCT VIDEO
------------------------
1. Go to YouTube, open the video, click "Share" → copy the link
   (works with youtube.com/watch?v=... or youtu.be/... links)
2. Paste it into the "Product Video" field
3. A live preview appears immediately in the admin panel
4. Save — the video now shows in a "Video" tab on the product page

MANAGING IMAGES ON AN EXISTING PRODUCT
------------------------------------------
1. Admin → Products → Edit (pencil icon)
2. See all current images with an ✕ button on each extra image
   — click it to delete instantly (no page reload)
3. Upload additional images in the remaining open slots
   (max 5 images total per product)

REMOVING A VIDEO
------------------
Edit the product → clear the "Video URL" field → Save.
The Video tab will no longer appear on that product's page.


============================================================
  NOTES
============================================================

- Max 5 images per product (1 main + 4 extra) — this keeps
  product pages fast-loading.
- Only YouTube links are auto-embedded with a player. Other
  video URLs will still be saved and attempted as an iframe.
- The image slider gracefully shows just 1 image (no arrows/
  thumbnails) if a product only has a single image — nothing
  looks broken for older products that haven't been updated yet.
- Deleting a product from Admin → Products still automatically
  removes ALL its images (main + extra) and video reference.

============================================================
