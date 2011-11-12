<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Tags
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static $driver;

	/**
	 * Saved instance of this object for later fetching
	 *
	 * @var object
	 */
	static $instance;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(); // Connect to the database

		if ( ! self::$instance)
		{
			self::$instance = $this;
			self::set_driver();
		}
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Tags_'.ucfirst(Kohana::$config->load('tags.driver'));
		return (self::$driver = new $driver_name);
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	public static function add($name)
	{
		return self::driver()->add($name);
	}

	public static function get_id_by_name($name)
	{
		return self::driver()->get_id_by_name($name);
	}

	public static function get_name_by_id($id)
	{
		return self::driver()->get_name_by_id($id);
	}

	public static function get_tags($order_by = 'name')
	{
		return self::driver()->get_tags($order_by);
	}

	public static function rename($id, $new_name)
	{
		return self::driver()->rename($id, $new_name);
	}

	public static function rm($id)
	{
		return self::driver()->rm($id);
	}
}
