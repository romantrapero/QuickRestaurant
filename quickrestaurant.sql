-- -------------------------------------------------------------
-- TablePlus 6.7.8(650)
--
-- https://tableplus.com/
--
-- Database: quickrestaurant
-- Generation Time: 2025-12-14 14:19:33.2880
-- -------------------------------------------------------------


DROP TABLE IF EXISTS "public"."migrations";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS migrations_id_seq;

-- Table Definition
CREATE TABLE "public"."migrations" (
    "id" int4 NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
    "migration" varchar(255) NOT NULL,
    "batch" int4 NOT NULL,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."users";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS users_id_seq;

-- Table Definition
CREATE TABLE "public"."users" (
    "id" int8 NOT NULL DEFAULT nextval('users_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "email" varchar(255) NOT NULL,
    "email_verified_at" timestamp(0),
    "password" varchar(255) NOT NULL,
    "remember_token" varchar(100),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."password_reset_tokens";
-- Table Definition
CREATE TABLE "public"."password_reset_tokens" (
    "email" varchar(255) NOT NULL,
    "token" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    PRIMARY KEY ("email")
);

DROP TABLE IF EXISTS "public"."sessions";
-- Table Definition
CREATE TABLE "public"."sessions" (
    "id" varchar(255) NOT NULL,
    "user_id" int8,
    "ip_address" varchar(45),
    "user_agent" text,
    "payload" text NOT NULL,
    "last_activity" int4 NOT NULL,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."cache";
-- Table Definition
CREATE TABLE "public"."cache" (
    "key" varchar(255) NOT NULL,
    "value" text NOT NULL,
    "expiration" int4 NOT NULL,
    PRIMARY KEY ("key")
);

DROP TABLE IF EXISTS "public"."cache_locks";
-- Table Definition
CREATE TABLE "public"."cache_locks" (
    "key" varchar(255) NOT NULL,
    "owner" varchar(255) NOT NULL,
    "expiration" int4 NOT NULL,
    PRIMARY KEY ("key")
);

DROP TABLE IF EXISTS "public"."jobs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS jobs_id_seq;

-- Table Definition
CREATE TABLE "public"."jobs" (
    "id" int8 NOT NULL DEFAULT nextval('jobs_id_seq'::regclass),
    "queue" varchar(255) NOT NULL,
    "payload" text NOT NULL,
    "attempts" int2 NOT NULL,
    "reserved_at" int4,
    "available_at" int4 NOT NULL,
    "created_at" int4 NOT NULL,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."job_batches";
-- Table Definition
CREATE TABLE "public"."job_batches" (
    "id" varchar(255) NOT NULL,
    "name" varchar(255) NOT NULL,
    "total_jobs" int4 NOT NULL,
    "pending_jobs" int4 NOT NULL,
    "failed_jobs" int4 NOT NULL,
    "failed_job_ids" text NOT NULL,
    "options" text,
    "cancelled_at" int4,
    "created_at" int4 NOT NULL,
    "finished_at" int4,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."failed_jobs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS failed_jobs_id_seq;

-- Table Definition
CREATE TABLE "public"."failed_jobs" (
    "id" int8 NOT NULL DEFAULT nextval('failed_jobs_id_seq'::regclass),
    "uuid" varchar(255) NOT NULL,
    "connection" text NOT NULL,
    "queue" text NOT NULL,
    "payload" text NOT NULL,
    "exception" text NOT NULL,
    "failed_at" timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."categories";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS categories_id_seq;

-- Table Definition
CREATE TABLE "public"."categories" (
    "id" int8 NOT NULL DEFAULT nextval('categories_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "order" int4 NOT NULL DEFAULT 0,
    "is_active" bool NOT NULL DEFAULT true,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."dishes";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS dishes_id_seq;

-- Table Definition
CREATE TABLE "public"."dishes" (
    "id" int8 NOT NULL DEFAULT nextval('dishes_id_seq'::regclass),
    "category_id" int8 NOT NULL,
    "name" varchar(255) NOT NULL,
    "description" text,
    "cost_price" numeric(10,2) NOT NULL,
    "sale_price" numeric(10,2) NOT NULL,
    "is_available" bool NOT NULL DEFAULT true,
    "image_url" varchar(255),
    "preparation_time" int4 NOT NULL DEFAULT 15,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."order_items";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS order_items_id_seq;

-- Table Definition
CREATE TABLE "public"."order_items" (
    "id" int8 NOT NULL DEFAULT nextval('order_items_id_seq'::regclass),
    "order_id" int8 NOT NULL,
    "dish_id" int8 NOT NULL,
    "quantity" int4 NOT NULL DEFAULT 1,
    "unit_price" numeric(10,2) NOT NULL,
    "total_price" numeric(10,2) NOT NULL,
    "special_instructions" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."orders";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS orders_id_seq;

-- Table Definition
CREATE TABLE "public"."orders" (
    "id" int8 NOT NULL DEFAULT nextval('orders_id_seq'::regclass),
    "order_number" varchar(255) NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'pending'::character varying CHECK ((status)::text = ANY ((ARRAY['pending'::character varying, 'preparing'::character varying, 'ready'::character varying, 'delivered'::character varying, 'cancelled'::character varying])::text[])),
    "total" numeric(10,2) NOT NULL,
    "table_number" varchar(255),
    "customer_notes" text,
    "completed_at" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "customer_name" varchar(150),
    PRIMARY KEY ("id")
);

INSERT INTO "public"."migrations" ("id", "migration", "batch") VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_12_000000_create_restaurant_tables', 1);

INSERT INTO "public"."users" ("id", "name", "email", "email_verified_at", "password", "remember_token", "created_at", "updated_at") VALUES
(1, 'Administrador', 'admin@restaurant.com', '2025-12-13 07:54:18', '$2y$12$jLc3wY4McynuNWePpHGipO5uswV9.7m6TFb2PEAvlmJu6B0iBgo7i', 'kw7R6nXeVnwvnHzaR8dwLHBi8yPaa9TphpMibqcVCUjKaxqfqYzs0okuqPBu', '2025-12-13 07:54:18', '2025-12-13 09:23:21');

INSERT INTO "public"."sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES
('bHUP2Q9B5qjyaC1xbbY13c3fD4EFMXlRv6ZhQFBR', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoibENQS3g0Z3R2TFFlNjkwMVZkcVNsMm50d3FnYUUwZ0lYWm1oMEV5TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9vcmRlcnMiO3M6NToicm91dGUiO3M6Mzc6ImZpbGFtZW50LmFkbWluLnJlc291cmNlcy5vcmRlcnMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkakxjM3dZNE1jeW51TldlUHBIR2lwTzV1c3dWOS43bTZURmIyUEVBdmxtSnU2QjBpQmdvN2kiO3M6NjoidGFibGVzIjthOjQ6e3M6NDA6Ijk4ZWY2NDgwZmNhNGZjZjRlNmRlNjA4MzhhMTc0NDI5X2NvbHVtbnMiO2E6OTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJjYXRlZ29yeS5uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJDYXRlZ29yw61hIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJuYW1lIjtzOjU6ImxhYmVsIjtzOjE2OiJOb21icmUgZGVsIFBsYXRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY29zdF9wcmljZSI7czo1OiJsYWJlbCI7czo1OiJDb3N0byI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InNhbGVfcHJpY2UiO3M6NToibGFiZWwiO3M6MTU6IlByZWNpbyBkZSBWZW50YSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImlzX2F2YWlsYWJsZSI7czo1OiJsYWJlbCI7czo2OiJBY3Rpdm8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6ImltYWdlX3VybCI7czo1OiJsYWJlbCI7czo2OiJJbWFnZW4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE2OiJwcmVwYXJhdGlvbl90aW1lIjtzOjU6ImxhYmVsIjtzOjE4OiJQcmVwYXJhY2nDs24gKG1pbikiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjY6IkNyZWFkbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fWk6ODthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidXBkYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMToiQWN0dWFsaXphZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319czo0MDoiZTc5M2EyNzlkNTZlNDUwNjA5NzU0MDIwZDYyN2JlZWNfY29sdW1ucyI7YTo4OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6Im9yZGVyX251bWJlciI7czo1OiJsYWJlbCI7czoxMDoiIyBkZSBPcmRlbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6ImN1c3RvbWVyX25hbWUiO3M6NToibGFiZWwiO3M6NzoiQ2xpZW50ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6InN0YXR1c19iYWRnZSI7czo1OiJsYWJlbCI7czowOiIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6InRvdGFsIjtzOjU6ImxhYmVsIjtzOjU6IlRvdGFsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoidGFibGVfbnVtYmVyIjtzOjU6ImxhYmVsIjtzOjEzOiJUaXBvIGRlIE9yZGVuIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czo5OiJDcmVhZG8gRW4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTQ6IkFjdHVhbGl6YWRvIEVuIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6ImRkYzFkMDhlYmVmYTY1MjI5MDNhYjFmMzdjM2NiOGFjX2NvbHVtbnMiO2E6NTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NjoiTm9tYnJlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJvcmRlciI7czo1OiJsYWJlbCI7czo1OiJPcmRlbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiaXNfYWN0aXZlIjtzOjU6ImxhYmVsIjtzOjY6IkFjdGl2byI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6NjoiQ3JlYWRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjExOiJBY3R1YWxpemFkbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX1zOjQwOiIzOThhMjM0MjY0ZDFhYmEwOGMwOTFjMzNjZjQ0ZTM2Y19jb2x1bW5zIjthOjQ6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJkaXNoLm5hbWUiO3M6NToibGFiZWwiO3M6ODoiUGxhdGlsbG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjg6InF1YW50aXR5IjtzOjU6ImxhYmVsIjtzOjg6IkNhbnRpZGFkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidW5pdF9wcmljZSI7czo1OiJsYWJlbCI7czoxNToiUHJlY2lvIFVuaXRhcmlvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToidG90YWxfcHJpY2UiO3M6NToibGFiZWwiO3M6NToiVG90YWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19', 1765743493);

INSERT INTO "public"."cache" ("key", "value", "expiration") VALUES
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1765694302),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1765694302;', 1765694302),
('laravel-cache-livewire-rate-limiter:16d36dff9abd246c67dfac3e63b993a169af77e6', 'i:1;', 1765735623),
('laravel-cache-livewire-rate-limiter:16d36dff9abd246c67dfac3e63b993a169af77e6:timer', 'i:1765735623;', 1765735623);

INSERT INTO "public"."categories" ("id", "name", "order", "is_active", "created_at", "updated_at") VALUES
(1, 'Aguachiles', 2, 't', '2025-12-13 07:54:18', '2025-12-14 18:05:15'),
(2, 'Ceviches', 1, 't', '2025-12-13 07:54:18', '2025-12-14 00:04:08'),
(3, 'Bebidas', 3, 't', '2025-12-13 07:54:18', '2025-12-14 00:15:32'),
(4, 'Extras', 4, 't', '2025-12-14 00:27:15', '2025-12-14 02:15:02');

INSERT INTO "public"."dishes" ("id", "category_id", "name", "description", "cost_price", "sale_price", "is_available", "image_url", "preparation_time", "created_at", "updated_at") VALUES
(1, 1, 'Aguachile Celestino''s', 'Camarón enchilado, camarón cocido y pulpo en salsa verde, acompañado con pepino y cebolla cortado en julianas. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.', 125.00, 285.00, 't', '01KCC5ZNW6P7BGFJRJ5XQX8VSG.png', 15, '2025-12-13 07:54:18', '2025-12-14 05:36:18'),
(2, 2, 'Ceviche Culichi', 'Camarón cocido, callo de róbalo y pulpo sumergido en kermato, acompañando con pepino, tomate y cebolla cortados finamente. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.', 95.00, 230.00, 't', '01KCC6A4FDF4JCZAZZX46P1ZXP.png', 15, '2025-12-13 07:54:18', '2025-12-14 00:29:21'),
(3, 3, 'Preparado Grande', 'Preparato refrescante y especiado a base de Kermato sin alcohol, servido bien frío con hielo. Se realza con un toque de limón fresco y sal, se completa con salsas suaves que resaltan su carácter intenso y equilibrado.', 25.00, 60.00, 't', NULL, 5, '2025-12-13 07:54:18', '2025-12-14 06:35:33'),
(4, 4, 'Tostadas', 'Tostadas 100% Sinaloenses', 5.00, 15.00, 't', NULL, 0, '2025-12-14 01:03:45', '2025-12-14 02:14:37'),
(5, 1, 'Aguachile del Pacifico', 'Callo de róbalo, atún y pulpo en salsa negra, acompañado con pepino y cebolla cortado en julianas. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.', 120.00, 270.00, 't', '01KCDAFF1PY3D4S14D4YQ8J86X.png', 15, '2025-12-14 02:19:18', '2025-12-14 02:19:18'),
(6, 3, 'Preparado Chico', 'Preparato refrescante y especiado a base de Kermato sin alcohol, servido bien frío con hielo. Se realza con un toque de limón fresco y sal, se completa con salsas suaves que resaltan su carácter intenso y equilibrado.', 15.00, 35.00, 't', NULL, 5, '2025-12-14 06:34:56', '2025-12-14 06:34:56'),
(7, 4, 'Tostitos', NULL, 10.00, 15.00, 't', NULL, 0, '2025-12-14 06:36:01', '2025-12-14 06:36:01'),
(8, 2, 'Ceviche Sinaloense', 'Camarón curtido, camarón cocido y pulpo sumergido en kermato, acompañando con pepino tomate y cebolla cortados finamente. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.', 95.00, 230.00, 't', '01KCDS8Z6PZX4PZMK3EBJ6B7DE.png', 15, '2025-12-14 06:37:54', '2025-12-14 06:37:54');

INSERT INTO "public"."order_items" ("id", "order_id", "dish_id", "quantity", "unit_price", "total_price", "special_instructions", "created_at", "updated_at") VALUES
(28, 17, 8, 1, 230.00, 230.00, NULL, '2025-12-14 20:10:12', '2025-12-14 20:10:12'),
(29, 17, 1, 1, 285.00, 285.00, NULL, '2025-12-14 20:10:12', '2025-12-14 20:10:12'),
(30, 18, 5, 2, 270.00, 540.00, NULL, '2025-12-14 20:10:45', '2025-12-14 20:10:45'),
(31, 18, 3, 2, 60.00, 120.00, NULL, '2025-12-14 20:10:45', '2025-12-14 20:10:45'),
(32, 19, 2, 1, 230.00, 230.00, NULL, '2025-12-14 20:11:00', '2025-12-14 20:11:00'),
(33, 19, 8, 1, 230.00, 230.00, NULL, '2025-12-14 20:11:00', '2025-12-14 20:11:00');

INSERT INTO "public"."orders" ("id", "order_number", "status", "total", "table_number", "customer_notes", "completed_at", "created_at", "updated_at", "customer_name") VALUES
(17, 'ORD-20251214-0001', 'delivered', 515.00, 'To Go', 'Poco picante', NULL, '2025-12-14 20:10:12', '2025-12-14 20:11:25', 'Maria'),
(18, 'ORD-20251214-0002', 'delivered', 660.00, 'Uber Eats', 'Empacar bien', NULL, '2025-12-14 20:10:45', '2025-12-14 20:11:44', 'Sara'),
(19, 'ORD-20251214-0003', 'delivered', 460.00, 'Rappi', NULL, NULL, '2025-12-14 20:11:00', '2025-12-14 20:11:56', 'Cliente no identificado');



-- Indices
CREATE UNIQUE INDEX users_email_unique ON public.users USING btree (email);


-- Indices
CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);
CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


-- Indices
CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


-- Indices
CREATE UNIQUE INDEX failed_jobs_uuid_unique ON public.failed_jobs USING btree (uuid);
ALTER TABLE "public"."dishes" ADD FOREIGN KEY ("category_id") REFERENCES "public"."categories"("id") ON DELETE CASCADE;


-- Indices
CREATE INDEX dishes_category_id_is_available_index ON public.dishes USING btree (category_id, is_available);
ALTER TABLE "public"."order_items" ADD FOREIGN KEY ("order_id") REFERENCES "public"."orders"("id") ON DELETE CASCADE;
ALTER TABLE "public"."order_items" ADD FOREIGN KEY ("dish_id") REFERENCES "public"."dishes"("id") ON DELETE RESTRICT;


-- Indices
CREATE INDEX order_items_order_id_dish_id_index ON public.order_items USING btree (order_id, dish_id);


-- Indices
CREATE INDEX orders_status_created_at_index ON public.orders USING btree (status, created_at);
CREATE UNIQUE INDEX orders_order_number_unique ON public.orders USING btree (order_number);
