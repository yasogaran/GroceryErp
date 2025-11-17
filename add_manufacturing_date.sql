-- Add manufacturing_date column to stock_movements table
-- Run this SQL if you cannot run php artisan migrate

ALTER TABLE `stock_movements`
ADD COLUMN `unit_cost` DECIMAL(10, 2) NULL COMMENT 'Purchase price (from supplier)' AFTER `quantity`,
ADD COLUMN `min_selling_price` DECIMAL(10, 2) NULL COMMENT 'Minimum selling price for this batch' AFTER `unit_cost`,
ADD COLUMN `max_selling_price` DECIMAL(10, 2) NULL COMMENT 'Maximum selling price (MRP) for this batch' AFTER `min_selling_price`,
ADD COLUMN `manufacturing_date` DATE NULL COMMENT 'Manufacturing date for this batch' AFTER `batch_number`;
