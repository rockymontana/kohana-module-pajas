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
			} else return TRUE;
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
	 * Checks if an image name is available
	 *
	 * @param str $name (With or without file ending)
	 * @return boolean
	 */
	public static function image_name_available($name)
	{
		foreach (self::driver()->get_images() as $image_name => $image_data)
		{
			// First remove possible file ending from both names
			if (substr($image_name, 0, strlen($image_name) - 4) == substr($name, 0, strlen($name) - 4)) return FALSE;

			// Then try without removing the file ending
			if (substr($image_name, 0, strlen($image_name) - 4) == $name)                               return FALSE;
		}

		// No matches found, image name is available
		return TRUE;
	}

	/**
	 * Load data from database
	 *
	 * @return boolean
	 */
	private function load_data()
	{
		$data = self::driver()->get_images($this->get_name());

		if (count($data))  return $this->data = $data[$this->get_name()];
		else               return FALSE;
	}

	/**
	 * Remove an image
	 *
	 * @return boolean
	 */
	public function rm_image()
	{
		if ($this->get_name())
		{
			// Remove files
			$cache_images = glob(Kohana::$cache_dir.'/user_content/images/'.$this->get_name().'*');
			foreach ($cache_images as $image_to_delete) unlink($image_to_delete);
			unlink(Kohana::config('user_content.dir').'/images/'.$this->get_name());

			// Remove from database
			self::driver()->rm_image($this->get_name());

			unset($this); // Destroy instance

			return TRUE;
		}

		return FALSE;
	}

	public function set_data($data = array())
	{
		if ($this->get_name())
		{
			self::driver()->update_image_data($this->get_name(), $data);
			if (
					isset($data['name']) &&
					is_string($data['name']) &&
					$this->get_name() != $data['name']
				)
			{
				self::driver()->update_image_name($this->get_name(), $data['name']);
			}
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
