<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Content extends Admincontroller {

	public function before()
	{
		$this->xslt_stylesheet = 'admin/content';
		xml::to_XML(array('admin_page' => 'Content'), $this->xml_meta);
	}

	public function action_index()
	{
		$this->xml_content_contents = $this->xml_content->appendChild($this->dom->createElement('contents'));
		foreach (Content_Content::get_contents() as $content)
		{
			$content_node = $this->xml_content_contents->appendChild($this->dom->createElement('content'));
			$content_node->setAttribute('id', $content['id']);
			$content_node_content = $this->dom->createElement('content', $content['content']);
			$content_node->appendChild($content_node_content);
			$tags_node = $content_node->appendChild($this->dom->createElement('tags'));
			foreach ($content['tags'] as $tag)
			{
				$tag_node = $tags_node->appendChild($this->dom->createElement('tag'));
				$tag_node->setAttribute('id', $tag['id']);
				$tag_node->appendChild($this->dom->createElement('name', $tag['name']));
				if ($tag['value']) $tag_node->appendChild($this->dom->createElement('value', $tag['value']));
			}
		}
	}

	public function action_add_content()
	{
		if (count($_POST))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post_values = $post->as_array();

			$tags = array();
			foreach ($post_values['tag'] as $nr => $tag_name)
			{
				if ($tag_name)
				{
					if ( ! isset($tags[$tag_name])) $tags[$tag_name] = array();
					$tags[$tag_name][] = $post_values['tag_value'][$nr];
				}
			}

			$content_id = Content_Content::new_content($post_values['content'], $tags);
			$this->add_message('Content #'.$content_id.' added');
		}
	}

	public function action_edit_content($id)
	{
		$content = new Content_Content($id);

		if ($content->get_content_id())
		{
			$this->xml_content->appendChild($this->dom->createElement('content_id', $id));

			if (count($_POST))
			{
				$post = new Validation($_POST);
				$post->filter('trim');
				$post_values = $post->as_array();

				$tags = array();
				foreach ($post_values['tag'] as $nr => $tag_name)
				{
					if ($tag_name)
					{
						if ( ! isset($tags[$tag_name])) $tags[$tag_name] = array();
						$tags[$tag_name][] = $post_values['tag_value'][$nr];
					}
				}

				$content->update_content($post_values['content'], $tags);
				$this->add_message('Content #'.$id.' updated');
			}

			$this->xml_content->appendChild($this->dom->createElement('content', $content->get_content()));
			$tags_node = $this->xml_content->appendChild($this->dom->createElement('tags'));

			foreach ($content->get_tags() as $tag_name => $tag_values)
			{
				foreach ($tag_values as $tag_value)
				{
					$tag_node = $tags_node->appendChild($this->dom->createElement('tag', $tag_value));
					$tag_node->setAttribute('name', $tag_name);
				}
			}

		}
		else $this->redirect();
	}

	public function action_rm_content($id)
	{
		$content = new Content_Content($id);
		$content->rm_content();

		$this->redirect();
	}

}
