<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Generic extends Xsltcontroller
{

	public function action_index($URI = 'welcome')
	{
		// Empty string defaults to 'welcome'
		if ($URI == '') $URI = 'welcome';

		// Set the name of the template to use
		$this->xslt_stylesheet = 'generic';

		// Initiate the page model
		$content_page = new Content_Page(Content_Page::get_page_id_by_uri($URI));

		// Create the DOM node <page>
		$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));

		// And load the page data into it
		$page_data = $content_page->get_page_data();
		foreach ($page_data['tag_ids'] as $template_field_id => $tag_ids)
		{

			// Get contents
			$contents = array();
			foreach ($tag_ids as $tag_id)
			{
				foreach (Content_Content::get_contents_by_tag_id($tag_id) as $content)
				{
					if ( ! isset($contents[$content['id'].'content']))
					{
						$contents[$content['id'].'content'] = array(
							'@id'  => $content['id'],
							'raw'  => $content['content'],
							'tags' => array(),
						);

						$counter = 0;
						foreach ($content['tags'] as $tag_name => $tag_values)
						{
							foreach ($tag_values as $tag_value)
							{
								$counter++;
								$contents[$content['id'].'content']['tags'][$counter.'tag']['@name'] = $tag_name;
								if ($tag_value)
								{
									$contents[$content['id'].'content']['tags'][$counter.'tag']['$value'] = $tag_value;
								}
							}
						}
					}
				}
			}

			// Get images
			$images     = array();
			$image_tags = array();
			foreach ($tag_ids as $tag_id) $image_tags[Tags::get_name_by_id($tag_id)] = TRUE;

			$larger_counter = 0;
			foreach (Content_Image::get_images(NULL, $image_tags) as $image_name => $image_tags)
			{
				$image = array(
					'@name' => $image_name,
					'tags'  => array(),
				);

				$counter = 0;
				foreach ($image_tags as $tag_name => $tag_values)
				{
					if (count($tag_values))
					{
						foreach ($tag_values as $tag_value)
						{
							$image['tags'][$counter.'tag'] = array(
								'@name' => $tag_name,
								'$value' => $tag_value,
							);
							$counter++;
						}
					}
					else
					{
						$image['tags'][$counter.'tag'] = array(
							'@name' => $tag_name
						);
						$counter++;
					}
				}

				$images[$larger_counter.'image'] = $image;
				$larger_counter++;
			}

			// Put it all in the $page_data for transport to the XML
			$page_data[$template_field_id.'template_field'] = array(
				'@id'      => $template_field_id,
				'contents' => $contents,
				'images'   => $images,
			);

		}
		unset($page_data['tag_ids']);

		xml::to_XML($page_data, $this->xml_content_page, NULL, array('id', 'template_field_id'));

		// We need to put some HTML in from our transformator
		// The reason for all this mess is that we must inject this directly in to the DOM, or else the <> will get destroyed
		$XPath = new DOMXpath($this->dom);
		foreach ($XPath->query('/root/content/page/template_field/contents/content/raw') as $raw_content_node)
		{
			$html_content = call_user_func(Kohana::config('content.content_transformator'), $raw_content_node->nodeValue);
			$html_node    = $raw_content_node->parentNode->appendChild($this->dom->createElement('html'));
			xml::xml_to_DOM_node($html_content, $html_node);
		}
	}

	public function action_singlecontent($id)
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'generic';


	}

}
