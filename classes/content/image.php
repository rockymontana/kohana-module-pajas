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
	 * @param str or array  $names      - Fetch specific images based on name
	 * @param arr           $tags       - Fetch specific images based on tags,
	 *                                    key as tag name, value as array of
	 *                                    tag values.
	 *                                    If value is boolean TRUE, all images
	 *                                    with this tag will be fetched.
	 *
	 *                                    example:
	 *                                    array(
	 *                                      'car'    => TRUE,
	 *                                      'colors' => array('red', 'green'),
	 *                                    )
	 *
	 * @param bol           $names_only - Make this method return an array of image
	 *                                    names only
	 * @return arr - array(
	 *                 // Image name
	 *                 'foobar' => array(
	 *                               // Image tags
	 *                               'date'        => array('2011-05-03'),
	 *                               'description' => array('Some description'),
	 *                               'tag'         => array('car', 'blue', 'fast'),
	 *                               etc...
	 *                             ),
	 *               )
	 */
	public static function get_images($names = NULL, $tags = array(), $names_only = FALSE)
	{
		return self::driver()->get_images($names, $tags, $names_only);
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
	 * Get all tags associated with images
	 *
	 * @return arr - array(
	 *                 1 => array(
	 *                   'name'   => 'location',
	 *                   'values' => array('stockholm', 'uppsala'),
	 *                 )
	 *                 3 => array(
	 *                   'name'   => 'blogpost',
	 *                   'values' => array(NULL),
	 *                 )
	 *               )
	 */
	public static function get_tags()
	{
		return self::driver()->get_tags_by_image_name();
	}

	/**
	 * Checks if an image name is available
	 *
	 * @param str $name (With or without file ending)
	 * @return boolean
	 */
	public static function image_name_available($name)
	{
		$name_pi = pathinfo($name);

		foreach (self::driver()->get_images() as $image_name => $image_data)
		{
			$image_name_pi = pathinfo($image_name);

			if ($image_name_pi['filename'] == $name_pi['filename']) return FALSE;
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
	 * New image
	 *
	 * @param str $name - Image name
	 * @param arr $tags
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 * @return boolean
	 */
	public static function new_image($name, $tags = FALSE)
	{
		return self::driver()->new_image($name, $tags);
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

	/**
	 * Set image data
	 *
	 * @param arr $tags
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 */
	public function set_data($tags = array())
	{
		if ($this->get_name())
		{
			self::driver()->update_image_data($this->get_name(), $tags);
			if (
					isset($tags['name']) &&
					is_string($tags['name']) &&
					$this->get_name() != $tags['name']
				)
			{
				// Clean out old cache images
				$cache_images = glob(Kohana::$cache_dir.'/user_content/images/'.$this->get_name().'*');
				foreach ($cache_images as $image_to_delete) unlink($image_to_delete);

				self::driver()->update_image_name($this->get_name(), $tags['name']);
			}

			// Update this objects information from the database
			$this->load_data();
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
