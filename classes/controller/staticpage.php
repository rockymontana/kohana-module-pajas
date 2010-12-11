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

		$page_model = new Page(Page::get_page_id_by_uri($uri));
		$this->xml_content_page = $this->xml_content->appendChild($this->dom->createElement('page'));
		xml::to_XML($page_model->get_page_data(), $this->xml_content_page);
	}

}
