<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Welcome extends Xsltcontroller {

	public function __construct()
	{
		// This is needed for the XSLT setup
		parent::__construct();
	}

	public function action_index()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'staticpage';

		// Check if we have a CMS page
		if ($page_id = Page::get_page_id_by_uri('welcome'))
		{
			// Initiate the page model
			$page_model = new Page($page_id);

			// Create the DOM node <page>
			$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));
			// and load the page data into it
			xml::to_XML($page_model->get_page_data(), $this->xml_content_page);

			// The HTML content needs some special care

			// Create the node for the html content inside the page-node
			$this->xml_content_page_htmlcontent = $this->xml_content_page->appendChild($this->dom->createElement('html_content'));

			// Now we put the XML-string into that new node we just created
			xml::xml_to_DOM_node(
				// The function to create the html is configurable
				call_user_func(Kohana::config('page.content_transformator'), $page_model->get_page_data('content')),
				$this->xml_content_page_htmlcontent
			);
		}
		else
		{
			// No CMS page, show a "hello world" and some help

			// You can put data into the XML, wich is accessible in the XSLT
			// template. In this case we add "h1" to the content-node:
			// <root>
			//	 <content>
			//		 <h1>Hello world</h1>
			//	 </content>
			// </root>
			// or spoken in XPath: /root/content/h1 = 'Hello world'
			// You can send big, recursive arrays to the XML with this function
			xml::to_XML(array('h1' => 'Hello world'), $this->xml_content);

			// Create the DOM node <page>
			$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));
			// Create the node for the html content inside the page-node
			$this->xml_content_page_htmlcontent = $this->xml_content_page->appendChild($this->dom->createElement('html_content'));
			xml::to_XML(
				array(
					'p' => array(
						'Create a page with the URI "welcome" in the ',
						array('a href="admin"' => 'CMS'),
						' to replace this page',
					)
				),
				$this->xml_content_page_htmlcontent
			);
		}

	}

}
