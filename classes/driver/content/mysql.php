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
		return count($columns) >= 10;
	}

	protected function create_db_structure() {
		return $this->pdo->query('
			CREATE TABLE IF NOT EXISTS `content_content` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`content` text COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
			CREATE TABLE IF NOT EXISTS `content_details` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `name` (`name`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_images` (
				`name` varchar(255) NOT NULL,
				PRIMARY KEY (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_images_details` (
				`image_name` varchar(255) NOT NULL,
				`detail_id` int(10) unsigned NOT NULL,
				`data` text NOT NULL,
				KEY `image_name` (`image_name`,`detail_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_pages` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				`URI` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `URI` (`URI`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_pages_types` (
				`page_id` int(10) unsigned NOT NULL,
				`type_id` int(10) unsigned NOT NULL,
				`template_field_id` int(10) unsigned NOT NULL COMMENT \'Where in the template this should reside\',
				PRIMARY KEY (`page_id`,`type_id`,`template_field_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			CREATE TABLE IF NOT EXISTS `content_type` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				`description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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

	public function get_contents()
	{

		$sql = '
			SELECT
				content_content.*,
				content_content_types.type_id,
				content_type.name
			FROM
				content_content
				LEFT JOIN
					content_content_types ON content_id = id
				JOIN
					content_type ON type_id = content_type.id;
		';

		$contents = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$contents[$row['id']]['id']      = $row['id'];
			$contents[$row['id']]['content'] = $row['content'];
			$contents[$row['id']]['types'][] = array(
			                                     'id'   => $row['type_id'],
			                                     'type' => $row['name'],
			                                   );
		}

		return $contents;
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

	public function get_detail_id($name)
	{
		return $this->pdo->query('SELECT id FROM content_details WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function get_detail_name($id)
	{
		return $this->pdo->query('SELECT name FROM content_details WHERE id = '.$this->pdo->quote($id))->fetchColumn();
	}

	public function get_details()
	{
		$details = array();
		foreach ($this->pdo->query('SELECT * FROM content_details;')->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$details[$row['id']] = $row['name'];
		}

		return $details;
	}

// DETAILS NOT WORKING!!!
	public function get_images($names = NULL, $details = array(), $names_only = FALSE)
	{
		if (is_array($names) && count($names) == 0) return array();
		if (is_string($names))                      $names = array($names);

		$sql = 'SELECT name FROM content_images WHERE 1';

		if (is_array($names))
		{
			$sql .= ' AND name IN (\''.implode('\',\'',$names).'\')';
		}

/* Fix this shit!!
		if (count($details))
		{
			$sql .= ' AND name IN (SELECT image_name FROM content_images_details WHERE ';
			foreach ($details as $detail_name => $detail_value)
			{
				$sql .= '(detail_id = (SELECT id FROM content_details WHERE name = '.$this->pdo->quote($detail_name).') AND
			}
			$sql .= ')';
		}
/**/

		$image_names = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
		if ($names_only) return $image_names;

		$images = array();
		foreach ($image_names as $image_name) $images[$image_name] = array();

		$sql = '
			SELECT
				image_name,
				detail_id,
				content_details.name AS detail_name,
				data
			FROM
				content_images_details
				INNER JOIN content_details ON content_details.id = content_images_details.detail_id
			WHERE image_name IN (\''.implode('\',\'',$image_names).'\');';

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			if ( ! isset($images[$row['image_name']][$row['detail_name']])) $images[$row['image_name']][$row['detail_name']] = array();
			$images[$row['image_name']][$row['detail_name']][] = $row['data'];
		}

		return $images;
	}

	public function get_page_data($id)
	{
		$sql = '
			SELECT
				content_pages.*,
				content_pages_types.type_id,
				content_pages_types.template_field_id
			FROM content_pages
				LEFT JOIN content_pages_types ON page_id = id
			WHERE id = '.$this->pdo->quote($id);

		$page_data = array();

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $data_line)
		{
			$page_data['id']                                        = $data_line['id'];
			$page_data['name']                                      = $data_line['name'];
			$page_data['URI']                                       = $data_line['URI'];
			$page_data['type_ids'][$data_line['template_field_id']] = $data_line['type_id'];
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

	public function image_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT name FROM content_images WHERE name = '.$this->pdo->quote($name))->fetchColumn();
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

	public function new_detail($name)
	{
		if ($this->pdo->exec('INSERT INTO content_details (name) VALUES('.$this->pdo->quote($name).');'))
		{
			return (int) $this->pdo->lastInsertId();
		}

		return FALSE;
	}

	public function new_image($name, $details = array())
	{
		if ($this->image_name_available($name))
		{
			$this->pdo->exec('INSERT INTO content_images (name) VALUES('.$this->pdo->quote($name).');');

			$sql = 'INSERT INTO content_images_details (image_name, detail_id, data) VALUES';
			foreach ($details as $detail_name => $detail_values)
			{
				if ( ! is_array($detail_values)) $detail_values = array($detail_values);

				if ( ! ($detail_id = $this->pdo->query('SELECT id FROM content_details WHERE name = '.$this->pdo->quote($detail_name))->fetchColumn()))
				{
					$this->pdo->exec('INSERT INTO content_details (name) VALUES('.$this->pdo->quote($detail_name).');');
					$detail_id = $this->pdo->lastInsertId();
				}

				foreach ($detail_values as $detail_value)
				{
					$sql .= '('.$this->pdo->quote($name).','.$this->pdo->quote($detail_id).','.$this->pdo->quote($detail_value).'),';
				}
			}

			// If we have any details, add them
			if (count($details)) $this->pdo->exec(substr($sql, 0, strlen($sql) - 1));

			return TRUE;
		}

		return FALSE;
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
					$sql = 'INSERT INTO content_pages_types (page_id, type_id, template_field_id) VALUES';

					foreach ($type_ids as $template_field_id => $type_id)
					{
						$sql .= '('.$this->pdo->quote($page_id).','.$this->pdo->quote($type_id).','.$this->pdo->quote($template_field_id).'),';
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

	public function rm_detail($detail_id)
	{
		$this->pdo->exec('DELETE FROM content_content_types_details WHERE detail_id = '.$this->pdo->quote($detail_id));
		$this->pdo->exec('DELETE FROM content_images_details        WHERE detail_id = '.$this->pdo->quote($detail_id));
		$this->pdo->exec('DELETE FROM content_types_details         WHERE detail_id = '.$this->pdo->quote($detail_id));
		$this->pdo->exec('DELETE FROM content_details               WHERE id        = '.$this->pdo->quote($detail_id));

		return TRUE;
	}

	public function rm_image($name)
	{
		$this->pdo->exec('DELETE FROM content_images_details WHERE image_name = '.$this->pdo->quote($name));
		$this->pdo->exec('DELETE FROM content_images         WHERE name       = '.$this->pdo->quote($name));

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

	public function update_image_data($image_name, $image_data = array())
	{
		// Clear previous data
		$this->pdo->exec('DELETE FROM content_images_details WHERE image_name = '.$this->pdo->quote($image_name).';');

		if (count($image_data))
		{
			$sql = 'INSERT INTO content_images_details (image_name, detail_id, data) VALUES';

			foreach ($image_data as $detail => $values)
			{
				if ($detail != 'name') // Name is forbidden, that is to be handled by update_image_name
				{
					$detail_id = $this->get_detail_id($detail);
					if ( ! $detail_id) $detail_id = $this->new_detail($detail);

					if ( ! is_array($values)) $values = array($values);
					foreach ($values as $value)
					{
						$sql .= '('.$this->pdo->quote($image_name).','.$detail_id.','.$this->pdo->quote($value).'),';
					}
				}
			}
			$sql = substr($sql, 0, strlen($sql) - 1);
			$this->pdo->exec($sql);
		}

		return TRUE;
	}

	public function update_image_name($old_image_name, $new_image_name)
	{
		if ($old_image_name != $new_image_name && Content_Image::image_name_available($new_image_name))
		{
			$this->pdo->exec('UPDATE content_images_details SET image_name = '.$this->pdo->quote($new_image_name).' WHERE image_name = '.$this->pdo->quote($old_image_name));
			$this->pdo->exec('UPDATE content_images SET name = '.$this->pdo->quote($new_image_name).' WHERE name = '.$this->pdo->quote($old_image_name));

			return $this->rename_image_files($old_image_name, $new_image_name);
		}
		return FALSE;
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
				$sql = 'INSERT INTO content_pages_types (page_id, type_id, template_field_id) VALUES';

				foreach ($type_ids as $template_field_id => $type_id)
				{
					$sql .= '('.$this->pdo->quote($id).','.$this->pdo->quote($type_id).','.$this->pdo->quote($template_field_id).'),';
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
