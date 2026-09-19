CREATE DATABASE IF NOT EXISTS taskforce_yii2_db
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_0900_ai_ci;

USE taskforce_yii2_db;

-- City dictionary; created before `user` because of the foreign key
CREATE TABLE IF NOT EXISTS `city` (
    `id`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`  VARCHAR(255)    NOT NULL,
    `lat`   DECIMAL(10,7)   NOT NULL,
    `lng`   DECIMAL(10,7)   NOT NULL,

    UNIQUE INDEX `uq_city_name` (`name`)

) ENGINE=InnoDB;

-- User
CREATE TABLE IF NOT EXISTS `user` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email`         VARCHAR(255)    NOT NULL,
    `name`          VARCHAR(128)    NOT NULL,
    `password`      VARCHAR(255)    NULL,
    `city_id`       BIGINT UNSIGNED NULL,

    `avatar`        VARCHAR(255)    NULL, -- relative path to avatar file
    `birthday`      DATE            NULL,

    -- all users can create tasks, but only executors can submit bids
    `is_executor`   BOOLEAN         NOT NULL DEFAULT FALSE,

    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX `uq_user_email` (`email`),

    CONSTRAINT `fk_user_city`
        FOREIGN KEY (`city_id`)
        REFERENCES `city` (`id`)

) ENGINE=InnoDB;

-- Executor profile; exists only for users with `is_executor = TRUE`
CREATE TABLE IF NOT EXISTS `executor_profile` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`           BIGINT UNSIGNED NOT NULL,

    `phone`             VARCHAR(20)     NULL,
    `telegram`          VARCHAR(64)     NULL, -- username without @
    `about`             TEXT            NULL,

    -- hide contacts from everyone except the task customer
    `hide_my_contacts`  BOOLEAN         NOT NULL DEFAULT FALSE,

    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX `uq_executor_profile_user` (`user_id`),

    CONSTRAINT `fk_executor_profile_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)

) ENGINE=InnoDB;

-- Category
CREATE TABLE IF NOT EXISTS `category` (
    `id`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`  VARCHAR(64)     NOT NULL,
    `slug`  VARCHAR(64)     NOT NULL,

    UNIQUE INDEX `uq_category_name` (`name`),
    UNIQUE INDEX `uq_category_slug` (`slug`)

) ENGINE=InnoDB;

-- Executor specialization (user-to-category)
CREATE TABLE IF NOT EXISTS `executor_specialization` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`       BIGINT UNSIGNED NOT NULL,
    `category_id`   BIGINT UNSIGNED NOT NULL,

    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE INDEX `uq_executor_specialization_user_category` (`user_id`, `category_id`),

    CONSTRAINT `fk_executor_specialization_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `fk_executor_specialization_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `category` (`id`)

) ENGINE=InnoDB;

-- Task
CREATE TABLE IF NOT EXISTS `task` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customer_id`   BIGINT UNSIGNED NOT NULL,
    `category_id`   BIGINT UNSIGNED NOT NULL,
    `executor_id`   BIGINT UNSIGNED NULL,

    `title`         VARCHAR(255)    NOT NULL,
    `description`   TEXT            NOT NULL,
    `budget`        BIGINT UNSIGNED NOT NULL,
    `expire_date`   DATE            NOT NULL,

    `status`        VARCHAR(32)     NOT NULL DEFAULT 'new',

    -- NULL means remote work without geographic binding
    `location`      VARCHAR(255)    NULL, -- address entered by the user
    `city_id`       BIGINT UNSIGNED NULL,
    `lat`           DECIMAL(10,7)   NULL,
    `lng`           DECIMAL(10,7)   NULL,

    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `chk_task_status`
        CHECK (`status` IN ('new', 'canceled', 'in_progress', 'completed', 'failed')),

    INDEX `idx_task_expire_date` (`expire_date`),
    INDEX `idx_task_created_at`  (`created_at`),

    CONSTRAINT `fk_task_customer`
        FOREIGN KEY (`customer_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `fk_task_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `category` (`id`),

    CONSTRAINT `fk_task_executor`
        FOREIGN KEY (`executor_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `fk_task_city`
        FOREIGN KEY (`city_id`)
        REFERENCES `city` (`id`)

) ENGINE=InnoDB;

-- Task attachment
CREATE TABLE IF NOT EXISTS `attachment` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `task_id`       BIGINT UNSIGNED     NOT NULL,

    `file_path`     VARCHAR(255)        NOT NULL, -- relative path to stored file
    `original_name` VARCHAR(255)        NOT NULL, -- original filename
    `mime_type`     VARCHAR(255)        NULL,
    `size_bytes`    BIGINT UNSIGNED     NULL,

    `created_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_attachment_task`
        FOREIGN KEY (`task_id`)
        REFERENCES `task` (`id`)

) ENGINE=InnoDB;

-- Bid
CREATE TABLE IF NOT EXISTS `bid` (
    `id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`   BIGINT UNSIGNED NOT NULL,
    `task_id`   BIGINT UNSIGNED NOT NULL,

    `price`     BIGINT UNSIGNED NOT NULL,

    `status`    VARCHAR(16) NOT NULL DEFAULT 'new',

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `chk_bid_status`
        CHECK (`status` IN ('new', 'accepted', 'rejected')),

    UNIQUE INDEX `uq_bid_user_task` (`user_id`, `task_id`),

    CONSTRAINT `fk_bid_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `fk_bid_task`
        FOREIGN KEY (`task_id`)
        REFERENCES `task` (`id`)

) ENGINE=InnoDB;

-- Review (customer review)
CREATE TABLE IF NOT EXISTS `review` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `task_id`     BIGINT UNSIGNED NOT NULL,
    `executor_id` BIGINT UNSIGNED NOT NULL,

    `score`       TINYINT UNSIGNED NOT NULL,
    `comment`     TEXT NULL,

    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `chk_review_score`
        CHECK (`score` BETWEEN 1 AND 5),

    UNIQUE INDEX `uq_review_task` (`task_id`),

    INDEX `idx_review_customer_created_at` (`customer_id`, `created_at`),
    INDEX `idx_review_executor_created_at` (`executor_id`, `created_at`),

    CONSTRAINT `fk_review_customer`
        FOREIGN KEY (`customer_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `fk_review_task`
        FOREIGN KEY (`task_id`)
        REFERENCES `task` (`id`),

    CONSTRAINT `fk_review_executor`
        FOREIGN KEY (`executor_id`)
        REFERENCES `user` (`id`)

) ENGINE=InnoDB;

-- Executor statistics (view)
CREATE OR REPLACE VIEW `executor_stats_view` AS
SELECT
    u.id AS executor_id,
    u.name,
    COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) AS completed_tasks,
    COUNT(DISTINCT CASE WHEN t.status = 'failed' THEN t.id END) AS failed_tasks,
    ROUND(AVG(r.score), 2) AS avg_score,
    -- use window function to calculate rating position
    ROW_NUMBER() OVER (ORDER BY ROUND(AVG(r.score), 2) DESC, u.id) AS rating_position
FROM `user` u
LEFT JOIN `task` AS t
    ON t.executor_id = u.id
LEFT JOIN `review` AS r
    ON r.executor_id = u.id
WHERE u.is_executor = TRUE
GROUP BY u.id;
