<?php defined('SYSPATH') OR die('No direct access allowed.');

class Content_Content extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static private $driver;

	/**
	 * Content
	 *
	 * @var str
	 */
	private $content;

	/**
	 * Content id
	 *
	 * @var int
	 */
	private $content_id;

	/**
	 * Type ids connected to this content
	 *
	 * @var array of ints
	 */
	private $type_ids;

	/**
	 * Constructor
	 *
	 * @param int $id - Content id
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct(); // Connect to the database

		if ($id)
		{
			$this->content_id = $id;
			if ( ! $this->load_content())
			{
				// This content id does not exist, unset the page id again
				$this->content_id = NULL;
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
	 * Get content
	 *
	 * @return str
	 */
	public function get_content()
	{
		return $this->content;
	}

	/**
	 * Get current content id
	 *
	 * @return int
	 */
	public function get_content_id()
	{
		return $this->content_id;
	}

	/**
	 * Get contents
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                        types   => array(
	 *                                     array(
	 *                                       id   => 3,
	 *                                       type => blog post,
	 *                                     )
	 *                                   )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        types   => array(
	 *                                     array(
	 *                                       id   => 4,
	 *                                       type => News,
	 *                                     )
	 *                                     array(
	 *                                       id   => 5,
	 *                                       type => RSS post,
	 *                                     )
	 *                                   )
	 *                        content => Lots of content
	 *                      ),
	 *                    )
	 */
	public static function get_contents()
	{
		return self::driver()->get_contents();
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
