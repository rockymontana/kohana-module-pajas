<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Media {

	public function __construct() {}

	public function before() {
		// We need to load all theme modules
		foreach (scandir(MODPATH) as $modulePath)
		{
			if (substr($modulePath, 0, 5) == 'theme')
			{
				Kohana::modules(array($modulePath => MODPATH.$modulePath) + Kohana::modules());
			}
		}
	}

	public function after() {}

	public function action_css($path)
	{
		$file = Kohana::find_file('css', $path, 'css');
		if ($file)
		{
			Request::instance()->headers['Last-Modified'] = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';
			Request::instance()->headers['Content-Type'] = 'text/css';
		  echo file_get_contents($file);
		}
		else
		{
		  Request::instance()->status = 404;
		  echo Request::factory('404')->execute()->response;
		}
	}

	public function action_img($file)
	{
		// Find the file ending
		$file_parts  = explode('.', $file);
		$file_ending = end($file_parts);

		$file = Kohana::find_file('img', substr($file, 0, strlen($file) - (strlen($file_ending) + 1)), $file_ending);
		if ($file)
		{
			$mime = File::mime_by_ext($file_ending);
			if (substr($mime, 0, 5) == 'image')
			{
				Request::instance()->headers['Content-Type'] = 'content-type: '.$mime.'; encoding='.Kohana::$charset.';';

				// Getting headers sent by the client.
				$headers = apache_request_headers();

				// Checking if the client is validating his cache and if it is current.
				if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($file)))
				{
					// Client's cache IS current, so we just respond '304 Not Modified'.
					Request::instance()->headers['Last-Modified'] = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';
					Request::instance()->status = 304;
				}
				else
				{
					// Image not cached or cache outdated, we respond '200 OK' and output the image.
					Request::instance()->headers['Last-Modified']  = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';
					Request::instance()->headers['Content-Length'] = filesize($file);
					Request::instance()->status = 200;
					echo file_get_contents($file);
				}
			}
			else
			{
				// This is not an image, so we respond that it is not found
				Request::instance()->status = 404;
				echo Request::factory('404')->execute()->response;
			}
		}
		else
		{
			// File not found at all
		  Request::instance()->status = 404;
		  echo Request::factory('404')->execute()->response;
		}
	}

	public function action_js($path)
	{
		$file = Kohana::find_file('js', $path, 'js');
		if ($file)
		{
			Request::instance()->headers['Last-Modified'] = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';
			Request::instance()->headers['Content-Type'] = 'application/javascript';
		  echo file_get_contents($file);
		}
		else
		{
		  Request::instance()->status = 404;
		  echo Request::factory('404')->execute()->response;
		}
	}

	public function action_xsl($path)
	{
		$file = Kohana::find_file('xsl', $path, 'xsl');
		if ($file)
		{
			Request::instance()->headers['Content-Type'] = 'content-type: text/xml; encoding='.Kohana::$charset.';';
		  echo file_get_contents($file);
		}
		else
		{
		  Request::instance()->status = 404;
		  echo Request::factory('404')->execute()->response;
		}
	}

}
