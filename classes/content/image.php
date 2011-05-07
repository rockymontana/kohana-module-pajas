<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Image extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Image name
	 *
	 * @var str
	 */
	private $name;

	/**
	 * Image data
	 *
	 * @var arr
	 */
	private $data;

	/**
	 * Constructor
	 *
	 * @param str $name - Image name
	 */
	public function __construct($name = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($name)
		{
			$this->name = $name;
			if ( ! $this->load_data())
			{
				// This image does not exist, unset the name again
				$this->name = NULL;
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
	 * Get data
	 *
	 * @return arr
	 */
	public function get_data()
	{
		return $this->data;
	}

	/**
	 * Get images
	 *
	 * @return array - ex array(
	 */
	public static function get_images()
	{
		return self::driver()->get_images();
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
	 * Get contents by type id
	 *
	 * @param int $type_id
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        content => Lots of content
	 *                      ),
	 *                    )
	 */
	public static function get_contents_by_type($type_id)
	{
		return self::driver()->get_contents_by_type_id($type_id);
	}

	public function get_type_ids()
	{
		return $this->type_ids;
	}

	public function load_content()
	{
		$this->type_ids = self::driver()->get_type_ids_by_content_id($this->get_content_id());
		$this->content  = self::driver()->get_content($this->get_content_id());
		return TRUE;
	}

	public static function new_content($content, $type_ids = FALSE)
	{
		return self::driver()->new_content($content, $type_ids);
	}

	public function rm_content()
	{
		if (self::driver()->rm_content($this->get_content_id()))
		{
			unset($this);
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

	public function update_content($content, $type_ids = FALSE)
	{
		if (self::driver()->update_content($this->get_content_id(), $content, $type_ids))
		{
			// We must update the local class content also
			$this->load_content();
			return TRUE;
		}

		return FALSE;
	}

}
