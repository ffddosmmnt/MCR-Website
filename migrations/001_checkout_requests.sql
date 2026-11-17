-- Select the application database before running this migration.
CREATE TABLE IF NOT EXISTS checkout_requests (
    user_id INT UNSIGNED NOT NULL,
    request_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    payload_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    ticket_order_id INT UNSIGNED DEFAULT NULL,
    merch_order_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, request_key),
    UNIQUE KEY uq_checkout_ticket (ticket_order_id),
    UNIQUE KEY uq_checkout_merch (merch_order_id),
    CONSTRAINT fk_checkout_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_checkout_ticket FOREIGN KEY (ticket_order_id) REFERENCES ticket_orders(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_checkout_merch FOREIGN KEY (merch_order_id) REFERENCES merch_orders(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT chk_checkout_one_order CHECK ((ticket_order_id IS NULL) <> (merch_order_id IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
