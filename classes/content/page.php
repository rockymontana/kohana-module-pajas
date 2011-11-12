<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Page extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Page data
	 *
	 * @var array
	 */
	private $page_data;

	/**
	 * Page id
	 *
	 * @var integer
	 */
	private $page_id;

	/**
	 * Constructor
	 *
	 * @param int $id - Page id
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($id)
		{
			$this->page_id = $id;
			if ( ! $this->load_page_data())
			{
				// This page id does not exist, unset the page id again
				$this->page_id = NULL;
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
	 * Get page data
	 *
	 * @param str $field - If specified, only return this field (id, name, URI)
	 * @return str or arr
	 */
	public function get_page_data($field = FALSE)
	{
		if ($field)
		{
			if (isset($this->page_data[$field])) return $this->page_data[$field];
			else                                 return FALSE;
		}
		return $this->page_data;
	}

	/**
	 * Get current page id
	 *
	 * @return int
	 */
	public function get_page_id()
	{
		return $this->page_id;
	}

	/**
	 * Get page id by URI
	 *
	 * @param str $URI
	 * @return int
	 */
	public static function get_page_id_by_URI($URI)
	{
		return self::driver()->get_page_id_by_URI($URI);
	}

	/**
	 * Get pages
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        name    => About,
	 *                        URI     => about,
	 *                        'tag_ids'  => array(
	 *                                        template_field_id => tag_ids
	 *                                        1 => array(1),
	 *                                        2 => array(4, 5, 8),
	 *                                        5 => array(2),
	 *                                      )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        name    => Contact us,
	 *                        URI     => contact,
	 *                        'tag_ids'  => array(
	 *                                        template_field_id => tag_ids
	 *                                        1 => array(1),
	 *                                        2 => array(4, 5, 8),
	 *                                        5 => array(2),
	 *                                      )
	 *                      ),
	 *                    )
	 */
	public static function get_pages()
	{
		return self::driver()->get_pages();
	}

	/**
	 * Get all tags associated with pages
	 *
	 * @return arr - array(
	 *                 1 => array(
	 *                   'id'     => 1,
	 *                   'name'   => 'location',
	 *                   'values' => array('stockholm', 'uppsala'),
	 *                 )
	 *                 3 => array(
	 *                   'id'     => 3,
	 *                   'name'   => 'blogpost',
	 *                   'values' => array(NULL),
	 *                 )
	 *               )
	 */
	public static function get_tags()
	{
		return self::driver()->get_tags_by_content_id();
	}

	/**
	 * Load the page data into class cache array
	 *
	 * @return boolean
	 */
	public function load_page_data()
	{
		return ($this->page_data = self::driver()->get_page_data($this->get_page_id()));
	}

	/**
	 * Create a new page
	 *
	 * @param str $name
	 * @param str $URI      OPTIONAL
	 * @param arr $tags     OPTIONAL template position as key, array of tag IDs as value
	 * @return int page id
	 */
	public static function new_page($name, $URI = FALSE, $tags = FALSE)
	{
		if ($URI == FALSE) $URI = uri::title($name, '-', TRUE);

		return self::driver()->new_page($name, $URI, $tags);
	}

	/**
	 * Checks if a page name is available
	 *
	 * @param str $page_name
	 * @return boolean
	 */
	public static function page_name_available($name)
	{
		return self::driver()->page_name_available($name);
	}

	/**
	 * Checks if a page URI is available
	 *
	 * @param str $URI
	 * @return boolean
	 */
	public static function page_URI_available($URI)
	{
		$routes = Route::all();

		foreach ($routes as $name => $route)
		{

			if ($params = $route->matches($URI))
			{
				if ($default_params = $route->matches(''))
				{
					if ($params['controller'] != 'staticpage')
					{
						return TRUE;
					}
				}

				if (
					class_exists('controller_'.$params['controller']) &&
					method_exists('controller_'.$params['controller'], 'action_'.$params['action'])
				)
				{
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	public function rm_page()
	{
		if (self::driver()->rm_page($this->get_page_id()))
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
		$driver_name = 'Driver_Content_'.ucfirst(Kohana::$config->load('content.driver'));
		return (self::$driver = new $driver_name);
	}

	/**
	 * Update page data
	 *
	 * @param str $name     OPTIONAL
	 * @param str $URI      OPTIONAL
	 * @param arr $tags     OPTIONAL - template position as key, array of tag IDs as value
	 * @return boolean
	 */
	public function update_page_data($name = FALSE, $URI = FALSE, $tags = FALSE)
	{
		if (self::driver()->update_page_data($this->get_page_id(), $name, $URI, $tags))
		{
			// We must update the local class page data also
			$this->load_page_data();
			return TRUE;
		}

		return FALSE;
	}

}
