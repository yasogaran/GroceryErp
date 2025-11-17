-- Add manufacturing_date column to stock_movements table
-- Use this if unit_cost, min_selling_price, max_selling_price already exist

-- Check if the column already exists before adding
SET @dbname = DATABASE();
SET @tablename = 'stock_movements';
SET @columnname = 'manufacturing_date';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists' AS status;",
  "ALTER TABLE stock_movements ADD COLUMN manufacturing_date DATE NULL COMMENT 'Manufacturing date for this batch' AFTER batch_number;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
