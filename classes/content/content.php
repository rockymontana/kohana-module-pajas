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
	 * Tag IDs connected to this content
	 *
	 * @var array - tag name as key, tag values as array value
	 *              example:
	 *              array(
	 *                'location' => array('stockholm', 'tokyo'),
	 *                'blogpost' => array(NULL),
	 *              )
	 */
	private $tags;

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
	 *                        tags    => array(
	 *                                     array(
	 *                                       id   => 3,
	 *                                       name => blog post,
	 *                                     )
	 *                                   )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        tags    => array(
	 *                                     array(
	 *                                       id   => 4,
	 *                                       name => News,
	 *                                     )
	 *                                     array(
	 *                                       id   => 5,
	 *                                       name => RSS post,
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
	 * Get contents by tag id
	 *
	 * @param int $tag_id
	 * @return array of content ids - ex array(1, 3, 4
	 *                      array(
	 *                        id      => 1,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        content => Lots of content
	 *                        tags    => array(
	 *                          date     => array('2011-05-30'),
	 *                          blogpost => array(NULL)
	 *                          location => array('stockholm', 'uppsala')
	 *                        )
	 *                      ),
	 *                    )
	 */
	public static function get_contents_by_tag_id($tag_id)
	{
		return self::driver()->get_contents_by_tag_id($tag_id);
	}

	public function get_tags()
	{
		return $this->tags;
	}

	public function load_content()
	{
		$this->tags     = self::driver()->get_tags_by_content_id($this->get_content_id());
		$this->content  = self::driver()->get_content($this->get_content_id());
		return TRUE;
	}

	public static function new_content($content, $tags = FALSE)
	{
		return self::driver()->new_content($content, $tags);
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

	public function update_content($content, $tags = FALSE)
	{
		if (self::driver()->update_content($this->get_content_id(), $content, $tags))
		{
			// We must update the local class content also
			$this->load_content();
			return TRUE;
		}

		return FALSE;
	}

}
