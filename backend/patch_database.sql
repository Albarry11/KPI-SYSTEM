USE kpi_system;

DROP PROCEDURE IF EXISTS PatchDatabase;

DELIMITER $$
CREATE PROCEDURE PatchDatabase()
BEGIN
    DECLARE CONTINUE HANDLER FOR 1060 BEGIN END;

    ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER name;
    ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
    ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL;

    ALTER TABLE kpi_categories ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
    ALTER TABLE kpi_weights ADD COLUMN notes TEXT NULL;
    ALTER TABLE tasks ADD COLUMN deadline DATE NULL;
    ALTER TABLE tasks ADD COLUMN priority VARCHAR(50) DEFAULT 'medium';
    ALTER TABLE evaluations ADD COLUMN submitted_at TIMESTAMP NULL;
    ALTER TABLE evaluations ADD COLUMN approved_at TIMESTAMP NULL;
END $$
DELIMITER ;

CALL PatchDatabase();
DROP PROCEDURE IF EXISTS PatchDatabase;

CREATE TABLE IF NOT EXISTS task_progresses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  task_id INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED NOT NULL,
  progress_value DECIMAL(10,2) DEFAULT 0,
  notes TEXT NULL,
  evidence_url VARCHAR(255) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

UPDATE users SET username = 'manager' WHERE email LIKE 'manager%';
UPDATE users SET username = 'andi' WHERE email LIKE 'andi%';
UPDATE users SET username = 'sari' WHERE email LIKE 'sari%';
UPDATE users SET username = 'budi' WHERE email LIKE 'budi%';
UPDATE users SET username = 'dewi' WHERE email LIKE 'dewi%';
UPDATE users SET is_active = TRUE;
