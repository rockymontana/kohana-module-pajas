<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Roles extends Admincontroller
{
	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/roles';
		xml::to_XML(array('admin_page' => 'roles'), $this->xml_meta);
	}

	public function action_index()
	{
		$role_uri  = array();

		foreach (User::get_roles_uris(FALSE) as $role => $uris)
		{
			$uri_field = "";
			foreach($uris as $uri)
			{
					$uri_field .= $uri.', ';
			}
			$role_uri[] = array('name' => $role,
			                    'uri' => $uri_field,
			);

		}
#echo "<pre>";
#print_r(User::get_roles_uris(TRUE));
#die();
		$this->xml_content_tags = $this->xml_content->appendChild($this->dom->createElement('roles'));
		xml::to_XML($role_uri, $this->xml_content_tags, 'role');
	}

	/**
	 * Edit Tags
	 * if id is set, instanciate an edit function
	 * if not instanciate an add tag function.
	 */
	public function action_role()
	{
		$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('roles'));
		xml::to_XML(Uvtag::get_tags(), $this->xml_content_types, 'role');

		if ( ! empty($_POST))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->rule('Valid::not_empty', 'role');
			$post->rule('Valid::not_empty', 'uri');

			if (isset($role))
			{
				$tag->update($post->as_array());
				$this->add_message('Role name updated');
			}
			else
			{
				if (Uvtag::add($post->get('role'),$post->get('uri') ))
				{
					$this->add_message('Role "'.$post->get('name').'" was added');
				}
				else
				{
					$this->add_message('Role "'.$post->get('name').'" could not be added');
				}
			}
		}
		elseif (isset($tag))
		{
			// Set the form input to the tag name.
			$this->set_formdata($tag->get());
		}
	}

	/**
	 * Remove Tag controller
	 * @return
	 */
	public function action_rm()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : die('No direct access allowed.');
		// Remove the tag from the tag-table and also the connector table (in the rm_category function)
		Uvtag::rm($id);
		$this->redirect('/admin/roles/');
	}
}
