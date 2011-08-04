<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Images extends Admincontroller {

	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/images';
		xml::to_XML(array('admin_page' => 'Images'), $this->xml_meta);
	}

	public function action_index()
	{
		// List images
		$this->xml_content_images = $this->xml_content->appendChild($this->dom->createElement('images'));
		foreach (Content_Image::get_images() as $image_name => $image_details)
		{
			// Create the image node and set the image data to it
			$image_node = $this->xml_content_images->appendChild($this->dom->createElement('image'));
			$image_node->setAttribute('name', $image_name);
			$image_node->appendChild($this->dom->createElement('name', substr($image_name, 0, strlen($image_name) - 4)));
			$image_node->appendChild($this->dom->createElement('URL', 'user_content/images/'.$image_name));

			foreach ($image_details as $detail_name => $detail_values)
			{
				foreach ($detail_values as $detail_value)
				{
					$image_node->appendChild($this->dom->createElement($detail_name, $detail_value));
				}
			}
		}
	}

	public function action_add_image()
	{
		if (count($_FILES))
		{
			$pathinfo = pathinfo($_FILES['file']['name']);
			if (strtolower($pathinfo['extension']) == 'jpg')
			{
				$new_filename = $_FILES['file']['name'];
				$counter      = 1;
				while ( ! Content_Image::image_name_available($new_filename))
				{
					$new_filename = substr($_FILES['file']['name'], 0, strlen($_FILES['file']['name']) - 4) . '_'.$counter.'.jpg';
					$counter++;
				}
				if (move_uploaded_file($_FILES['file']['tmp_name'], APPPATH.'/user_content/images/'.$new_filename))
				{

					$gd_img_object = ImageCreateFromJpeg(Kohana::config('user_content.dir').'/images/'.$new_filename);
					$details       = array(
					                   'width'  => imagesx($gd_img_object),
					                   'height' => imagesy($gd_img_object)
					                 );
					foreach ($_POST['tag'] as $nr => $tag_name) $details[$tag_name] = $_POST['tag_value'][$nr];
					Content_Image::new_image($new_filename, $details);

					$this->add_message('Image "'.$new_filename.'" added');
				}
				else $this->add_error('Unknown error uploading image');
			}
			else
			{
				$this->add_error('Image must be of jpeg type (file extension .jpg)');
			}
		}
	}

	public function action_edit_image($name)
	{
		if ($content_image = new Content_Image($name))
		{

			$short_name = substr($name, 0, strlen($name) - 4);

			$this->xml_content_image = $this->xml_content->appendChild($this->dom->createElement('image'));
			$this->xml_content_image->setAttribute('name', $name);

			$this->xml_content_image->appendChild($this->dom->createElement('URL', 'user_content/images/'.$name));
			$tags_node = $this->xml_content_image->appendChild($this->dom->createElement('tags'));

			if (count($_POST))
			{
				$_POST['name'] = URL::title($_POST['name'], '-', TRUE);
				$post = new Validation($_POST);
				$post->filter('trim');
				$post->rule('Valid::not_empty', 'name');

				$form_data = $post->as_array();
				if ($form_data['name'] != $short_name)
				{
					$post->rule('Content_Image::image_name_available', 'name');
				}

				// Check for form errors
				if ($post->validate())
				{
					// No form errors, edit image

					$old_image_data = $content_image->get_data();
					$new_image_data = array_merge($content_image->get_data(), $form_data);
					$new_image_data['name'] .= substr($name, strlen($name) - 4);
					$content_image->set_data($new_image_data);

					if ($form_data['name'] != $short_name)
					{
						$_SESSION['content']['image']['message'] = 'Image data saved';
						// Redirect to the new name
						$this->redirect('/admin/images/edit_image/'.$new_image_data['name']);
					}
					else $this->add_message('Image data saved');
				}

			}
			else
			{
				$image_data = $content_image->get_data();

				foreach ($image_data as $tag_name => $tag_values)
				{
					foreach ($tag_values as $tag_value)
					{
						$tag_node = $tags_node->appendChild($this->dom->createElement('tag', $tag_value));
						$tag_node->setAttribute('name', $tag_name);
					}
				}
			}
		}
		else $this->redirect();
	}

	public function action_rm_image($name)
	{
		$content_image = new Content_Image($name);
		$content_image->rm_image();

		$this->redirect();
	}

}
