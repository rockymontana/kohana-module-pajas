<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Page_Mysql extends Driver_Page
{

	public function __construct()
	{
		parent::__construct();
	}

	public function check_db_structure()
	{
		$columns = $this->pdo->query('SHOW TABLES like \'page%\';')->fetchAll(PDO::FETCH_COLUMN);
		if (count($columns) != 1)
		{
			$this->pdo->query('CREATE TABLE IF NOT EXISTS `page_pages` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`content` text COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `uri` (`uri`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			');
		}

		return TRUE;
	}

	public function get_page_data($id)
	{
		return $this->pdo->query('SELECT * FROM page_pages WHERE id = '.$this->pdo->quote($id))->fetch(PDO::FETCH_ASSOC);
	}

	public function get_page_id_by_uri($uri)
	{
		return $this->pdo->query('SELECT id FROM page_pages WHERE uri = '.$this->pdo->quote($uri))->fetchColumn();
	}

	public function get_pages()
	{
		return $this->pdo->query('SELECT * FROM page_pages ORDER BY name;')->fetchAll();
	}

	public function new_page($name, $uri, $content)
	{
		if ($this->page_name_available($name) && Page::page_uri_available($uri))
		{
			$this->pdo->exec('
				INSERT INTO page_pages
				(name, uri, content)
				VALUES('.$this->pdo->quote($name).','.$this->pdo->quote($uri).','.$this->pdo->quote($content).');');

			$page_id = $this->pdo->lastInsertId();

			if ($page_id)
			{
				return $page_id;
			}
		}

		return FALSE;
	}

	public function page_name_available($name)
	{
		return ! (bool) $this->pdo->query('SELECT id FROM page_pages WHERE name = '.$this->pdo->quote($name))->fetchColumn();
	}

	public function rm_page($id)
	{
		$this->pdo->exec('DELETE FROM page_pages WHERE id = '.$this->pdo->quote($id));

		return TRUE;
	}

	public function update_page_data($id, $name = FALSE, $uri = FALSE, $content = FALSE)
	{
		$sql = 'UPDATE page_pages SET ';

		if (!($current_page_data = $this->get_page_data($id)))
		{
			return FALSE;
		}

		if ($name && $current_page_data['name'] != $name)
		{
			if ($this->page_name_available($name))
			{
				$sql .= 'name = '.$this->pdo->quote($name).', ';
			}
			else return FALSE;
		}

		if ($uri && $current_page_data['uri'] != $uri)
		{
			if (Page::page_uri_available($uri))
			{
				$sql .= 'uri = '.$this->pdo->quote($uri).', ';
			}
			else return FALSE;
		}

		if ($content)
		{
			$sql .= 'content = '.$this->pdo->quote($content).', ';
		}

		$sql = substr($sql, 0, strlen($sql) - 2);

		$sql .= ' WHERE id = '.$this->pdo->quote($id);

		$this->pdo->exec($sql);

		return TRUE;
	}

}
