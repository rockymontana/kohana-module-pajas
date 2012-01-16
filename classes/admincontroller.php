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
	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);

		if (class_exists('User'))
		{
			/**
			 * Must be a logged in user with admin role to access the admin pages
			 */
			$user = User::instance();
			if ($user->logged_in())
			{
				$user_data = array(
					'@id'      => $user->get_user_id(),
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

		if ($this->request->controller() != 'login')
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
		  foreach (Kohana::$config->load('admin_menu_options') as $menu_option)
		  {
			if (in_array('admin', $user->get_role()) ||
				in_array($menu_option['href'],$user->get_roles_uri()))
				{
					xml::to_XML(
						array($menu_option),                 // Array to make XML from
						$this->menuoptions_node,             // Container node
						'menuoption'                         // Put each group in a node with this name
					);
				}
		  }

		}
		if (!in_array($this->request->controller(), $user->get_roles_uri()))
		{
		throw new HTTP_Exception_403('403 Forbidden Controller: :controller', array(':controller' => $this->request->controller()));
		}
	}

}
