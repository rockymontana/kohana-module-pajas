<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Tags extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			if ( ! $this->check_db_structure())
			{
				$this->create_db_structure();
				$this->insert_initial_data();
			}
		}
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	abstract protected function check_db_structure();

	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();

	/**
	 * Insert initial data
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	protected function insert_initial_data()
	{
		return TRUE;
	}

	/**
	 * Add a new tag
	 *
	 * @var $name str
	 * @return int - Tag ID
	 */
	abstract public function add($name);

	/**
	 * Get tag ID by Name
	 *
	 * @var @name str - Tag Name
	 * @return int    - Tag ID
	 */
	abstract public function get_id_by_name($name);

	/**
	 * Get tag name by ID
	 *
	 * @var $id int - Tag ID
	 * @return str  - Tag Name
	 */
	abstract public function get_name_by_id($id);

	/**
	 * Get tags
	 *
	 * @var $order_by str - Either "name" or "id"
	 * @return array      - Tag IDs as key, name as value
	 */
	abstract public function get_tags($order_by = 'name');

	/**
	 * Rename a tag
	 *
	 * @var $id int       - Tag ID
	 * @var $new_name str - New Name
	 * @return boolean
	 */
	abstract public function rename($id, $new_name);

	/**
	 * Remove a tag
	 *
	 * @var $id int - Tag ID
	 * @return boolean
	 */
	abstract public function rm($id);

}
