# Dokumen Skema Database SAMARA

Skema database SAMARA menggunakan RDBMS MySQL 8 / MariaDB dengan kaskade Foreign Key, Soft Delete, dan Database Indexing.

---

## Tabel Utama

### 1. `users`
- `id` (BIGINT, PK, Auto Increment)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, Nullable)
- `full_name` (VARCHAR)
- `role` (VARCHAR, Enum: ADMIN, TIM, DIRECTOR)
- `manager_email` (VARCHAR, Nullable)
- `avatar_url` (VARCHAR, Nullable)
- `is_active` (BOOLEAN, Default true)
- `last_login_at` (TIMESTAMP, Nullable)
- `timestamps`

### 2. `areas`
- `id` (BIGINT, PK)
- `area_code` (VARCHAR, Unique)
- `area_name` (VARCHAR)
- `region` (VARCHAR)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default true)
- `timestamps`, `deleted_at`

### 3. `user_areas`
- `id` (BIGINT, PK)
- `user_id` (FK -> users.id)
- `area_id` (FK -> areas.id)
- UNIQUE constraint: `(user_id, area_id)`

### 4. `segments`
- `id` (BIGINT, PK)
- `segment_code` (VARCHAR, Unique)
- `segment_name` (VARCHAR)
- `description` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default true)
- `timestamps`, `deleted_at`

### 5. `customers`
- `id` (BIGINT, PK)
- `customer_code` (VARCHAR, Unique)
- `customer_name` (VARCHAR)
- `area_id` (FK -> areas.id)
- `segment_id` (FK -> segments.id)
- `owner_id` (FK -> users.id)
- `address`, `city`, `province` (VARCHAR)
- `latitude`, `longitude` (DECIMAL 10,7, Nullable)
- `contact_name`, `contact_position`, `contact_phone`, `contact_email` (Nullable)
- `priority_tier` (VARCHAR: A, B, C)
- `notes` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default true)
- `timestamps`, `deleted_at`

### 6. `visit_plans`
- `id` (BIGINT, PK)
- `plan_number` (VARCHAR, Unique)
- `customer_id` (FK -> customers.id)
- `area_id` (FK -> areas.id)
- `owner_id` (FK -> users.id)
- `plan_month` (VARCHAR: YYYY-MM)
- `planned_date` (DATE)
- `start_time`, `end_time` (TIME)
- `activity_type` (VARCHAR: SALES_VISIT, ACCOUNT_MAINTENANCE, PRODUCT_DEMO, TECHNICAL_SURVEY, MARKETING_ENGAGEMENT, EVENT, OTHER)
- `priority` (VARCHAR: LOW, MEDIUM, HIGH, CRITICAL)
- `monthly_objective`, `specific_objective` (TEXT)
- `resource_notes`, `location_text` (Nullable)
- `status` (VARCHAR: DRAFT, PLANNED, APPROVED, IN_PROGRESS, COMPLETED, CANCELLED, RESCHEDULED)
- `rescheduled_from_id` (FK -> visit_plans.id, Nullable)
- `cancellation_reason` (TEXT, Nullable)
- `actual_start_at`, `actual_end_at`, `completed_at` (DATETIME, Nullable)
- `timestamps`, `deleted_at`

### 7. `visit_reports`
- `id` (BIGINT, PK)
- `report_number` (VARCHAR, Unique)
- `visit_plan_id` (FK -> visit_plans.id, Unique)
- `actual_start_at`, `actual_end_at` (DATETIME)
- `outcome_summary` (TEXT)
- `outcome_type` (VARCHAR: NO_CHANGE, NEED_IDENTIFIED, FOLLOW_UP, PROPOSAL, NEGOTIATION, WON, LOST)
- `engagement_score` (TINYINT 1-5)
- `attendance_summary` (TEXT)
- `barrier`, `need_or_opportunity`, `competitor_information` (TEXT, Nullable)
- `next_step_summary` (TEXT)
- `follow_up_required` (BOOLEAN, Default false)
- `submitted_by` (FK -> users.id)
- `submitted_at` (DATETIME)
- `submit_status` (VARCHAR: DRAFT, SUBMITTED, CORRECTED)
- `timestamps`

### 8. `follow_ups`
- `id` (BIGINT, PK)
- `action_number` (VARCHAR, Unique)
- `visit_plan_id` (FK -> visit_plans.id)
- `visit_report_id` (FK -> visit_reports.id, Nullable)
- `customer_id` (FK -> customers.id)
- `action_title` (VARCHAR)
- `action_detail` (TEXT)
- `owner_id` (FK -> users.id)
- `due_date` (DATE)
- `priority` (VARCHAR: LOW, MEDIUM, HIGH, CRITICAL)
- `status` (VARCHAR: OPEN, IN_PROGRESS, BLOCKED, DONE, CANCELLED)
- `completion_note`, `blocked_reason` (TEXT, Nullable)
- `completed_at` (DATETIME, Nullable)
- `rescheduled_to` (DATE, Nullable)
- `timestamps`, `deleted_at`

### 9. `director_inputs`
- `id` (BIGINT, PK)
- `visit_report_id`, `visit_plan_id`, `customer_id`, `area_id` (Nullable FKs)
- `topic` (VARCHAR)
- `input_type` (VARCHAR: STRATEGIC_DIRECTION, SUGGESTION, SOLUTION, DECISION)
- `direction_text` (TEXT)
- `assigned_to` (FK -> users.id)
- `priority` (VARCHAR)
- `due_date` (DATE, Nullable)
- `status` (VARCHAR: OPEN, ACKNOWLEDGED, IN_PROGRESS, CLOSED, CANCELLED)
- `acknowledged_at`, `started_at`, `closed_at` (DATETIME, Nullable)
- `completion_note` (TEXT, Nullable)
- `timestamps`, `deleted_at`

### 10. `audit_logs`
- `id` (BIGINT, PK)
- `actor_id` (FK -> users.id, Nullable)
- `action` (VARCHAR)
- `entity_type` (VARCHAR)
- `entity_id` (BIGINT, Nullable)
- `before_data`, `after_data`, `metadata` (JSON, Nullable)
- `ip_address`, `user_agent` (Nullable)
- `created_at` (TIMESTAMP)
