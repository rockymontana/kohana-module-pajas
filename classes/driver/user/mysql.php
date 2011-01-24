<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_User_Mysql extends Driver_User
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function check_db_structure()
	{
		$columns = $this->pdo->query('SHOW TABLES like \'user%\';')->fetchAll(PDO::FETCH_COLUMN);
		return count($columns) == 3;
	}
	
	protected function create_db_structure()
	{
		$this->pdo->query('CREATE TABLE `user_data_fields` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `name` (`name`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
		$this->pdo->query('CREATE TABLE `user_users` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
		$this->pdo->query('CREATE TABLE `user_users_data` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) DEFAULT NULL,
			`field_id` int(11) DEFAULT NULL,
			`data` text COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			KEY `users_fields` (`user_id`,`field_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function get_data_field_id($field_name)
	{
		return $this->pdo->query('SELECT id FROM user_data_fields WHERE name = '.$this->pdo->quote($field_name))->fetchColumn();
	}

	public function get_data_field_name($field_id)
	{
		return $this->pdo->query('SELECT name FROM user_data_fields WHERE id = '.$this->pdo->quote($field_id))->fetchColumn();
	}

	public function get_data_fields()
	{
		$data_fields = array();
		foreach ($this->pdo->query('SELECT id, name FROM user_data_fields ORDER BY name;') as $row)
		{
			$data_fields[$row['id']] = $row['name'];
		}

		return $data_fields;
	}

	public function get_user_data($user_id)
	{
		$sql = '
			SELECT
				name AS field_name,
				data
			FROM
				user_users_data
				JOIN
					user_data_fields ON
						user_data_fields.id = user_users_data.field_id
			WHERE user_users_data.user_id = '.$this->pdo->quote($user_id);

		$user_data = array();

		foreach ($this->pdo->query($sql) as $row)
		{
			$user_data[$row['field_name']][] = $row['data'];
		}

		ksort($user_data);

		return $user_data;
	}

	public function get_user_id_by_username_and_password($username, $password)
	{
		return $this->pdo->query('SELECT id FROM user_users WHERE username = '.$this->pdo->quote($username).' AND password = '.$this->pdo->quote($password))->fetchColumn();
	}

	public function get_username_by_id($user_id)
	{
		return $this->pdo->query('SELECT username FROM user_users WHERE id = '.$this->pdo->quote($user_id))->fetchColumn();
	}

	public function get_users($q, $start = FALSE, $limit = FALSE, $order_by = FALSE)
	{
		$sql = '
			SELECT
				id AS user_id,
				username
			FROM
				user_users';

		if (is_string($q))
		{
			$sql .= '
			WHERE
				user_id = '.intval($q).' OR
				username LIKE \'%'.$q.'%\'';

			$user_ids = array();
			foreach ($this->pdo->query('SELECT id FROM user_data_fields') as $row)
			{
				foreach ($this->pdo->query('SELECT user_id FROM user_users_data WHERE data LIKE \'%'.$q.'%\' AND field_id = '.$row['id']) as $row2)
				{
					if (!in_array($row2['user_id'], $user_ids))
					{
						$user_ids[] = $row2['user_id'];
					}
				}
			}

			$sql .= ' OR
				user_id IN ('.implode(',', $user_ids).')';
		}
		elseif (is_array($q))
		{
			$user_ids = array();
			foreach ($q as $field => $data)
			{
				if ($field == 'username')
				{
					foreach ($this->pdo->query('SELECT id FROM user_users WHERE username LIKE \'%'.$data.'%\'') as $row2)
					{
						$user_ids[] = $row2['id'];
					}
				}
				else
				{
					foreach ($this->pdo->query('SELECT user_id FROM user_users_data WHERE data LIKE \'%'.$data.'%\' AND field_id = (SELECT id FROM user_data_fields WHERE name = '.$this->pdo->quote($field).')') as $row2)
					{
						if (!in_array($row2['user_id'], $user_ids))
						{
							$user_ids[] = $row2['user_id'];
						}
					}
				}
			}

			if (count($user_ids))
			{
				$sql .= '
			WHERE
				id IN ('.implode(',', $user_ids).')';
			}
			else
			{
				return array();
			}

		}

		if ($order_by)
		{
			$sql .= '
			ORDER BY '.$this->pdo->quote($order_by);
		}

		if ($limit)
		{
			if ($start)
			{
				$sql .= '
			LIMIT '.$start.','.$limit;
			}
			else
			{
				$sql .= '
			LIMIT '.$limit;
			}
		}

		$users = array();
		foreach ($this->pdo->query($sql) as $row)
		{
			$users[] = array(
				'user_id'  => $row['user_id'],
				'username' => $row['username'],
			);
		}

		return $users;
	}

	public function new_field($field_name)
	{
		if (User::field_name_available($field_name))
		{
			$this->pdo->exec('INSERT INTO user_data_fields (name) VALUES('.$this->pdo->quote($field_name).')');
			return $this->pdo->lastInsertId();
		}
		else return FALSE;
	}

	public function new_user($username, $password, $user_data = array())
	{
		$this->pdo->exec('INSERT INTO user_users (username, password) VALUES('.$this->pdo->quote($username).','.$this->pdo->quote($password).')');

		$user_id = $this->pdo->lastInsertId();

		if (count($user_data))
		{
			$sql = 'INSERT INTO user_users_data (user_id,field_id,data) VALUES';
			foreach ($user_data as $field_name => $field_data)
			{
				if ($field_id = User::get_data_field_id($field_name))
				{
					if (!is_array($field_data))
					{
						$field_data = array($field_data);
					}

					foreach ($field_data as $field_data_piece)
					{
						$sql .= '('.$user_id.','.$field_id.','.$this->pdo->quote($field_data_piece).'),';
					}
				}
			}
			$this->pdo->exec(substr($sql, 0, strlen($sql) - 1));
		}

		return $user_id;
	}

	public function rm_field($field_id)
	{
		$this->pdo->exec('DELETE FROM user_users_data WHERE field_id = '.$this->pdo->quote($field_id));
		$this->pdo->exec('DELETE FROM user_data_fields WHERE id = '.$this->pdo->quote($field_id));
		return TRUE;
	}

	public function rm_user($user_id)
	{
		return $this->pdo->query('DELETE FROM user_users WHERE id = '.$this->pdo->quote($user_id));
	}

	public function set_data($user_id, $user_data, $clear_previous_data = TRUE)
	{

		if ($clear_previous_data)
		{
			$fields = array();
			foreach ($user_data as $field => $content)
			{
				$fields[] = $this->pdo->quote($field);
			}

			if (count($fields))
			{
				$this->pdo->query('
					DELETE user_users_data.*
					FROM user_users_data
						JOIN user_data_fields ON user_data_fields.id = user_users_data.field_id
					WHERE
						user_data_fields.name IN ('.implode(',', $fields).') AND
						user_users_data.user_id = '.$this->pdo->quote($user_id));
			}
		}

		if (count($user_data))
		{
			$sql = 'INSERT INTO user_users_data (user_id, field_id, data) VALUES';
			foreach ($user_data as $field => $content)
			{
				if (!is_array($content))
				{
					$content = array($content);
				}

				foreach ($content as $content_piece)
				{
					$sql .= '
						(
							'.$this->pdo->quote($user_id).',
							(
								SELECT user_data_fields.id
								FROM   user_data_fields
								WHERE  user_data_fields.name = '.$this->pdo->quote($field).'
							),
							'.$this->pdo->quote($content_piece).'
						),';
				}
			}
			$sql = substr($sql, 0, strlen($sql) - 1);

			return $this->pdo->query($sql);
		}

		return TRUE;
	}

	public function set_password($user_id, $password)
	{
		return $this->pdo->query('UPDATE user_users SET password = '.$this->pdo->quote($password).' WHERE id = '.$this->pdo->quote($user_id));
	}

	public function set_username($user_id, $username)
	{
		return $this->pdo->query('UPDATE user_users SET username = '.$this->pdo->quote($username).' WHERE id = '.$this->pdo->quote($user_id));
	}

	public function update_field($field_id, $field_name)
	{
		return $this->pdo->exec('UPDATE user_data_fields SET name = '.$this->pdo->quote($field_name).' WHERE id = '.$this->pdo->quote($field_id));
	}

}
