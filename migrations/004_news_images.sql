-- Apply once to the existing application database before using News image uploads.
-- Existing articles and their content are preserved.
ALTER TABLE news_articles ADD COLUMN image_path TEXT NULL AFTER content;
