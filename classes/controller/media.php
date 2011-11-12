<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Media extends Controller
{

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

	public function action_css($path)
	{
		$file = Kohana::find_file('css', $path, 'css');
		if ($file)
		{
			$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
			$this->response->headers('Content-Type', 'text/css');
			echo file_get_contents($file);
		}
		else
		{
// This needs to be altered to function in Kohana 3.1 and then to 3.2 :)
			$this->response->status = 404;
			echo Request::factory('404')->execute()->response;
		}
	}

	public function action_img()
	{
		$file = $this->request->param('file');

		// Find the file ending
		$file_parts  = explode('.', $file);
		$file_ending = end($file_parts);

		$file = Kohana::find_file('img', substr($file, 0, strlen($file) - (strlen($file_ending) + 1)), $file_ending);
		if ($file)
		{
			$mime = File::mime_by_ext($file_ending);
			if (substr($mime, 0, 5) == 'image')
			{
				$this->response->headers('Content-Type', 'content-type: '.$mime.'; encoding='.Kohana::$charset.';');

				// Getting headers sent by the client.
				$headers = apache_request_headers();

				// Checking if the client is validating his cache and if it is current.
				if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($file)))
				{
					// Client's cache IS current, so we just respond '304 Not Modified'.
					$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
					$this->response->status(304);
				}
				else
				{
					// Image not cached or cache outdated, we respond '200 OK' and output the image.
					$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
					$this->response->headers('Content-Length', strval(filesize($file)));
					$this->response->status(200);
					echo file_get_contents($file);
				}
			}
			else
			{
				// This is not an image, so we respond that it is not found
				throw new Http_Exception_404('File not found!');
			}
		}
		else
		{
			// File not found at all
			throw new Http_Exception_404('File not found!');
		}
	}

	public function action_js()
	{
		$path = $this->request->param('path');

		$file = Kohana::find_file('js', $path, 'js');
		if ($file)
		{
			$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
			$this->response->headers('Content-Type', 'application/javascript');
			echo file_get_contents($file);
		}
		else
		{
			throw new Http_Exception_404('File not found!');
		}
	}


	public function action_user_content_image()
	{
		$file = $this->request->param('file');

		// Find the file ending
		$file_parts  = explode('.', $file);
		$file_ending = end($file_parts);
		$filename    = $file;

		// Check if it needs resizing
		$cache_ending = '';
		if (isset($_GET['width']) && preg_match('/^\d+$/', $_GET['width']))   $cache_ending .= '_width_'.$_GET['width'];
		elseif (isset($_GET['width']))                                        unset($_GET['width']);

		if (isset($_GET['height']) && preg_match('/^\d+$/', $_GET['height'])) $cache_ending .= '_height_'.$_GET['height'];
		elseif (isset($_GET['height']))                                       unset($_GET['height']);

		if ($cache_ending != '')
		{
			// Resizing needed
			exec('mkdir -p '.Kohana::$cache_dir.'/user_content/images'); // Make sure the cache dir exists
			exec('chmod a+w '.Kohana::$cache_dir.'/user_content/images'); // Make sure its writeable by all
			$file = Kohana::$cache_dir.'/user_content/images/'.$filename.$cache_ending;
			if ( ! file_exists($file))
			{
				// Create a new cached resized file
				list($original_width, $original_height) = getimagesize(Kohana::$config->load('user_content.dir').'/images/'.$filename);
				$wh_ratio = $original_width / $original_height;

				if (isset($_GET['width']))  $new_width  = $_GET['width'];
				else                        $new_width  = $original_width;

				if (isset($_GET['height'])) $new_height = $_GET['height'];
				else                        $new_height = $original_height;

				if ($new_width / $new_height > $wh_ratio) {
					$calculated_width  = $new_height * $wh_ratio;
					$calculated_height = $new_height;
				} else {
					$calculated_height = $new_width / $wh_ratio;
					$calculated_width  = $new_width;
				}

				$src = imagecreatefromjpeg(Kohana::$config->load('user_content.dir').'/images/'.$filename);
				$dst = imagecreatetruecolor($calculated_width, $calculated_height);
				imagecopyresampled($dst, $src, 0, 0, 0, 0, $calculated_width, $calculated_height, $original_width, $original_height);
				imagejpeg($dst, $file);
			}
		}
		else
		{
			$file = Kohana::$config->load('user_content.dir').'/images/'.$file;
		}


		if (file_exists($file))
		{
			$mime = File::mime_by_ext($file_ending);
			if (substr($mime, 0, 5) == 'image')
			{
				$this->response->headers('Content-Type', 'content-type: '.$mime.'; encoding='.Kohana::$charset.';');

				// Getting headers sent by the client.
				$headers = apache_request_headers();

				// Checking if the client is validating his cache and if it is current.
				if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($file)))
				{
					// Client's cache IS current, so we just respond '304 Not Modified'.
					$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
					$this->response->status(304);
				}
				else
				{
					// Image not cached or cache outdated, we respond '200 OK' and output the image.
					$this->response->headers('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
					$this->response->headers('Content-Length', strval(filesize($file)));
					$this->response->status(200);
					echo file_get_contents($file);
				}
			}
			else
			{
				// This is not an image, so we respond that it is not found
				throw new Http_Exception_404('File not found!');
			}
		}
		else
		{
			// File not found at all
			throw new Http_Exception_404('File not found!');
		}
	}

	public function action_xsl()
	{
		$path = $this->request->param('path');

		$file = Kohana::find_file('xsl', $path, 'xsl');
		if ($file)
		{
			$this->response->headers('Content-type', 'text/xml; encoding='.Kohana::$charset.';');
			echo file_get_contents($file);
		}
		else
		{
			throw new Http_Exception_404('File not found!');
		}
	}

}
