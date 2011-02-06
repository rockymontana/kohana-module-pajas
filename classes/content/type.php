<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Type extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Type data
	 *
	 * @var array
	 */
	private $type_data;

	/**
	 * Type id
	 *
	 * @var integer
	 */
	private $type_id;

	/**
	 * Constructor
	 *
	 * @param int $id - Type id
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($id)
		{
			$this->type_id = $id;
			if ( ! $this->load_type_data())
			{
				// This type id does not exist, unset the type id again
				$this->type_id = NULL;
			}
		}
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
	 * Get type data
	 *
	 * @param str $field - If specified, only return this field (id, name, description)
	 * @return str or arr
	 */
	public function get_type_data($field = FALSE)
	{
		if ($field)
		{
			if (isset($this->type_data[$field])) return $this->type_data[$field];
			else                                 return FALSE;
		}
		return $this->type_data;
	}

	/**
	 * Get current type id
	 *
	 * @return int
	 */
	public function get_type_id()
	{
		return $this->type_id;
	}

	/**
	 * Get type id by name
	 *
	 * @param str $name
	 * @return int
	 */
	public static function get_type_id_by_name($name)
	{
		return self::driver()->get_type_id_by_name($name);
	}

	/**
	 * Get types
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id          => 1,
	 *                        name        => News,
	 *                        description => This content type is for a news feed,
	 *                      ),
	 *                      array(
	 *                        id          => 2,
	 *                        name        => Page: welcome,
	 *                        description => Text fields on the page "welcome",
	 *                      ),
	 *                    )
	 */
	public static function get_types()
	{
		return self::driver()->get_types();
	}

	/**
	 * Load the type data into class cache array
	 *
	 * @return boolean
	 */
	public function load_type_data()
	{
		return ($this->type_data = self::driver()->get_type_data($this->get_type_id()));
	}

	/**
	 * Create a new content type
	 *
	 * @param str $name
	 * @param str $description
	 * @return int new content type id
	 */
	public static function new_type($name, $description = '')
	{
		return self::driver()->new_type($name, $description);
	}

	/**
	 * Remove this content type
	 *
	 * @return boolean
	 */
	public function rm_type()
	{
		if (self::driver()->rm_type($this->get_type_id()))
		{
			unset($this);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Checks if a type name is available
	 *
	 * @param str $name
	 * @return boolean
	 */
	public static function type_name_available($name)
	{
		return self::driver()->type_name_available($name);
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

	/**
	 * Update type data
	 *
	 * @param str $name        OPTIONAL
	 * @param str $description OPTIONAL
	 * @return boolean
	 */
	public function update_type_data($name = FALSE, $description = FALSE)
	{
		if (self::driver()->update_type_data($this->get_type_id(), $name, $description))
		{
			// We must update the local class type data also
			$this->load_type_data();
			return TRUE;
		}

		return FALSE;
	}

}
