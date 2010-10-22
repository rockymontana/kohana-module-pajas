<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Kohana Admin Controller class. The Admin controller class must
 * be extended to work properly, so this class is defined as abstract.
 *
 * Downloaded from http://kohana.lillem4n.se
 */
abstract class Admincontroller extends Xsltcontroller
{

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		if (class_exists('User'))
		{
			/**
			 * Must be a logged in user with admin role to access the admin pages
			 */
			$user = User::instance();
			if ((!$user->logged_in() || !$user->get_user_data('role') == 'admin') && request::instance()->controller != 'login')
			{
				$this->redirect('/admin/login');
			}
			elseif ($user->logged_in())
			{
				$user_data = array(
					'user_id'  => $user->get_user_id(),
					'username' => $user->get_username(),
					'data'     => array(),
				);
				foreach ($user->get_user_data() as $field_name => $field_value)
				{
					$user_data['data']['field name="' . $field_name . '"'] = $field_value;
				}
				xml::to_XML(array('user_data' => $user_data), $this->xml_meta);
			}
		}

		if (request::instance()->controller != 'login')
		{
			/**
			 * Build the menu alternatives
			 */

			// First we need to create the container for the options
			$this->menuoptions_node = $this->xml_content->appendChild($this->dom->createElement('menuoptions'));

			// First add the default home-alternative
			xml::to_XML(
				array(array( // Just simulating the config reading, thats why it looks odd :p
		      'name'        => 'Home',
		      '@category'   => '',
		      'description' => 'Admin home page with descriptions of the available admin pages',
		  		'href'        => '',
		  		'position'    => 0,
		    )),
		    $this->menuoptions_node,
		    'menuoption'
		  );

			// Then we populate this container with options from the config files, and group them by 'menuoption'
		  foreach (Kohana::config('admin_menu_options') as $menu_option)
		  {
				xml::to_XML(
					array($menu_option),                 // Array to make XML from
					$this->menuoptions_node,             // Container node
					'menuoption'                         // Put each group in a node with this name
				);
		  }
		}
	}

	/**
	 * Add a simple error message
	 *
	 * @param str $error
	 * @return boolean
	 */
	public function add_error($error)
	{
		if (!isset($this->xml_content_errors))
		{
			$this->xml_content_errors = $this->xml_content->appendChild($this->dom->createElement('errors'));
		}

		xml::to_XML(array('error' => $error), $this->xml_content_errors);
		return TRUE;
	}

	/**
	 * Add form errors
	 *
	 * @param arr $errors - as from Validate::errors()
	 * @return boolean
	 */
	public function add_form_errors($errors)
	{
		if (!isset($this->xml_content_errors))
		{
			$this->xml_content_errors = $this->xml_content->appendChild($this->dom->createElement('errors'));
		}

		xml::to_XML(array('form_errors' => $errors), $this->xml_content_errors);
		return TRUE;
	}

	/**
	 * Add simple message
	 *
	 * @param str $message
	 * @return boolean
	 */
	public function add_message($message)
	{
		if (!isset($this->xml_content_messages))
		{
			$this->xml_content_messages = $this->xml_content->appendChild($this->dom->createElement('messages'));
		}

		xml::to_XML(array('message' => $message), $this->xml_content_messages);
		return TRUE;
	}

	/**
	 * Set form data - the data that should fill out forms
	 *
	 * @param arr - form data
	 * @return boolean
	 */
	public function set_formdata($formdata)
	{
		if (!isset($this->xml_content_formdata))
		{
			$this->xml_content_formdata = $this->xml_content->appendChild($this->dom->createElement('formdata'));
		}

		$formatted_formdata = array();
		foreach ($formdata as $field => $data)
		{
			$formatted_formdata[] = array(
				'@name' => $field,
				'$content' => $data,
			);
		}

		xml::to_XML($formatted_formdata, $this->xml_content_formdata, 'field');
		return TRUE;
	}

}
