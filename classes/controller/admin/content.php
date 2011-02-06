<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Content extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/content';
		xml::to_XML(array('admin_page' => 'Content'), $this->xml_meta);

		// List content types
		$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('types'));
		xml::to_XML(Content_Type::get_types(), $this->xml_content_types, 'type', 'id');
	}

	public function action_index()
	{
		if (isset($_GET['content_type']))
		{
			$this->xml_content_contents = $this->xml_content->appendChild($this->dom->createElement('contents'));
			xml::to_XML(Content_Content::get_contents_by_type($_GET['content_type']), $this->xml_content_contents, 'content', 'id');
		}
	}

	public function action_add_content()
	{
		if (count($_POST))
		{
			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->label('content', 'content');

			foreach ($_POST as $key => $value)
			{
				if (substr($key, 0, 8) == 'type_id_')
				{
					$post->label($key, $key);
				}
			}

			if ($post->check())
			{
				$type_ids = array();
				foreach ($post as $key => $value)
				{
					if (substr($key, 0, 8) == 'type_id_')
					{
						$type_ids[] = (int) substr($key, 8);
					}
				}
				$content_id = Content_Content::new_content($post['content'], $type_ids);
				$this->add_message('Content #'.$content_id.' added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post);
			}
		}
	}

	public function action_edit_content($id)
	{
		$content_content = new Content_Content($id);

		if ($content_content->get_content_id())
		{
			$this->xml_content->appendChild($this->dom->createElement('content_id', $id));

			if (count($_POST))
			{
				$post = new Validate($_POST);
				$post->filter(TRUE, 'trim');
				$post->label('content', 'content');

				foreach ($_POST as $key => $value)
				{
					if (substr($key, 0, 8) == 'type_id_')
					{
						$post->label($key, $key);
					}
				}

				if ($post->check())
				{

					$type_ids = array();
					foreach ($post as $key => $value)
					{
						if (substr($key, 0, 8) == 'type_id_')
						{
							$type_ids[] = (int) substr($key, 8);
						}
					}

					$content_content->update_content($post['content'], $type_ids);
					$this->add_message('Content #'.$id.' updated');

					$form_data = array('content' => $content_content->get_content());

					foreach ($content_content->get_type_ids() as $type_id)
					{
						$form_data['type_id_'.$type_id] = 'checked';
					}

					$this->set_formdata($form_data);
				}
				else
				{
					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
					$this->set_formdata($post);
				}

			}
			else
			{
				$form_data = array('content' => $content_content->get_content());

				foreach ($content_content->get_type_ids() as $type_id)
				{
					$form_data['type_id_'.$type_id] = 'checked';
				}

				$this->set_formdata($form_data);
			}

		}
		else $this->redirect();
	}

	public function action_rm_content($id)
	{
		$content_content = new Content_Content($id);
		$content_content->rm_content();

		$this->redirect();
	}

}
