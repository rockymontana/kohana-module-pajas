<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Content extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			if ( ! $this->check_db_structure())
			{
				$this->create_db_structure();
				$this->insert_initial_data();
			}
		}
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	abstract protected function check_db_structure();

	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();

	/**
	 * Insert initial data
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	protected function insert_initial_data()
	{
		$this->new_type('page_welcome', 'Text fields on the page "welcome"');
		$this->new_type('Puff 1', 'This is a puff');
		$this->new_type('Puff 2', 'Another puff');
		$this->new_type('Puff 3', 'Yet another puff');

		$this->new_content('# A First Level Header'."\n\n".'## A Second Level Header'."\n\n".'Some of these words *are emphasized*.'."\n".'Some of these words _are emphasized also_.'."\n\n".'Use two asterisks for **strong emphasis**.'."\n".'Or, if you prefer, __use two underscores instead__.'."\n\n".'Unordered (bulleted) lists use asterisks, pluses, and hyphens (*, +, and -) as list markers. These three markers are interchangable; this:'."\n\n".'*   Candy.'."\n".'*   Gum.'."\n".'*   Booze.'."\n\n".'Ordered (numbered) lists use regular numbers, followed by periods, as list markers:'."\n\n".'1.  Red'."\n".'2.  Green'."\n".'3.  Blue'."\n\n".'More basics at [Daring Fireball](http://daringfireball.net/projects/markdown/basics).', array(1));
		$this->new_content('### Help'."\n\n".'You can access the admin with [this link](admin).'."\n\n".'See online help [here](http://larvit.se/pajas).'."\n\n".'Wiki [here](https://github.com/lillem4n/kohana-module-pajas/wiki)', array(2));
		$this->new_content('### Col 2'."\n\n".'Lorem ipsum dolor sit amet, consectetur adipizscing elit. Fusce velit quam, pharetra id, vehicula eu, consectetur ut, orci. Donec odio. Donec non neque. Ut rutrum lectus nec elit. Ut id quam. Cras aliquam erat eu mi. Aliquam orci neque, lobortis a, tempus ut, lacinia sit amet, purus.', array(3));
		$this->new_content('### Col 3'."\n\n".'Lorem ipsum dolor sit amet, consectetur adipizscing elit. Fusce velit quam, pharetra id, vehicula eu, consectetur ut, orci. Donec odio. Donec non neque. Ut rutrum lectus nec elit. Ut id quam. Cras aliquam erat eu mi. Aliquam orci neque, lobortis a, tempus ut, lacinia sit amet, purus.', array(4));

		// We set the URI to 'welcome' since that is Kohanas default route
		$this->new_page('Hello world!', 'welcome', array(1=>1,2=>2,3=>3,4=>4));

		return TRUE;
	}

	/**
	 * Get content
	 *
	 * @param int $content_id
	 * @return str
	 */
	abstract public function get_content($content_id);

	/**
	 * Get all contents
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
	abstract public function get_contents();

	/**
	 * Get contents by type id
	 *
	 * @param int $type_id
	 * @return arr - array(
	 *                 1 => Here be contents for content id 1,
	 *                 2 => And here is content for content id 2
	 *               )
	 */
	abstract public function get_contents_by_type_id($type_id);

	/**
	 * Get page data
	 *
	 * @param int $id - Page ID
	 * @return arr - array(
	 *                 'id'       => 1
	 *                 'name'     => Some page
	 *                 'URI'      => some-page
	 *                 'type_ids' => array(
	 *                                 template_field_id => type_id
	 *                                 1 => 1,
	 *                                 2 => 4,
	 *                                 5 => 2,
	 *                               )
	 *               )
	 */
	abstract public function get_page_data($id);

	/**
	 * Get page ID by URI
	 *
	 * @param str $URI
	 * @return int
	 */
	abstract public function get_page_id_by_URI($URI);

	/**
	 * Get pages
	 *
	 * @return array - ex array(
	 *                      array(
	 *                        id      => 1,
	 *                        name    => About,
	 *                        URI     => about,
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        name    => Contact us,
	 *                        URI     => contact,
	 *                      ),
	 *                    )
	 */
	abstract public function get_pages();

	/**
	 * Get type data
	 *
	 * @param int $id - Page ID
	 * @return arr - array(
	 *                 'id'          => 1
	 *                 'name'        => Some content type
	 *                 'description' => Description of this type
	 *               )
	 */
	abstract public function get_type_data($id);

	/**
	 * Get type ID by name
	 *
	 * @param str $name
	 * @return int
	 */
	abstract public function get_type_id_by_name($name);

	/**
	 * Get type ids by content id
	 *
	 * @param int $content_id
	 * @return array of ints
	 */
	abstract public function get_type_ids_by_content_id($content_id);

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
	abstract public function get_types();

	/**
	 * New content
	 *
	 * @param str $content
	 * @param arr $type_ids - array of ints OPTIONAL
	 * @return int content id
	 */
	abstract public function new_content($content, $type_ids = FALSE);

	/**
	 * Create a new page
	 *
	 * @param str $name     - Page name
	 * @param str $URI      - Page URL (Defaults to page name, just URL formatted)
	 * @param str $content  - Page content (Defaults to empty string)
	 * @param arr $type_ids - Array with template_field_id as key and type_id as value
	 * @return int page id
	 */
	abstract public function new_page($name, $URL, $type_ids = FALSE);

	/**
	 * Create a new type
	 *
	 * @param str $name        - Content type name
	 * @param str $description - Content type description OPTIONAL
	 * @return int type id
	 */
	abstract public function new_type($name, $description = '');

	/**
	 * Checks if a page name is available
	 *
	 * @param str $page_name
	 * @return boolean
	 */
	abstract public function page_name_available($page_name);

	/**
	 * Remove content
	 *
	 * @param int $content_id
	 * @return boolean
	 */
	abstract public function rm_content($content_id);

	/**
	 * Remove a page
	 *
	 * @param int $id - Page ID
	 * @return bool
	 */
	abstract public function rm_page($id);

	/**
	 * Remove a type
	 *
	 * @param int $id - type ID
	 * @return bool
	 */
	abstract public function rm_type($id);

	/**
	 * Checks if a type name is available
	 *
	 * @param str $name
	 * @return boolean
	 */
	abstract public function type_name_available($name);

	/**
	 * Update content
	 *
	 * @param int $content_id
	 * @param str $content    OPTIONAL
	 * @param arr $type_ids   OPTIONAL
	 * @return boolean
	 */
	abstract public function update_content($content_id, $content = FALSE, $type_ids = FALSE);

	/**
	 * Update page data
	 *
	 * @param int $id       - Page ID
	 * @param str $name     - Page name                                                    OPTIONAL
	 * @param str $URI      - Page URL                                                     OPTIONAL
	 * @param arr $type_ids - Array with template_field_id as key and type_id as value     OPTIONAL
	 * @return bool
	 */
	abstract public function update_page_data($id, $name = FALSE, $URI = FALSE, $type_ids = FALSE);

	/**
	 * Update content type data
	 *
	 * @param int $id          - Content Type ID
	 * @param str $name        - Content Type name        OPTIONAL
	 * @param str $description - Content Type description OPTIONAL
	 * @return bool
	 */
	abstract public function update_type_data($id, $name = FALSE, $description = FALSE);

}
