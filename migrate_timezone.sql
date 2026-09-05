USE password_manager;

-- Run this ONCE only if the existing rows were created while MySQL was using UTC.
-- New installations do not need this migration.
UPDATE sites
SET last_modified_time = DATE_ADD(DATE_ADD(last_modified_time, INTERVAL 3 HOUR), INTERVAL 30 MINUTE)
WHERE last_modified_time IS NOT NULL;

UPDATE password_history
SET changed_at = DATE_ADD(DATE_ADD(changed_at, INTERVAL 3 HOUR), INTERVAL 30 MINUTE)
WHERE changed_at IS NOT NULL;
