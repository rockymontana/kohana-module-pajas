<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Page extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			if( ! $this->check_db_structure()) {
				$this->create_db_structure();
				$this->insert_initial_data();
			}
		}
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @return boolean
	 * @author Johnny Karhinen
	 */
	abstract protected function check_db_structure();
	
	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();
	
	protected function insert_initial_data() {
		$this->new_page('Hello world!', 'welcome', 'You have successfully installed Pajas. You can edit this page in the <a href="/admin">administration section</a>.');
	}

	/**
	 * Get page data
	 *
	 * @param int $id - Page ID
	 * @return arr - array(
	 *                 'id'      => 1
	 *                 'name'    => Some page
	 *                 'uri'     => some-page
	 *                 'content' => some content on the page
	 *               )
	 */
	abstract public function get_page_data($id);

	/**
	 * Get page ID by URI
	 *
	 * @param str $uri
	 * @return int
	 */
	abstract public function get_page_id_by_uri($uri);

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
	abstract public function get_pages();

	/**
	 * Create a new page
	 *
	 * @param str $name    - Page name
	 * @param str $uri     - Page URL (Defaults to page name, just URL formatted)
	 * @param str $content - Page content (Defaults to empty string)
	 * @return int page id
	 */
	abstract public function new_page($name, $URL, $content);

	/**
	 * Checks if a page name is available
	 *
	 * @param str $page_name
	 * @return boolean
	 */
	abstract public function page_name_available($page_name);

	/**
	 * Remove a page
	 *
	 * @param int $id - Page ID
	 * @return bool
	 */
	abstract public function rm_page($id);

	/**
	 * Update page data
	 *
	 * @param int $id      - Page ID
	 * @param str $name    - Page name    OPTIONAL
	 * @param str $uri     - Page URL     OPTIONAL
	 * @param str $content - Page content OPTIONAL
	 * @return bool
	 */
	abstract public function update_page_data($id, $name = FALSE, $uri = FALSE, $content = FALSE);

}
