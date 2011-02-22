<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Content extends Admincontroller {

	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/content';
		xml::to_XML(array('admin_page' => 'Content'), $this->xml_meta);

		// List content types
		$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('types'));
		xml::to_XML(Content_Type::get_types(), $this->xml_content_types, 'type', 'id');
	}

	public function action_index()
	{
		$this->xml_content_contents = $this->xml_content->appendChild($this->dom->createElement('contents'));
		foreach (Content_Content::get_contents() as $content)
		{
			$content_node = $this->xml_content_contents->appendChild($this->dom->createElement('content'));
			$content_node->setAttribute('id', $content['id']);
			$content_node->appendChild($this->dom->createElement('content', $content['content']));
			$types_node   = $content_node->appendChild($this->dom->createElement('types'));
			foreach ($content['types'] as $type)
			{
				$type_node = $types_node->appendChild($this->dom->createElement('type', $type['type']));
				$type_node->setAttribute('id', $type['id']);

			}
		}
	}

	public function action_add_content()
	{
		if (count($_POST) && isset($_POST['content']))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post_values = $post->as_array();

			$type_ids = array();
			foreach ($post_values as $key => $value)
			{
				if (substr($key, 0, 8) == 'type_id_')
				{
					$type_ids[] = (int) substr($key, 8);
				}
			}
			$content_id = Content_Content::new_content($post_values['content'], $type_ids);
			$this->add_message('Content #'.$content_id.' added');
		}
	}

	public function action_edit_content($id)
	{
		$content_content = new Content_Content($id);

		if ($content_content->get_content_id())
		{
			$this->xml_content->appendChild($this->dom->createElement('content_id', $id));

			if (count($_POST) && isset($_POST['content']))
			{
				$post = new Validation($_POST);
				$post->filter('trim');
				$post_values = $post->as_array();

				$type_ids = array();
				foreach ($post_values as $key => $value)
				{
					if (substr($key, 0, 8) == 'type_id_')
					{
						$type_ids[] = (int) substr($key, 8);
					}
				}

				$content_content->update_content($post_values['content'], $type_ids);
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
