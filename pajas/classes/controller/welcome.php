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
		$this->xslt_stylesheet = 'welcome';

		// You can put data into the XML, wich is accessible in the XSLT
		// template. In this case we add "h1" to the content-node:
		// <root>
		//	 <content>
		//		 <h1>Hello world</h1>
		//	 </content>
		// </root>
		// or spoken in XPath: /root/content/h1 = 'Hello world'
		xml::to_XML(array('h1' => 'Hello world'), $this->xml_content);

		// An array of links to display. The array can be recursive in infinit
		// number of levels.
		//
		// See http://kohana.lillem4n.se/2010/06/11/kohana-xml-helper-module/
		// for more information about how you handle data into the XML
		xml::to_XML(
			array(
				'links' => array(
					'0link' => array('Home Page',     '@url' => 'http://kohana.lillem4n.se/dahdah'),
					'1link' => array('Kohana',        '@url' => 'http://kohanaframework.org'),
					'2link' => array('Forum',         '@url' => 'http://forum.kohanaframework.org/'),
				)
			),
			$this->xml_content
		);
	}

}
