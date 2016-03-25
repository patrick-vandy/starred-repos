DROP PROCEDURE IF EXISTS repo_upsert;

DELIMITER $$

CREATE DEFINER = 'github'@'localhost' PROCEDURE repo_upsert
(
	IN p_sync_id INTEGER(10) UNSIGNED,
	IN p_name CHARACTER VARYING(255),
	IN p_description TEXT, 
	IN p_url CHARACTER VARYING(255),
	IN p_repo_created_on TIMESTAMP,
	IN p_repo_updated_on TIMESTAMP,
	IN p_last_push_date TIMESTAMP,
	IN p_stars INTEGER(10) UNSIGNED,
	OUT p_repo_id INTEGER(10) UNSIGNED
)
BEGIN
	--
	-- Upsert logic for adding / updating repos
	--

	-- Check if sync_id already exists in repo, if so store the repo_id
	SELECT repo_id INTO p_repo_id FROM repo WHERE sync_id = p_sync_id;


	-- If the sync_id did exist do an update
	IF p_repo_id IS NOT NULL
	THEN

		UPDATE repo
		SET
			name = p_name,
			description = p_description,
			url = p_url,
			repo_created_on = p_repo_created_on,
			repo_updated_on = p_repo_updated_on,
			last_push_date = p_last_push_date,
			stars = p_stars
		WHERE sync_id = p_sync_id;
		
	-- Otherwise do an insert
	ELSE

		INSERT INTO repo (
			sync_id,
			name,
			description,
			url,
			repo_created_on,
			repo_updated_on,
			last_push_date,
			stars
		)
		VALUES (
			p_sync_id,
			p_name,
			p_description,
			p_url,
			p_repo_created_on,
			p_repo_updated_on,
			p_last_push_date,
			p_stars
		);
		
		-- Get repo_id from insert above
		SELECT last_insert_id() INTO p_repo_id;
		
	END IF;

END$$

DELIMITER ;