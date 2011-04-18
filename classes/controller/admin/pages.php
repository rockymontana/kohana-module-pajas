<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Pages extends Admincontroller {

	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/pages';
		xml::to_XML(array('admin_page' => 'Pages'), $this->xml_meta);
	}

	public function action_index()
	{
		// List pages
		$this->xml_content_pages = $this->xml_content->appendChild($this->dom->createElement('pages'));
		foreach (Content_Page::get_pages() as $page)
		{
			// Create the page node and set the page data to it
			$page_node = $this->xml_content_pages->appendChild($this->dom->createElement('page'));
			unset($page['type_ids']); // This only clutters the XML
			xml::to_XML($page, $page_node, NULL, 'id');
		}
	}

	public function action_add_page()
	{
		$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('types'));
		xml::to_XML(Content_Type::get_types(), $this->xml_content_types, 'type', 'id');

		if (count($_POST) && isset($_POST['URI']) && isset($_POST['name']))
		{
			if ($_POST['URI'] == '') $_POST['URI'] = $_POST['name'];

			$_POST['URI'] = URL::title($_POST['URI'], '-', TRUE);

			$post = new Validation($_POST);
			$post->filter('trim');
			$post->rule('Valid::not_empty',                  'name');
			$post->rule('Content_Page::page_name_available', 'name');
			$post->rule('Content_Page::page_URI_available',  'URI');
			$post_values = $post->as_array();

			if ($post->validate())
			{
				$type_ids = array();
				foreach ($post_values as $key => $value)
				{
					if (substr($key, 0, 5) == 'type_') {
						$type_ids[$post_values['template_for_type_'.substr($key, 5)]] = (int) substr($key, 5);
					}
				}
				$page_id = Content_Page::new_page($post_values['name'], $post_values['URI'], $type_ids);
				$this->add_message('Page "'.$post_values['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post_values);
			}
		}
	}

	public function action_edit_page($id)
	{
		$content_page = new Content_Page($id);
		if ($content_page->get_page_id())
		{
			$this->xml_content_types = $this->xml_content->appendChild($this->dom->createElement('types'));
			xml::to_XML(Content_Type::get_types(), $this->xml_content_types, 'type', 'id');

			$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));

			if (count($_POST) && isset($_POST['URI']) && isset($_POST['name']))
			{
				if ($_POST['URI'] == '') $_POST['URI'] = $_POST['name'];
				$_POST['URI'] = URL::title($_POST['URI'], '-', TRUE);

				$post = new Validation($_POST);
				$post->filter('trim');
				$post->rule('Valid::not_empty', 'name');

				if ($post->validate())
				{
					$post_values       = $post->as_array();
					$current_page_data = $content_page->get_page_data();

					if ($post_values['name'] != $current_page_data['name'] && ! Content_Page::page_name_available($post_values['name']))
					{
						$post->add_error('name', 'Content_Page::page_name_available');
					}

					if ($post_values['URI'] != $current_page_data['URI'] && ! Content_Page::page_URI_available($post_values['URI']))
					{
						$post->add_error('URI', 'Content_Page::page_URI_available');
					}

				}

				// Retry
				if ($post->validate())
				{
					$type_ids = array();
					foreach ($post_values as $key => $value)
					{
						if (substr($key, 0, 5) == 'type_') {
							$type_ids[$post_values['template_for_type_'.substr($key, 5)]] = (int) substr($key, 5);
						}
					}
					$content_page->update_page_data($post_values['name'], $post_values['URI'], $type_ids);
					$this->add_message('Page "'.$post_values['name'].'" updated');

					$page_data = $content_page->get_page_data();
					foreach ($page_data['type_ids'] as $template_field_id => $type_id)
					{
						$page_data['type_'.$type_id]              = 'checked';
						$page_data['template_for_type_'.$type_id] = $template_field_id;
					}
					unset($page_data['type_ids']);
					$this->set_formdata($page_data);
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
				$page_data = $content_page->get_page_data();
				foreach ($page_data['type_ids'] as $template_field_id => $type_id)
				{
					$page_data['type_'.$type_id]              = 'checked';
					$page_data['template_for_type_'.$type_id] = $template_field_id;
				}
				unset($page_data['type_ids']);
				$this->set_formdata($page_data);
			}

			/**
			 * Put the page data to the XML
			 *
			 */
			$page_data = $content_page->get_page_data();
			// Load the type ids to a variable of their own
			$type_ids = $page_data['type_ids'];

			// And unset it from the page data array, or it will cludge our XML
			unset($page_data['type_ids']);

			// Set the page data (name and URI) to the page node
			xml::to_XML($page_data, $this->xml_content_page, NULL, 'id');

			// For each type id, make a type node and set the attribute id to the type id
			foreach ($type_ids as $template_field_id => $type_id)
			{
				$type_node = $this->xml_content_page->appendChild($this->dom->createElement('type'));
				$type_node->setAttribute('id', $type_id);
				$type_node->setAttribute('template_field_id', $template_field_id);
			}
		}
		else $this->redirect();
	}

	public function action_rm_page($id)
	{
		$content_page = new Content_Page($id);
		$content_page->rm_page();

		$this->redirect();
	}

}
