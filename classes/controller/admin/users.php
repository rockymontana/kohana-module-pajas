<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Users extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
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
		xml::to_XML(User::get_users(FALSE, FALSE, FALSE, 'user_id'), $this->xml_content_users, 'user');
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
		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->filter('username', 'strtolower');
			$post->rule('username', 'not_empty');
			$post->rule('username', 'User::username_available');
			$post->rule('password', 'not_empty');

			if (isset($_POST['do_add_field']))
			{
				// Add another user data field and save no data, but repopulate the form fields
				if (!isset($_SESSION['add_user_detail_fields']))
				{
					$_SESSION['add_user_detail_fields'] = array();
				}

				$_SESSION['add_user_detail_fields'][] = $_POST['add_field'];
			}
			else
			{
				// Check for form errors
				if ($post->check())
				{
					// No form errors, add the user!

					// Erase the empty data fields
					foreach ($_POST as $key => $value)
					{
						if (substr($key, 0, 8) == 'fieldid_' && is_array($value))
						{
							foreach ($value as $nr => $value_piece)
							{
								if ($value_piece == '') unset($_POST[$key][$nr]);
							}
						}
					}

					// Organize the field data and set the session fields
					$fields = array();
					foreach ($_POST as $key => $value)
					{
						if (substr($key, 0, 8) == 'fieldid_' && is_array($value))
						{
							$fields[User::get_data_field_name(substr($key, 8))] = $value;
						}
					}

					// Actually add the user
					User::new_user(
						$post['username'],
						$post['password'],
						$fields
					);
					$this->add_message('User '.$post['username'].' added');
					$_POST = array(); // Empty the POST array to stop repopulating the form
				}
				else
				{
					// Form errors detected!

					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
				}
			}

			// Reconstruct the form data to repopulate the form
			$formdata = array();
			$counter  = 0;
			foreach ($_POST as $field => $data)
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

	public function action_edit_user($user_id)
	{
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
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->filter('username', 'strtolower');
			$post->rule('username', 'not_empty');
			$post->label('password', 'Password');

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

				if ($post->check())
				{
					if ($post['username'] != $user->get_username() && !User::username_available($post['username']))
					{
						$post->error('username', 'username_available');
					}
				}

				// Try to save the form data

				if (!count($post->errors()))
				{
					// No errors, save!

					$fields = array('username' => $post['username']);

					if (isset($post['password']) && $post['password'] != '')
					{
						$fields['password'] = $post['password'];
					}

					// Format the field data so it fits the set_user_data()
					foreach ($_POST as $field => $data)
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
									unset($_POST[$field][$nr]);
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
					foreach ($_POST as $field => $data)
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
		xml::to_XML(array('user_id' => $user_id, 'username' => $user->get_username()), $this->xml_content_user);
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

	public function action_rm_user($user_id)
	{
		$user = new User($user_id, FALSE, FALSE, 'default', FALSE);

		$user->rm_user();

		$this->redirect();
	}

}
