<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Pages extends Admincontroller {

	public function before()
	{
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
			unset($page['tag_ids']); // This only clutters the XML
			xml::to_XML($page, $page_node, NULL, 'id');
		}
	}

	public function action_add_page()
	{
		// Get all tags associated with pages
		$this->xml_content_tags = $this->xml_content->appendChild($this->dom->createElement('tags'));
		foreach (Content_Page::get_tags() as $tag)
		{
			$tag_node = $this->xml_content_tags->appendChild($this->dom->createElement('tag', $tag['name']));
			$tag_node->setAttribute('id', $tag['id']);
		}

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
				$tags = array();
				foreach ($post_values['template_position'] as $nr => $template_position)
				{
					if ($post_values['tag_id'][$nr] > 0)
					{
						if ( ! isset($tags[$template_position])) $tags[$template_position] = array();
						$tags[$template_position][] = $post_values['tag_id'][$nr];
					}
				}

				$page_id = Content_Page::new_page($post_values['name'], $post_values['URI'], $tags);
				$this->add_message('Page "'.$post_values['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());

				// Fix template position data
				$tmp_node = $this->xml_content->appendChild($this->dom->createElement('tmp'));
				foreach ($post_values['template_position'] as $nr => $template_position)
				{
					$template_field_node = $tmp_node->appendChild($this->dom->createElement('template_field'));
					$template_field_node->setAttribute('id', $template_position);
					if ($post_values['tag_id'][$nr] > 0)
					{
						$tag_node = $template_field_node->appendChild($this->dom->createElement('tag'));
						$tag_node->setAttribute('id', $post_values['tag_id'][$nr]);
					}
				}

				unset($post_values['template_position'], $post_values['tag_id']);
				$this->set_formdata($post_values);
			}
		}
	}

	public function action_edit_page($id)
	{
		$content_page = new Content_Page($id);
		if ($content_page->get_page_id())
		{
			$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));

			// Get all tags associated with pages
			$this->xml_content_tags = $this->xml_content->appendChild($this->dom->createElement('tags'));
			foreach (Content_Page::get_tags() as $tag)
			{
				$tag_node = $this->xml_content_tags->appendChild($this->dom->createElement('tag', $tag['name']));
				$tag_node->setAttribute('id', $tag['id']);
			}

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
					$tags = array();
					foreach ($post_values['template_position'] as $nr => $template_position)
					{
						if ($post_values['tag_id'][$nr] > 0)
						{
							if ( ! isset($tags[$template_position])) $tags[$template_position] = array();
							$tags[$template_position][] = $post_values['tag_id'][$nr];
						}
					}

					$content_page->update_page_data($post_values['name'], $post_values['URI'], $tags);
					$this->add_message('Page "'.$post_values['name'].'" updated');

					$page_data = $content_page->get_page_data();
					unset($page_data['tag_ids']);
					$this->set_formdata($page_data);
				}
				else
				{
					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());

					// Fix template position data
					$tmp_node = $this->xml_content->appendChild($this->dom->createElement('tmp'));
					foreach ($post_values['template_position'] as $nr => $template_position)
					{
						$template_field_node = $tmp_node->appendChild($this->dom->createElement('template_field'));
						$template_field_node->setAttribute('id', $template_position);
						if ($post_values['tag_id'][$nr] > 0)
						{
							$tag_node = $template_field_node->appendChild($this->dom->createElement('tag'));
							$tag_node->setAttribute('id', $post_values['tag_id'][$nr]);
						}
					}

					unset($post_values['template_position'], $post_values['tag_id']);
					$this->set_formdata($post_values);
				}
			}
			else
			{
				$page_data = $content_page->get_page_data();
				unset($page_data['tag_ids']);
				$this->set_formdata($page_data);
			}

			/**
			 * Put the page data to the XML
			 *
			 */
			$page_data                    = $content_page->get_page_data();
			$page_data['template_fields'] = array();
			foreach ($page_data['tag_ids'] as $template_field_id => $tag_ids)
			{
				$page_data['template_fields'][$template_field_id.'template_field'] = array(
					'@id' => $template_field_id,
				);

				foreach ($tag_ids as $tag_id)
				{
					$page_data['template_fields'][$template_field_id.'template_field'][$tag_id.'tag'] = array('@id' => $tag_id);
				}
			}

			// Unset this, or it will cludge our XML
			unset($page_data['tag_ids']);

			// Set the page data to the page node
			xml::to_XML($page_data, $this->xml_content_page, NULL, 'id');

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
