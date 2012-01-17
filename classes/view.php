<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class View
{

	private $template;

	public $JSON;

	public function __construct($controller, $template_name = FALSE)
	{
		$this->JSON = array();

		if ( ! $template_name) $template_name = $controller->request->controller();

		$template_filename = Kohana::find_file('templates', $template_name, 'mustache');

		if (file_exists($template_filename)) $this->template = file_get_contents($template_filename);
		else                                 $this->template = "";

		// Parse in external files
		$this->template = $this->load_template($this->template);

		$this->JSON['meta'] = array(
			'protocol'      => (isset($_SERVER['HTTPS'])) ? 'https' : 'http',
			'domain'        => $_SERVER['SERVER_NAME'],
			'base'					=> URL::base(),
			'path'          => $controller->request->uri(),
			'action'        => $controller->request->action(),
			'controller'    => $controller->request->controller(),
			'url_params'    => $_GET,
		);
		$this->JSON['content'] = array();
	}

	public function render()
	{
		if (isset($_GET['JSON']))
			echo json_encode($this->JSON);
		elseif (isset($_GET['JSONr']))
		{
			echo '<pre>';
			print_r($this->JSON);
		}
		else
		{
			$mustache = new Mustache;
			echo $mustache->render($this->template, $this->JSON);
		}
	}

	private function load_template($template_string)
	{
		preg_match_all('/\[\[load\s(.*)\.mustache\]\]/', $template_string, $matches);

		foreach ($matches[1] as $nr => $match)
		{
			$template_filename = Kohana::find_file('templates', $match, 'mustache');
            if(!$template_filename) $template_filename = Kohana::find_file('templates','404','mustache');
			if (file_exists($template_filename)) $new_template = file_get_contents($template_filename);

			$new_template    = $this->load_template($new_template);
			$template_string = str_replace($matches[0][$nr], $new_template, $template_string);
		}

		return $template_string;
	}

}
