<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Staticpage extends Xsltcontroller {

	public function __construct()
	{
		// This is needed for the XSLT setup
		parent::__construct();
	}

	public function action_index($uri)
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'staticpage';

		// Initiate the page model
		$page_model = new Page(Page::get_page_id_by_uri($uri));

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

}
