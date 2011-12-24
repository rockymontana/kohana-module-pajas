<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Users extends Admincontroller {

	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/users';
		xml::to_XML(array('admin_page' => 'Users'), $this->xml_meta);
	}

	public function action_index()
	{
		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));

		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		xml::to_XML($fields, $this->xml_content_users);
		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));
		xml::to_XML(User::get_users(FALSE, 0, 100, array('lastname'=>'DESC','firstname'=>'ASC')), $this->xml_content_users, 'user', 'id');
	}

	private function list_available_data_fields()
	{
		$fields = array();
		foreach (User::get_data_fields() as $field_id => $field_name)
		{
			$fields['field id="'.$field_id.'"'] = $field_name;
		}

		$this->xml_content_users = $this->xml_content->appendChild($this->dom->createElement('users'));
		xml::to_XML($fields, $this->xml_content_users);
	}

	public function action_add_user()
	{
		Session::instance(); // Make sure sessions is turned on

		$this->list_available_data_fields();

		// The form is executed! Do something!
		if (count($_POST) && isset($_POST['username']) && isset($_POST['password']))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->filter('strtolower', 'username');
			$post->rule('Valid::not_empty',         'username');
			$post->rule('User::username_available', 'username');
			$post->rule('Valid::not_empty',         'password');

			if (isset($_POST['do_add_field']))
			{
				// Add another user data field and save no data, but repopulate the form fields
				if (!isset($_SESSION['add_user_detail_fields']))
				{
					$_SESSION['add_user_detail_fields'] = array();
				}

				$_SESSION['add_user_detail_fields'][] = $_POST['add_field'];

				// Reconstruct the form data to repopulate the form
				$formdata    = array();
				$counter     = 0;
				$post_values = $post->as_array();
				foreach ($post_values as $field => $data)
				{
					if (substr($field, 0, 8) == 'fieldid_')
					{
						foreach ($data as $data_piece)
						{
							$counter++;
							$formdata['field_'.substr($field, 8).'_'.$counter] = trim($data_piece);
						}
					}
					elseif ($field == 'username') $formdata[$field] = $post_values[$field];
				}

				$this->set_formdata($formdata);
			}
			else
			{
				// Check for form errors
				if ($post->validate())
				{
					// No form errors, add the user!

					$post_values = $post->as_array();

					// Erase the empty data fields
					foreach ($post_values as $key => $value)
					{
						if (substr($key, 0, 8) == 'fieldid_' && is_array($value))
						{
							foreach ($value as $nr => $value_piece)
							{
								if ($value_piece == '') unset($post_values[$key][$nr]);
							}
						}
					}

					// Organize the field data and set the session fields
					$fields = $_SESSION['add_user_detail_fields'] = array();
					foreach ($post_values as $key => $value)
					{
						if (substr($key, 0, 8) == 'fieldid_' && is_array($value))
						{
							$fields[User::get_data_field_name(substr($key, 8))] = $value;
							foreach ($value as $foo) $_SESSION['add_user_detail_fields'][] = substr($key, 8);
						}
					}

					// Actually add the user
					User::new_user(
						$post_values['username'],
						$post_values['password'],
						$fields
					);
					$this->add_message('User '.$post_values['username'].' added');
				}
				else
				{
					// Form errors detected!

					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
					$formdata    = array();
					$counter     = 0;
					$post_values = $post->as_array();
					foreach ($post_values as $field => $data)
					{
						if (substr($field, 0, 8) == 'fieldid_')
						{
							foreach ($data as $data_piece)
							{
								$counter++;
								$formdata['field_'.substr($field, 8).'_'.$counter] = trim($data_piece);
							}
						}
						elseif ($field == 'username') $formdata[$field] = $post_values[$field];
					}

					$this->set_formdata($formdata);
				}
			}
		}

		// Define wich extra user detail fields should be listed
		if (isset($_SESSION['add_user_detail_fields']))
		{
			$detail_fields = array();
			foreach ($_SESSION['add_user_detail_fields'] as $nr => $field)
			{
				$detail_fields[$nr . 'custom_detail_field'] = $field;
			}
			xml::to_XML($detail_fields, $this->xml_content_users);
		}
	}

	public function action_edit_user()
	{
		$user_id = $this->request->param('options');

		$this->list_available_data_fields();

		$this->xml_content_user = $this->xml_content->appendChild($this->dom->createElement('user'));

		$user = new User($user_id, FALSE, FALSE, 'default', FALSE);
		if (!$user->logged_in())
		{
			// Invalid user, sending the user back to where they came from
			$this->redirect();
		}

		// The form is executed! Do something!
		if (count($_POST))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->filter('strtolower', 'username');
			$post->rule('Valid::not_empty', 'username');
			$post_values = $post->as_array();

			if (isset($_POST['do_add_field']))
			{
				// Add another user data field and save no data, but repopulate the form fields

				if (!isset($_SESSION['edit_user_detail_fields']))
				{
					$_SESSION['edit_user_detail_fields'] = array();
				}

				$_SESSION['edit_user_detail_fields'][] = $_POST['add_field'];
			}
			else
			{

				if ($post->validate())
				{
					if ($post_values['username'] != $user->get_username() && ! User::username_available($post_values['username']))
					{
						$post->add_error('username', 'User::username_available');
					}
				}

				if ($post->validate())
				{
					// No errors, save!

					$fields = array('username' => $post_values['username']);

					if (isset($post_values['password']) && $post_values['password'] != '')
					{
						$fields['password'] = $post_values['password'];
					}

					// Format the field data so it fits the set_user_data()
					foreach ($post_values as $field => $data)
					{
						if (substr($field, 0, 8) == 'fieldid_')
						{
							$fields[User::get_data_field_name(substr($field, 8))] = array();

							foreach ($data as $nr => $data_piece)
							{
								if (trim($data_piece) == '')
								{
									// Unset all emtpy fields
									unset($_SESSION['edit_user_detail_fields'][array_search(substr($field, 8), $_SESSION['edit_user_detail_fields'])]);
									unset($post_values[$field][$nr]);
								}
								else
								{
									$fields[User::get_data_field_name(substr($field, 8))][] = trim($data_piece);
								}
							}

						}
					}

					$user->set_user_data($fields); // This saves to database

					$this->add_message('User data saved');

					$_SESSION['edit_user_detail_fields'] = array(); // Empty the special fields array
				}
				else
				{
					// Errors detected!

					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());

					// Reconstruct the form data to repopulate the form
					$formdata = array();
					$counter  = 0;
					foreach ($post_values as $field => $data)
					{
						if (substr($field, 0, 8) == 'fieldid_')
						{
							foreach ($data as $data_piece)
							{
								$counter++;
								$formdata['field_'.substr($field, 8).'_'.$counter] = trim($data_piece);
							}
						}
						elseif ($field == 'username')
						{
							$formdata[$field] = $post[$field];
						}
					}

					$this->set_formdata($formdata);
				}

			}
		}

		if (!count($_POST) || (count($_POST) && !count($post->errors())) || isset($_POST['do_add_field']))
		{
			// No form is submitted or just a field is added, so we need to fetch the user data from database

			$formdata = array(
				'username' => $user->get_username(),
			);
			$counter = 0;
			foreach ($user->get_user_data() as $field => $data)
			{
				foreach ($data as $data_piece)
				{
					$counter++;
					$formdata['field_'.User::get_data_field_id($field).'_'.$counter] = $data_piece;
				}
			}
			$this->set_formdata($formdata);
		}

		// Output user data to the XML
		xml::to_XML(array('id' => $user_id, 'username' => $user->get_username()), $this->xml_content_user, NULL, 'id');
		foreach ($user->get_user_data() as $field_name => $values)
		{
			foreach ($values as $value)
			{
				xml::to_XML(
					array(
						'field' => array(
							'@id'      => User::get_data_field_id($field_name),
							'$content' => $value,
						),
					),
					$this->xml_content_user
				);
			}
		}

		// Define wich extra user detail fields should be listed
		if (isset($_SESSION['edit_user_detail_fields']))
		{
			$detail_fields = array();
			foreach ($_SESSION['edit_user_detail_fields'] as $nr => $field)
			{
				$detail_fields[$nr . 'custom_detail_field'] = $field;
			}
			xml::to_XML($detail_fields, $this->xml_content_users);
		}

	}

	public function action_rm_user()
	{
		$user_id = $this->request->param('options');
		$user    = new User($user_id, FALSE, FALSE, 'default', FALSE);

		$user->rm_user();

		$this->redirect();
	}

}
