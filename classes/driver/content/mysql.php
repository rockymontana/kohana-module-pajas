<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Content_Mysql extends Driver_Content
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function check_db_structure()
	{
		$columns = $this->pdo->query('SHOW TABLES like \'content_%\';')->fetchAll(PDO::FETCH_COLUMN);
		return count($columns) == 7;
	}

	protected function create_db_structure() {
		return $this->pdo->query('
			CREATE TABLE IF NOT EXISTS `content_content` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`content` text COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_content_types` (
				`content_id` bigint(20) unsigned NOT NULL,
				`type_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`content_id`,`type_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_content_types_details` (
				`content_id` bigint(20) NOT NULL,
				`type_id` int(11) NOT NULL,
				`detail_id` int(11) NOT NULL,
				`content` text COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`content_id`,`type_id`,`detail_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_pages` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(100) NOT NULL,
				`URI` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `URI` (`URI`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_pages_types` (
				`page_id` int(10) unsigned NOT NULL,
				`type_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`page_id`,`type_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_type` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				`description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_types_details` (
				`type_id` int(10) unsigned NOT NULL,
				`detail_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`type_id`,`detail_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function get_content($content_id)
	{
		return $this->pdo->query('SELECT content FROM content_content WHERE id = '.$this->pdo->quote($content_id))->fetchColumn();
	}

	public function get_contents_by_type_id($type_id)
	{
		$sql = '
			SELECT id, content
			FROM content_content
			WHERE id IN
				(
					SELECT content_id
					FROM content_content_types
					WHERE type_id = '.$this->pdo->quote($type_id).'
				);';

		return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_page_data($id)
	{
		$sql = '
			SELECT content_pages.* , content_pages_types.type_id
			FROM content_pages
				LEFT JOIN content_pages_types ON page_id = id
			WHERE id = '.$this->pdo->quote($id);

		$page_data = array();

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $data_line)
		{
			$page_data['id']         = $data_line['id'];
			$page_data['name']       = $data_line['name'];
			$page_data['URI']        = $data_line['URI'];
			$page_data['type_ids'][] = $data_line['type_id'];
		}

		return $page_data;
	}

	public function get_page_id_by_URI($URI)
	{
		return $this->pdo->query('SELECT id FROM content_pages WHERE URI = '.$this->pdo->quote($URI))->fetchColumn();
	}

	public function get_pages()
	{
		$sql = '
			SELECT content_pages.* , content_pages_types.type_id
			FROM content_pages
				LEFT JOIN content_pages_types ON page_id = id
			ORDER BY name';

		$page_data = array();

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $data_line)
		{
			$page_data[$data_line['id']]['id']         = $data_line['id'];
			$page_data[$data_line['id']]['name']       = $data_line['name'];
			$page_data[$data_line['id']]['URI']        = $data_line['URI'];
			$page_data[$data_line['id']]['type_ids'][] = $data_line['type_id'];
		}

		return $page_data;
	}

	public function get_type_data($id)
	{
		return $this->pdo->query('SELECT * FROM content_type WHERE id = '.$this->pdo->quote($id))->fetch(PDO::FETCH_ASSOC);
	}

	public function get_type_id_by_name($name)
	{
		return $this->pdo->query('SELECT id FROM content_type WHERE name = '.$this->pdo->quote($name))->fetchColumn(PDO::FETCH_ASSOC);
	}

	public function get_type_ids_by_content_id($content_id)
	{
		return $this->pdo->query('SELECT type_id FROM content_content_types WHERE content_id = '.$this->pdo->quote($content_id))->fetchAll(PDO::FETCH_COLUMN);
	}

	public function get_types()
	{
		return $this->pdo->query('SELECT * FROM content_type ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function new_content($content, $type_ids = FALSE)
	{
		$this->pdo->exec('INSERT INTO content_content (content) VALUES('.$this->pdo->quote($content).');');
		$content_id = $this->pdo->lastInsertId();

		if ($type_ids)
		{
			$sql = 'INSERT INTO content_content_types (content_id, type_id) VALUES';
			foreach ($type_ids as $type_id)
			{
				$sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote($type_id).'),';
			}
			$this->pdo->exec(substr($sql, 0, strlen($sql) - 1).';');
		}

		return (int) $content_id;
	}

	public function new_page($name, $URI, $type_ids = FALSE)
	{
		if ($this->page_name_available($name) && Content_Page::page_URI_available($URI))
		{
			$this->pdo->exec('
				INSERT INTO content_pages
				(name, URI)
				VALUES('.$this->pdo->quote($name).','.$this->pdo->quote($URI).');');

			$page_id = $this->pdo->lastInsertId();

			if ($page_id)
			{
				// Add the type connections
				if ($type_ids)
				{
					$sql = 'INSERT INTO content_pages_types (page_id, type_id) VALUES';

					foreach ($type_ids as $type_id)
					{
						$sql .= '('.$this->pdo->quote($page_id).','.$this->pdo->quote($type_id).'),';
					}

					$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
				}

				return $page_id;
			}
		}

		return FALSE;
	}

	public function new_type($name, $description = '')
	{
		if ($this->type_name_available($name))
		{
			$this->pdo->exec('
				INSERT INTO content_type
				(name, description)
				VALUES('.$this->pdo->quote($name).','.$this->pdo->quote($description).');');

			if ($type_id = $this->pdo->lastInsertId()) return $type_id;
		}

		return FALSE;
	}

	public function page_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT id FROM content_pages WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function rm_content($content_id)
	{
		$this->pdo->exec('DELETE FROM content_content               WHERE id         = '.$this->pdo->quote($content_id));
		$this->pdo->exec('DELETE FROM content_content_types         WHERE content_id = '.$this->pdo->quote($content_id));
		$this->pdo->exec('DELETE FROM content_content_types_details WHERE content_id = '.$this->pdo->quote($content_id));

		return TRUE;
	}

	public function rm_page($id)
	{
		$this->pdo->exec('DELETE FROM content_pages WHERE id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_pages_types WHERE page_id = '.$this->pdo->quote($id));

		return TRUE;
	}

	public function rm_type($id)
	{
		$this->pdo->exec('DELETE FROM content_type                  WHERE id      = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_types_details         WHERE type_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_pages_types           WHERE type_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_content_types         WHERE type_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_content_types_details WHERE type_id = '.$this->pdo->quote($id));

		return TRUE;
	}

	public function type_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT id FROM content_type WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function update_content($content_id, $content = FALSE, $type_ids = FALSE)
	{
		if ($content !== FALSE)
		{
			$this->pdo->exec('
				UPDATE content_content
				SET content = '.$this->pdo->quote($content).'
				WHERE id = '.$this->pdo->quote($content_id)
			);
		}

		if ($type_ids)
		{
			$this->pdo->exec('
				DELETE FROM content_content_types
				WHERE content_id = '.$this->pdo->quote($content_id).';');

			$sql = 'INSERT INTO content_content_types (content_id, type_id) VALUES';
			foreach ($type_ids as $type_id)
			{
				$sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote($type_id).'),';
			}
			$this->pdo->exec(substr($sql, 0, strlen($sql) - 1).';');
		}

		return (int) $content_id;
	}

	public function update_page_data($id, $name = FALSE, $URI = FALSE, $type_ids = FALSE)
	{
		// Nothing to update
		if ($name === FALSE && $URI === FALSE && $type_ids === FALSE) return TRUE;

		if ( ! ($current_page_data = $this->get_page_data($id)))
		{
			return FALSE;
		}

		// Check if there is something to update in the content_pages table
		if (($name && $current_page_data['name'] != $name) || ($URI && $current_page_data['URI'] != $URI))
		{
			$sql = 'UPDATE content_pages SET ';

			if ($name && $current_page_data['name'] != $name)
			{
				if ($this->page_name_available($name))
				{
					$sql .= 'name = '.$this->pdo->quote($name).', ';
				}
				else return FALSE;
			}

			if ($URI && $current_page_data['URI'] != $URI)
			{
				if (Content_Page::page_URI_available($URI))
				{
					$sql .= 'URI = '.$this->pdo->quote($URI).', ';
				}
				else return FALSE;
			}

			// Finalize and run the query to the content_pages table
			$sql  = substr($sql, 0, strlen($sql) - 2).' WHERE id = '.$this->pdo->quote($id);
			$this->pdo->exec($sql);
		}

		// Check if there is something to update in the types connection table
		if ($type_ids)
		{
			// First remove all the old ones
			$this->pdo->exec('DELETE FROM content_pages_types WHERE page_id = '.$this->pdo->quote($id));

			if (count($type_ids))
			{
				// Then add the new ones
				$sql = 'INSERT INTO content_pages_types (page_id, type_id) VALUES';

				foreach ($type_ids as $type_id)
				{
					$sql .= '('.$this->pdo->quote($id).','.$this->pdo->quote($type_id).'),';
				}

				$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
			}
		}

		return TRUE;
	}

	public function update_type_data($id, $name = FALSE, $description = FALSE)
	{
		// Nothing to update
		if ($name == FALSE && $description == FALSE) return TRUE;

		$sql = 'UPDATE content_type SET ';

		if ( ! ($current_type_data = $this->get_type_data($id)))
		{
			return FALSE;
		}

		if ($name && $current_type_data['name'] != $name)
		{
			if ($this->type_name_available($name))
			{
				$sql .= 'name = '.$this->pdo->quote($name).', ';
			}
			else return FALSE;
		}

		if ($description && $current_type_data['description'] != $description)
		{
			$sql .= 'description = '.$this->pdo->quote($description).', ';
		}

		$sql = substr($sql, 0, strlen($sql) - 2);

		$sql .= ' WHERE id = '.$this->pdo->quote($id);

		return $this->pdo->exec($sql);
	}

}
