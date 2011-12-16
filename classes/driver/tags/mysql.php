<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Tags_Mysql extends Driver_Tags
{

	protected function check_db_structure()
	{
		$columns = $this->pdo->query('SHOW TABLES like \'tags%\';')->fetchAll(PDO::FETCH_COLUMN);
		return count($columns) == 1;
	}

	protected function create_db_structure()
	{
		$this->pdo->query('
			CREATE TABLE `tags` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function add($name)
	{
		if ( ! $this->pdo->query('SELECT id FROM tags WHERE name = '.$this->pdo->quote($name))->fetchColumn())
		{
			$this->pdo->exec('INSERT INTO tags (name) VALUES('.$this->pdo->quote($name).')');
			return $this->pdo->lastInsertId();
		}
		else return FALSE;
	}

	public function get_id_by_name($name)
	{
		if ($name == '') return FALSE;

		if ($tag_id = $this->pdo->query('SELECT id FROM tags WHERE name = '.$this->pdo->quote($name))->fetchColumn())
		{
			return $tag_id;
		}

		return self::add($name);
	}

	public function get_name_by_id($id)
	{
		return $this->pdo->query('SELECT name FROM tags WHERE id = '.$this->pdo->quote($id))->fetchColumn();
	}

	public function get_tags($order_by = 'name')
	{
		$tags = array();
		foreach ($this->pdo->query('SELECT id, name FROM tags ORDER BY '.$this->pdo->quote($order_by).';') as $row)
		{
			$tags[$row['id']] = $row['name'];
		}

		return $tags;
	}

	public function rename($id, $new_name)
	{
		if ( ! Tags::get_tag_id_by_name($new_name))
		{
			$this->pdo->exec('UPDATE tags SET name = '.$this->pdo->quote($new_name).' WHERE id = '.$this->pdo->quote($id).';');
			return TRUE;
		}
		else return FALSE;
	}

	public function rm($id)
	{
		$this->pdo->exec('DELETE FROM tags WHERE id = '.$this->pdo->quote($id).';');
		return TRUE;
	}

}
