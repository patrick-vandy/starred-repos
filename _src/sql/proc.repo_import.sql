DROP PROCEDURE IF EXISTS repo_import;

DELIMITER $$

CREATE DEFINER = 'github'@'localhost' PROCEDURE repo_import
(
	IN p_repo_import_id INTEGER(10) UNSIGNED,
	IN p_sync_id INTEGER(10) UNSIGNED,
	IN p_name CHARACTER VARYING(255),
	IN p_description TEXT, 
	IN p_url CHARACTER VARYING(255),
	IN p_repo_created_on TIMESTAMP,
	IN p_repo_updated_on TIMESTAMP,
	IN p_last_push_date TIMESTAMP,
	IN p_stars INTEGER(10) UNSIGNED
)
BEGIN
	--
	-- Saves a repo and stores its id in repo_import_object
	--

	CALL repo_upsert
	(
		p_sync_id,
		p_name,
		p_description,
		p_url,
		p_repo_created_on,
		p_repo_updated_on,
		p_last_push_date,
		p_stars,
		@repo_id
	);

	INSERT INTO repo_import_object (repo_import_id, repo_id)
	VALUES (p_repo_import_id, @repo_id);

END$$

DELIMITER ;