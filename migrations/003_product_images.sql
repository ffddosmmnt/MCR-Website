-- Apply once to the existing application database, before importing content.
-- Existing products, inventory and orders are preserved.
ALTER TABLE products ADD COLUMN image_path TEXT NULL AFTER details;
