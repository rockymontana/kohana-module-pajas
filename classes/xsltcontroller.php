<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * Downloaded from http://kohana.lillem4n.se
 */
abstract class Xsltcontroller extends Controller
{

	/**
	 * If set to TRUE, render() will automaticly be ran
	 * when the controller is done.
	 */
	public $auto_render = TRUE;

	/**
	 * Decides where the transformation of XSLT->HTML
	 * should be done
	 * ATTENTION! This setting is configurable in xslt.php
	 *
	 * options:
	 * 'auto' = Normally sends XML+XSLT, but sometimes HTML,
	 *          depending on the HTTP_USER_AGENT
	 * TRUE   = Always send HTML
	 * FALSE  = Always send XML+XSLT
	 * XML    = Send only XML without XSLT processing instruction
	 * JSON   = Send JSON
	 *
	 */
	public $transform;

	/**
	 * Where to look for the XSLT stylesheets
	 */
	public $xslt_path;

	/**
	 * The filename of the XSLT stylesheet, excluding .xsl
	 */
	public $xslt_stylesheet = 'generic';


	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   Request   $request  Request that created the controller
	 * @param   Response  $response The request's response
	 * @return  void
	 */
	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);

		// Set transformation
		if (isset($_GET['transform']))
		{
			if     (strtolower($_GET['transform']) == 'true')  $this->transform = TRUE;
			elseif (strtolower($_GET['transform']) == 'false') $this->transform = FALSE;
			elseif (strtolower($_GET['transform']) == 'xml')   $this->transform = 'XML';
			elseif (strtolower($_GET['transform']) == 'json')  $this->transform = 'JSON';
			else                                               $this->transform = 'auto';
		}
		else
		{
			$this->transform = Kohana::$config->load('xslt.transform');
		}

		// Set XSLT path
		$this->xslt_path = Kohana::$base_url.'xsl/';

		// Create the XML DOM
		$this->dom = new DomDocument('1.0', 'UTF-8');
		$this->dom->formatOutput = TRUE;

		// Create the XML root
		$this->xml = $this->dom->appendChild($this->dom->createElement('root'));

		// Create the meta node
		$this->xml_meta = $this->xml->appendChild($this->dom->createElement('meta'));

		xml::to_XML(
			array(
				'protocol'      => (isset($_SERVER['HTTPS'])) ? 'https' : 'http',
				'domain'        => $_SERVER['SERVER_NAME'],
				'base'					=> URL::base(),
				'path'          => $this->request->uri(),
				'action'        => $this->request->action(),
				'controller'    => $this->request->controller(),
				'url_params'    => $_GET,
			),
			$this->xml_meta
		);

		// Create the content node
		$this->xml_content = $this->xml->appendChild($this->dom->createElement('content'));

		return TRUE;
	}

	public function before() {}

	/**
	 * Render the page - this is ran automaticly
	 *
	 * @return Boolean
	 */
	public function render()
	{
		if ($this->transform === TRUE || $this->transform === FALSE || $this->transform == 'auto')
		{
			$this->dom->insertBefore($this->dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $this->xslt_path . $this->xslt_stylesheet . '.xsl"'), $this->xml);

			// If the stylesheet name includes an additional path, we need to extract it
			$extra_xslt_path = '';
			$extra_path_parts = explode('/', $this->xslt_stylesheet);
			foreach ($extra_path_parts as $nr => $extra_path_part)
			{
				if ($nr < (count($extra_path_parts) - 1)) $extra_xslt_path .= $extra_path_part . '/';
			}

			// See if we have a user agent that triggers the server side HTML generation
			$user_agent_trigger = FALSE;
			foreach (Kohana::$config->load('xslt.user_agents') as $user_agent)
			{
				if (strpos($_SERVER['HTTP_USER_AGENT'], $user_agent)) $user_agent_trigger = TRUE;
			}

			if ($this->transform === TRUE || ($this->transform == 'auto' && $user_agent_trigger == TRUE))
			{
				$xslt = new DOMDocument;
				if (file_exists(getenv('DOCUMENT_ROOT').$this->xslt_path.$this->xslt_stylesheet.'.xsl'))
				{
					// If the stylesheet exists in the specified path, load it directly
					$xslt->load(getenv('DOCUMENT_ROOT').$this->xslt_path.$this->xslt_stylesheet.'.xsl');
				}
				else
				{
					// Else make a search for it

					// We need to load all theme modules
					foreach (scandir(MODPATH) as $modulePath)
					{
						if (substr($modulePath, 0, 5) == 'theme')
						{
							Kohana::modules(array($modulePath => MODPATH.$modulePath) + Kohana::modules());
						}
					}

					$xslt->load(Kohana::find_file(
						rtrim(preg_replace('/^'.str_replace('/', '\\/', Kohana::$base_url).'/', '', $this->xslt_path), '/'),
						$this->xslt_stylesheet,
						'xsl'
					));
				}

				// We need to update paths to included XSL elements
				$XPath         = new DOMXPath($xslt);
				$include_nodes = $XPath->query('//xsl:include');

				foreach ($include_nodes as $include_node)
				{
					foreach ($include_node->attributes as $attribute_node)
					{
						$new_filename = Kohana::find_file(rtrim(preg_replace('/^'.str_replace('/', '\\/', Kohana::$base_url).'/', '', $this->xslt_path.$extra_xslt_path), '/'), substr($attribute_node->nodeValue, 0, strlen($attribute_node->nodeValue) - 4), 'xsl');
						$include_node->removeAttribute('href');
						$include_node->setAttribute('href', $new_filename);
					}
				}
				// Done updating paths

				$proc = new xsltprocessor();
				$proc->importStyleSheet($xslt);

				echo $proc->transformToXML($this->dom);
			}
			else
			{
				$this->response->headers('Content-Type', 'application/xml; encoding='.Kohana::$charset.';');
				echo $this->dom->saveXML();
			}
		}
		elseif ($this->transform == 'XML')
		{
			$this->response->headers('Content-Type', 'application/xml; encoding='.Kohana::$charset.';');
			echo $this->dom->saveXML();
		}
		elseif ($this->transform == 'JSON')
		{
			$this->response->headers('Content-type: application/json; encoding='.Kohana::$charset.';');
			echo json_encode(new SimpleXMLElement($this->dom->saveXML(), LIBXML_NOCDATA));
		}

		return TRUE;
	}

	public function after()
	{
		if (Kohana::$profiling === TRUE)
		{
			xml::to_XML(
				array('benchmark' => Profiler::application()),
				$this->xml_meta
			);
		}

		if ($this->auto_render == TRUE)
		{
			// Render the template immediately after the controller method
			$this->render();
		}
	}

	/**
	 * Add a simple error message
	 *
	 * @param str $error
	 * @return boolean
	 */
	public function add_error($error)
	{
		if ( ! isset($this->xml_content_errors))
		{
			$this->xml_content_errors = $this->xml_content->appendChild($this->dom->createElement('errors'));
		}

		xml::to_XML(array('error' => $error), $this->xml_content_errors);
		return TRUE;
	}

	/**
	 * Add form errors
	 *
	 * @param arr $errors - as from Validate::errors()
	 * @return boolean
	 */
	public function add_form_errors($errors)
	{
/*
Array
(
    [username] => Array
        (
            [0] => Valid::not_empty
            [1] => User::username_available
        )

    [password] => Array
        (
            [0] => Valid::not_empty
        )

    // To add a message:
    [username] => 'Username is to ugly'

)*/


		if ( ! isset($this->xml_content_errors))
		{
			$this->xml_content_errors = $this->xml_content->appendChild($this->dom->createElement('errors'));
		}

		if ( ! isset($this->xml_content_errors_form_errors))
		{
			$this->xml_content_errors_form_errors = $this->xml_content_errors->appendChild($this->dom->createElement('form_errors'));
		}

		foreach ($errors as $field => $field_errors)
		{
			if (is_array($field_errors))
			{
				foreach ($field_errors as $field_error)
				{
					xml::to_XML(array($field => $field_error), $this->xml_content_errors_form_errors);
				}
			}
			else
			{
				xml::to_XML(array($field => array('message' => $field_errors)), $this->xml_content_errors_form_errors);
			}
		}

		return TRUE;
	}

	/**
	 * Add simple message
	 *
	 * @param str $message
	 * @return boolean
	 */
	public function add_message($message)
	{
		if ( ! isset($this->xml_content_messages))
		{
			$this->xml_content_messages = $this->xml_content->appendChild($this->dom->createElement('messages'));
		}

		xml::to_XML(array('message' => $message), $this->xml_content_messages);
		return TRUE;
	}

	/**
	 * Redirect to another URI. All further execution is terminated
	 *
	 * @param str $uri - If left out, redirects to previous uri.
	 */
	public function redirect($uri = FALSE)
	{

		if ($uri == FALSE)
		{
			if (isset($_SERVER['HTTP_REFERER']))
			{
				$this->request->redirect($_SERVER['HTTP_REFERER']);
			}
			else
			{
				$this->request->redirect(Kohana::$base_url);
			}
		}

		$this->request->redirect($uri);
	}

	/**
	 * Set form data - the data that should fill out forms
	 *
	 * @param arr - form data
	 * @return boolean
	 */
	public function set_formdata($formdata)
	{
		if ( ! isset($this->xml_content_formdata))
		{
			$this->xml_content_formdata = $this->xml_content->appendChild($this->dom->createElement('formdata'));
		}

		$formatted_formdata = array();
		foreach ($formdata as $field => $data)
		{
			$formatted_formdata[] = array(
				'@id'      => $field,
				'$content' => $data,
			);
		}

		xml::to_XML($formatted_formdata, $this->xml_content_formdata, 'field');
		return TRUE;
	}

}
