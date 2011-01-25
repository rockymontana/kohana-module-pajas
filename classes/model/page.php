<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Page extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static $driver;

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
	 * @param int $id - Page ID
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($id)
		{
			$this->page_id = $id;
			if ( ! $this->load_page_data())
			{
				// This page ID does not exist, unset the page id again
				$this->page_id = NULL;
			}
		}
	}
	
	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	/**
	 * Get page data
	 *
	 * @param str $field - If specified, only return this field (id, name, uri, content)
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
	 * Get current page ID
	 *
	 * @return int
	 */
	public function get_page_id()
	{
		return $this->page_id;
	}

	/**
	 * Get page ID by URI
	 *
	 * @param str $uri
	 * @return int
	 */
	public static function get_page_id_by_uri($uri)
	{
		return self::driver()->get_page_id_by_uri($uri);
	}

	/**
	 * Get pages
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        name    => About,
	 *                        uri     => about,
	 *                        content => Lots of page content
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        name    => Contact us,
	 *                        uri     => contact,
	 *                        content => Lots of page content
	 *                      ),
	 *                    )
	 */
	public static function get_pages()
	{
		return self::driver()->get_pages();
	}

	public function load_page_data()
	{
		return ($this->page_data = self::driver()->get_page_data($this->get_page_id()));
	}

	public static function new_page($name, $uri = FALSE, $content = '')
	{
		if ($uri == FALSE)
		{
			$uri = uri::title($name, '-', TRUE);
		}

		return self::driver()->new_page($name, $uri, $content);
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
	 * Checks if a page uri is available
	 *
	 * @param str $uri
	 * @return boolean
	 */
	public static function page_uri_available($uri)
	{
		$routes = Route::all();

		foreach ($routes as $name => $route)
		{

			if ($params = $route->matches($uri))
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
		$driver_name = 'Driver_Page_'.ucfirst(Kohana::config('page.driver'));
		return (self::$driver = new $driver_name);
	}

	public function update_page_data($name, $uri, $content)
	{
		if (self::driver()->update_page_data($this->get_page_id(), $name, $uri, $content))
		{
			// We must update the local class page data also
			$this->load_page_data();
			return TRUE;
		}

		return FALSE;
	}

}
