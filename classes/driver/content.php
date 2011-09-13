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

		// Add image files that does not exist in database
		$tracked_images = $this->get_images(NULL, array(), TRUE);
		$cwd            = getcwd();
		chdir(Kohana::config('user_content.dir').'/images');
		$actual_images  = glob('*.jpg');
		chdir($cwd);
		foreach (array_diff($actual_images, $tracked_images) as $non_tracked_image)
		{
			if (@$gd_img_object = ImageCreateFromJpeg(Kohana::config('user_content.dir').'/images/'.$non_tracked_image))
			{
				$gd_img_object = ImageCreateFromJpeg(Kohana::config('user_content.dir').'/images/'.$non_tracked_image);
				$width         = imagesx($gd_img_object);
				$height        = imagesy($gd_img_object);
				$this->new_image($non_tracked_image, array('width'=>$width,'height'=>$height));
			}
			else
			{
				// This jpg file is not a valid jpg image
				$path_parts   = pathinfo($non_tracked_image);
				$new_filename = $path_parts['filename'].'.broken_jpg';
				while (file_exists(Kohana::config('user_content.dir').'/images/'.$new_filename))
				{
					if ( ! isset($counter)) $counter = 1;
					$new_filename = $path_parts['filename'].'_'.$counter.'.broken_jpg';
					$counter++;
				}
				@rename(Kohana::config('user_content.dir').'/images/'.$non_tracked_image, Kohana::config('user_content.dir').'/images/'.$new_filename);
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
		Tags::add('page_welcome');
		Tags::add('Puff 1');
		Tags::add('Puff 2');
		Tags::add('Puff 3');

		$this->new_content('# A First Level Header'."\n\n".'## A Second Level Header'."\n\n".'Some of these words *are emphasized*.'."\n".'Some of these words _are emphasized also_.'."\n\n".'Use two asterisks for **strong emphasis**.'."\n".'Or, if you prefer, __use two underscores instead__.'."\n\n".'Unordered (bulleted) lists use asterisks, pluses, and hyphens (*, +, and -) as list markers. These three markers are interchangable; this:'."\n\n".'*   Candy.'."\n".'*   Gum.'."\n".'*   Booze.'."\n\n".'Ordered (numbered) lists use regular numbers, followed by periods, as list markers:'."\n\n".'1.  Red'."\n".'2.  Green'."\n".'3.  Blue'."\n\n".'More basics at [Daring Fireball](http://daringfireball.net/projects/markdown/basics).', array('page_welcome'));
		$this->new_content('### Help'."\n\n".'You can access the admin with [this link](admin).'."\n\n".'See online help [here](http://larvit.se/pajas).'."\n\n".'Wiki [here](https://github.com/lillem4n/kohana-module-pajas/wiki)', array('Puff 1'));
		$this->new_content('### Col 2'."\n\n".'Lorem ipsum dolor sit amet, consectetur adipizscing elit. Fusce velit quam, pharetra id, vehicula eu, consectetur ut, orci. Donec odio. Donec non neque. Ut rutrum lectus nec elit. Ut id quam. Cras aliquam erat eu mi. Aliquam orci neque, lobortis a, tempus ut, lacinia sit amet, purus.', array('Puff 2'));
		$this->new_content('### Col 3'."\n\n".'Lorem ipsum dolor sit amet, consectetur adipizscing elit. Fusce velit quam, pharetra id, vehicula eu, consectetur ut, orci. Donec odio. Donec non neque. Ut rutrum lectus nec elit. Ut id quam. Cras aliquam erat eu mi. Aliquam orci neque, lobortis a, tempus ut, lacinia sit amet, purus.', array('Puff 3'));

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
	 *                        tags    => array(
	 *                                     array(
	 *                                       id    => 3,
	 *                                       name  => blog post,
	 *                                       value => NULL,
	 *                                     )
	 *                                   )
	 *                      ),
	 *                      array(
	 *                        id      => 2,
	 *                        tags    => array(
	 *                                     array(
	 *                                       id    => 4,
	 *                                       name  => Date,
	 *                                       value => 2011-07-04,
	 *                                     )
	 *                                     array(
	 *                                       id    => 5,
	 *                                       name  => RSS post,
	 *                                       value => NULL,
	 *                                     )
	 *                                   )
	 *                        content => Lots of content
	 *                      ),
	 *                    )
	 */
	abstract public function get_contents();

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
	abstract public function get_contents_by_tag_id($tag_id);

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
	abstract public function get_images($names = NULL, $tags = array(), $names_only = FALSE);

	/**
	 * Get page data
	 *
	 * @param int $id - Page ID
	 * @return arr - array(
	 *                 'id'       => 1
	 *                 'name'     => Some page
	 *                 'URI'      => some-page
	 *                 'tag_ids'  => array(
	 *                                 template_field_id => tag_ids
	 *                                 1 => array(1),
	 *                                 2 => array(4, 5, 8),
	 *                                 5 => array(2),
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
	abstract public function get_pages();

	/**
	 * Get tag ids by content id
	 *
	 * @param int $content_id OPTIONAL
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
	abstract public function get_tags_by_content_id($content_id = FALSE);

	/**
	 * Get tag ids by image name
	 *
	 * @param str $image_name OPTIONAL
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
	abstract public function get_tags_by_image_name($image_name = FALSE);

	/**
	 * Checks if a image name is available
	 *
	 * @param str $name - Image name
	 * @return boolean
	 */
	abstract public function image_name_available($name);

	/**
	 * New content
	 *
	 * @param str $content
	 * @param arr $tags
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 * @return int content id
	 */
	abstract public function new_content($content, $tags = FALSE);

	/**
	 * New image
	 *
	 * @param str $name - Image name
	 * @param arr $tags
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 * @return boolean
	 */
	abstract public function new_image($name, $tags = FALSE);

	/**
	 * Create a new page
	 *
	 * @param str $name     - Page name
	 * @param str $URI      - Page URL (Defaults to page name, just URL formatted)
	 * @param str $content  - Page content (Defaults to empty string)
	 * @param arr $tags     - Array with template_field_id as key and array of tag_ids as value
	 * @return int page id
	 */
	abstract public function new_page($name, $URL, $tags = FALSE);

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
	 * Remove image from database
	 *
	 * @param str $name
	 * @return boolean
	 */
	abstract public function rm_image($name);

	/**
	 * Remove a page
	 *
	 * @param int $id - Page ID
	 * @return bool
	 */
	abstract public function rm_page($id);

	/**
	 * Remove a tag
	 *
	 * @param int $id - Tag ID
	 * @return bool
	 */
	abstract public function rm_tag($id);

	/**
	 * Update content
	 *
	 * @param int $content_id
	 * @param str $content  OPTIONAL
	 * @param arr $tags     OPTIONAL IMPORTANT! WILL REMOVE PREVIOUS TAGS AND TAG DATA!!!
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 * @return boolean
	 */
	abstract public function update_content($content_id, $content = FALSE, $tags = FALSE);

	/**
	 * Update image data
	 * WARNING! Removes all previous data!
	 *
	 * @param str $image_name
	 * @param arr $tags     OPTIONAL IMPORTANT! WILL REMOVE PREVIOUS TAGS AND TAG DATA!!!
	 *                 - Tag name as key, value or array of values as value
	 *                   set value as NULL if you want a simple tag
	 * @return boolean
	 */
	abstract public function update_image_data($image_name, $tags = FALSE);

	/**
	 * Update image name
	 *
	 * @param str $old_image_name
	 * @param str $new_image_name
	 * @return boolean
	 */
	abstract public function update_image_name($old_image_name, $new_image_name);

	/**
	 * Rename the physical files, should be called from the driver
	 *
	 * @param str $old_image_name
	 * @param str $new_image_name
	 * @return boolean
	 */
	protected function rename_image_files($old_image_name, $new_image_name)
	{
		rename(Kohana::config('user_content.dir').'/images/'.$old_image_name, Kohana::config('user_content.dir').'/images/'.$new_image_name);
		$cwd            = getcwd();
		chdir(Kohana::config('user_content.dir').'/images');
		$cached_images  = glob($old_image_name.'*');
		chdir($cwd);
		foreach ($cached_images as $cached_image) unlink(Kohana::$cache_dir.'/user_content/images/'.$cached_image);
		return TRUE;
	}

	/**
	 * Update page data
	 *
	 * @param int $id      - Page ID
	 * @param str $name    - Page name                                                          OPTIONAL
	 * @param str $URI     - Page URL                                                           OPTIONAL
	 * @param arr $tag_ids - Array with template_field_id as key and array of tag_ids as value  OPTIONAL
	 * @return boolean
	 */
	abstract public function update_page_data($id, $name = FALSE, $URI = FALSE, $tags = FALSE);

}
