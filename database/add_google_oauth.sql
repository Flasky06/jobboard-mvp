-- Add google_id column to users table if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) NULL UNIQUE AFTER email,
ADD INDEX idx_google_id (google_id);

-- Make password nullable for OAuth users (they might not have a password)
ALTER TABLE users
MODIFY COLUMN password VARCHAR(255) NULL;