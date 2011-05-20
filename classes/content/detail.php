<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Detail extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Detail ID
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Detail name
	 *
	 * @var str
	 */
	private $name;

	/**
	 * Constructor
	 *
	 * @param str $name - Detail name
	 */
	public function __construct($name = FALSE, $id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($name)
		{
			if ($id = self::get_id_by_name($name))
			{
				$this->id   = intval($id);
				$this->name = $name;

				return TRUE;
			}
		}
		elseif ($id)
		{
			if ($name = self::get_name_by_id($id))
			{
				$this->id   = intval($id);
				$this->name = $name;

				return TRUE;
			}
		}
		return FALSE;
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

	/**
	 * Get details
	 *
	 * @return array - ex array(
	 */
	public static function get_details()
	{
		return self::driver()->get_details();
	}

	/**
	 * Get current ID
	 *
	 * @return int
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Get detail id by name
	 *
	 * @param str $name
	 * @return int
	 */
	public static function get_id_by_name($name)
	{
		return self::driver()->get_detail_id($name);
	}

	/**
	 * Get current name
	 *
	 * @return str
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Get name by id
	 *
	 * @param int $id
	 * @return str
	 */
	public static function get_name_by_id($id)
	{
		return self::driver()->get_detail_name($id);
	}

	/**
	 * Checks if a detail name is available
	 *
	 * @param str $name
	 * @return boolean
	 */
	public static function name_available($name)
	{
		return (bool) ! self::get_id_by_name($name);
	}

	/**
	 * Create a new detail
	 *
	 * @param str $name
	 * @return int - The new detail id
	 */
	public static function new_detail($name)
	{
		return self::driver()->new_detail($name);
	}

	/**
	 * Remove detail
	 *
	 * @return boolean
	 */
	public function rm_detail()
	{
		if ($this->get_id())
		{
			// Remove from database
			self::driver()->rm_detail($this->get_id());

			unset($this); // Destroy instance

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Content_'.ucfirst(Kohana::config('content.driver'));
		return (self::$driver = new $driver_name);
	}

}
