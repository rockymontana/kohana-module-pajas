<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Content_Mysql extends Driver_Content
{

	protected function check_db_structure()
	{
		$columns = $this->pdo->query('SHOW TABLES like \'content%\';')->fetchAll(PDO::FETCH_COLUMN);
		return count($columns) >= 6;
	}

	protected function create_db_structure() {
		return $this->pdo->query('
		CREATE TABLE IF NOT EXISTS `content` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`content` text COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		CREATE TABLE IF NOT EXISTS `content_images` (
			`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		CREATE TABLE IF NOT EXISTS `content_images_tags` (
			`image_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`tag_id` int(11) NOT NULL,
			`tag_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			KEY `image_name` (`image_name`,`tag_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		CREATE TABLE IF NOT EXISTS `content_pages` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`URI` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `URI` (`URI`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		CREATE TABLE IF NOT EXISTS `content_pages_tags` (
			`page_id` int(10) unsigned NOT NULL,
			`tag_id` int(10) unsigned NOT NULL,
			`template_field_id` int(10) unsigned NOT NULL,
			KEY `page_id` (`page_id`,`tag_id`,`template_field_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		CREATE TABLE IF NOT EXISTS `content_tags` (
			`content_id` int(10) unsigned NOT NULL,
			`tag_id` int(10) unsigned NOT NULL,
			`tag_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			KEY `content_id` (`content_id`,`tag_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function get_content($content_id)
	{
		return $this->pdo->query('SELECT content FROM content WHERE id = '.$this->pdo->quote($content_id))->fetchColumn();
	}

	public function get_contents()
	{
		$sql = '
			SELECT
				content.*,
				content_tags.tag_id,
				content_tags.tag_value,
				tags.name as tag_name
			FROM
				content
				LEFT JOIN
					content_tags ON content_tags.content_id = content.id
				LEFT JOIN
					tags         ON tags.id = content_tags.tag_id;
		';

		$contents = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$contents[$row['id']]['id']      = $row['id'];
			$contents[$row['id']]['content'] = $row['content'];
			$contents[$row['id']]['tags'][]  = array(
			                                     'id'    => $row['tag_id'],
			                                     'name'  => $row['tag_name'],
			                                     'value' => $row['tag_value'],
			                                   );
		}

		return $contents;
	}

	public function get_contents_by_tag_id($tag_id)
	{
		$sql = '
			SELECT
				content.id,
				content.content,
				(
					SELECT name FROM tags WHERE tags.id = content_tags.tag_id
				) AS tag,
				content_tags.tag_value
			FROM
				content
				LEFT JOIN content_tags ON content_tags.content_id = content.id
			WHERE content.id IN
				(
					SELECT content_id
					FROM content_tags
					WHERE tag_id = '.$this->pdo->quote($tag_id).'
				);';

		$contents = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			if ( ! isset($contents[$row['id']]))
			{
				$contents[$row['id']] = array(
					'id'      => $row['id'],
					'content' => $row['content'],
					'tags'    => array(),
				);
			}

			if ( ! isset($contents[$row['id']]['tags'][$row['tag']]))
			{
				$contents[$row['id']]['tags'][$row['tag']] = array();
			}
			$contents[$row['id']]['tags'][$row['tag']][] = $row['tag_value'];
		}

		return $contents;
	}

	public function get_images($names = NULL, $tags = array(), $names_only = FALSE)
	{
		if (is_array($names) && count($names) == 0) return array();
		if (is_string($names))                      $names = array($names);

		$sql = '
			SELECT
				name,
				tag_id,
				(SELECT tags.name FROM tags WHERE tags.id = content_images_tags.tag_id) AS tag_name,
				tag_value
			FROM
				content_images
				LEFT JOIN
					content_images_tags ON image_name = name
			WHERE 1 = 1';

		if (@count($names))
		{
			$sql .= ' AND name IN (';
			foreach ($names as $name) $sql .= $this->pdo->quote($name).',';
			$sql = substr($sql, 0, strlen($sql) - 1).')';
		}

		if (@count($tags))
		{
			$sql .= ' AND (
				name IN (
					SELECT image_name FROM content_images_tags WHERE 1 = 1 AND (';
			foreach ($tags as $tag => $values)
			{
				if ($values === TRUE) $sql .= 'tag_id = '.$this->pdo->quote(Tags::get_id_by_name($tag)).' OR ';
				elseif (@count($values))
				{
					$sql .= '(tag_id = '.$this->pdo->quote(Tags::get_id_by_name($tag)).' AND (';

					foreach ($values as $value) $sql .= 'tag_value = '.$this->pdo->quote($value).' OR ';

					$sql = substr($sql, 0, strlen($sql) - 4).')) OR ';
				}
			}
			$sql = substr($sql, 0, strlen($sql) - 4).')))';
		}

		$images = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			if ( ! isset($images[$row['name']][$row['tag_name']])) $images[$row['name']][$row['tag_name']] = array();
			if ($row['tag_value']) $images[$row['name']][$row['tag_name']][] = $row['tag_value'];
		}

		if ($names_only) return array_keys($images);
		else             return $images;
	}

	public function get_page_data($id)
	{
		$sql = '
			SELECT
				content_pages.*,
				content_pages_tags.*
			FROM content_pages
				LEFT JOIN content_pages_tags ON page_id = id
			WHERE id = '.$this->pdo->quote($id);

		$page_data = array();

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $data_line)
		{
			if ( ! isset($page_data['tag_ids'][$data_line['template_field_id']]))
			{
				$page_data['tag_ids'][$data_line['template_field_id']] = array();
			}

			$page_data['id']                                          = $data_line['id'];
			$page_data['name']                                        = $data_line['name'];
			$page_data['URI']                                         = $data_line['URI'];
			$page_data['tag_ids'][$data_line['template_field_id']][]  = $data_line['tag_id'];
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
			SELECT
				content_pages.*,
				content_pages_tags.*
			FROM content_pages
				LEFT JOIN content_pages_tags ON page_id = id
			ORDER BY name;';

		$page_data = array();

		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $data_line)
		{
			if ( ! isset($page_data[$data_line['id']]))
			{
				$page_data[$data_line['id']] = array(
					'id'      => $data_line['id'],
					'name'    => $data_line['name'],
					'URI'     => $data_line['URI'],
					'tag_ids' => array(),
				);
			}

			if ( ! isset($page_data[$data_line['id']]['tag_ids'][$data_line['template_field_id']]))
			{
				$page_data[$data_line['id']]['tag_ids'][$data_line['template_field_id']] = array();
			}

			$page_data[$data_line['id']]['tag_ids'][$data_line['template_field_id']][] = $data_line['tag_id'];
		}

		return $page_data;
	}

	public function get_tags_by_content_id($content_id = FALSE)
	{
		$sql = '
			SELECT
				tag_id AS id,
				tags.name,
				tag_value
			FROM
				content_tags
				JOIN tags ON tags.id = content_tags.tag_id';

		if ($content_id)
		{
			$sql .= ' WHERE content_id = '.$this->pdo->quote($content_id);
		}

		$tags = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			if ( ! isset($tags[$row['id']])) $tags[$row['id']] = array('id'=>$row['id'],'name'=>$row['name'],'values'=>array());
			$tags[$row['id']]['values'][] = $row['tag_value'];
		}

		return $tags;
	}

	public function get_tags_by_image_name($image_name = FALSE)
	{
		$sql = '
			SELECT
				tag_id AS id,
				tags.name,
				tag_value
			FROM
				content_images_tags
				JOIN tags ON tags.id = content_images_tags.tag_id';

		if ($image_name)
		{
			$sql .= ' WHERE image_name = '.$this->pdo->quote($image_name);
		}

		$tags = array();
		foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			if ( ! isset($tags[$row['id']])) $tags[$row['id']] = array('id'=>$row['id'],'name'=>$row['name'],'values'=>array());
			$tags[$row['id']]['values'][] = $row['tag_value'];
		}

		return $tags;
	}

	public function image_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT name FROM content_images WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function new_content($content, $tags = FALSE)
	{
		$this->pdo->exec('INSERT INTO content (content) VALUES('.$this->pdo->quote($content).');');
		$content_id = $this->pdo->lastInsertId();

		if ($tags)
		{
			$sql = 'INSERT INTO content_tags (content_id, tag_id, tag_value) VALUES';
			foreach ($tags as $tag_name => $tag_values)
			{
				if ($tag_name == 0 && ! is_array($tag_values))
				{
					$sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote(Tags::get_id_by_name($tag_values)).',NULL),';
				}
				else
				{
					if ( ! is_array($tag_values)) $tag_values = array($tag_values);

					foreach ($tag_values as $tag_value)
					{
						if ($tag_value == NULL) $sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).',NULL),';
						else                    $sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).','.$this->pdo->quote($tag_value).'),';
					}
				}

			}
			$this->pdo->exec(substr($sql, 0, strlen($sql) - 1).';');
		}


		return (int) $content_id;
	}

	public function new_image($name, $tags = FALSE)
	{
		if ($this->image_name_available($name))
		{
			$this->pdo->exec('INSERT INTO content_images (name) VALUES('.$this->pdo->quote($name).');');

			if ($tags)
			{
				$sql = 'INSERT INTO content_images_tags (image_name, tag_id, tag_value) VALUES';
				foreach ($tags as $tag_name => $tag_values)
				{
					if ( ! is_array($tag_values)) $tag_values = array($tag_values);

					foreach ($tag_values as $tag_value)
					{
						if ($tag_value == NULL) $sql .= '('.$this->pdo->quote($name).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).',NULL),';
						else                    $sql .= '('.$this->pdo->quote($name).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).','.$this->pdo->quote($tag_value).'),';
					}
				}

				$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
			}

			return TRUE;
		}

		return FALSE;
	}

	public function new_page($name, $URI, $tags = FALSE)
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
				// Add the tag connections
				if ($tags)
				{
					$sql = 'INSERT INTO content_pages_tags (page_id, tag_id, template_field_id) VALUES';

					foreach ($tags as $template_field_id => $tag_ids)
					{
						if ( ! is_array($tag_ids)) $tag_ids = array($tag_ids);

						foreach ($tag_ids as $tag_id)
						{
							$sql .= '('.$this->pdo->quote($page_id).','.$this->pdo->quote($tag_id).','.$this->pdo->quote($template_field_id).'),';
						}
					}

					$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
				}

				return $page_id;
			}
		}

		return FALSE;
	}

	public function page_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT id FROM content_pages WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function rm_content($content_id)
	{
		$this->pdo->exec('DELETE FROM content       WHERE id         = '.$this->pdo->quote($content_id));
		$this->pdo->exec('DELETE FROM content_tags  WHERE content_id = '.$this->pdo->quote($content_id));

		return TRUE;
	}

	public function rm_image($name)
	{
		$this->pdo->exec('DELETE FROM content_images_tags  WHERE image_name = '.$this->pdo->quote($name));
		$this->pdo->exec('DELETE FROM content_images       WHERE name       = '.$this->pdo->quote($name));

		return TRUE;
	}

	public function rm_page($id)
	{
		$this->pdo->exec('DELETE FROM content_pages      WHERE id      = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_pages_tags WHERE page_id = '.$this->pdo->quote($id));

		return TRUE;
	}

	public function rm_tag($id)
	{
		$this->pdo->exec('DELETE FROM content_tags         WHERE tag_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_pages_tags   WHERE tag_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM content_images_tags  WHERE tag_id = '.$this->pdo->quote($id));
		$this->pdo->exec('DELETE FROM tags                 WHERE id     = '.$this->pdo->quote($id));

		return TRUE;
	}

	public function update_content($content_id, $content = FALSE, $tags = FALSE)
	{
		if ($content !== FALSE)
		{
			$this->pdo->exec('
				UPDATE content
				SET    content = '.$this->pdo->quote($content).'
				WHERE  id      = '.$this->pdo->quote($content_id)
			);
		}

		if ($tags !== FALSE && is_array($tags))
		{
			$this->pdo->exec('
				DELETE FROM content_tags
				WHERE       content_id   = '.$this->pdo->quote($content_id).';');

			if (count($tags))
			{
				$sql = 'INSERT INTO content_tags (content_id, tag_id, tag_value) VALUES';
				foreach ($tags as $tag_name => $tag_values)
				{
					if ( ! is_array($tag_values)) $tag_values = array($tag_values);

					foreach ($tag_values as $tag_value)
					{
						if ($tag_value == NULL) $sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).',NULL),';
						else                    $sql .= '('.$this->pdo->quote($content_id).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).','.$this->pdo->quote($tag_value).'),';
					}
				}
				$this->pdo->exec(substr($sql, 0, strlen($sql) - 1).';');
			}
		}

		return (int) $content_id;
	}

	public function update_image_data($image_name, $tags = FALSE)
	{
		if ($tags !== FALSE && is_array($tags))
		{
			// Clear previous data
			$this->pdo->exec('DELETE FROM content_images_tags WHERE image_name = '.$this->pdo->quote($image_name).';');

			if (count($tags))
			{
				$sql = 'INSERT INTO content_images_tags (image_name, tag_id, tag_value) VALUES';

				foreach ($tags as $tag_name => $tag_values)
				{
					if (
						$tag_name != 'name' && // Name is forbidden, that is to be handled by update_image_name
						$tag_name != ''
					)
					{
						if ( ! is_array($tag_values)) $tag_values = array($tag_values);
						foreach ($tag_values as $tag_value)
						{
							$sql .= '('.$this->pdo->quote($image_name).','.$this->pdo->quote(Tags::get_id_by_name($tag_name)).','.$this->pdo->quote($tag_value).'),';
						}
					}
				}
				$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
			}
		}

		return TRUE;
	}

	public function update_image_name($old_image_name, $new_image_name)
	{
		if ($old_image_name != $new_image_name && Content_Image::image_name_available($new_image_name))
		{
			$this->pdo->exec('UPDATE content_images_tags SET image_name = '.$this->pdo->quote($new_image_name).' WHERE image_name = '.$this->pdo->quote($old_image_name));
			$this->pdo->exec('UPDATE content_images      SET       name = '.$this->pdo->quote($new_image_name).' WHERE       name = '.$this->pdo->quote($old_image_name));

			return $this->rename_image_files($old_image_name, $new_image_name);
		}
		return FALSE;
	}

	public function update_page_data($id, $name = FALSE, $URI = FALSE, $tags = FALSE)
	{
		// Nothing to update
		if ($name === FALSE && $URI === FALSE && $tags === FALSE) return TRUE;

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
		if (is_array($tags))
		{
			// First remove all the old ones
			$this->pdo->exec('DELETE FROM content_pages_tags WHERE page_id = '.$this->pdo->quote($id));

			if (count($tags))
			{
				// Then add the new ones
				$sql = 'INSERT INTO content_pages_tags (page_id, tag_id, template_field_id) VALUES';

				foreach ($tags as $template_field_id => $tag_ids)
				{
					foreach ($tag_ids as $tag_id)
					{
						$sql .= '('.$this->pdo->quote($id).','.$this->pdo->quote($tag_id).','.$this->pdo->quote($template_field_id).'),';
					}
				}

				$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
			}
		}

		return TRUE;
	}

}
