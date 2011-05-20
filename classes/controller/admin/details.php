<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Details extends Admincontroller {

	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/details';
		xml::to_XML(array('admin_page' => 'Detail fields'), $this->xml_meta);
	}

	public function action_index()
	{
		// List details
		$details = array();
		foreach (Content_Detail::get_details() as $id => $name)
		{
			$details['detail id="'.$id.'"'] = $name;
		}

		$this->xml_content_details = $this->xml_content->appendChild($this->dom->createElement('details'));
		xml::to_XML($details, $this->xml_content_details);
	}

	public function action_add_detail()
	{
		if (count($_POST) && isset($_POST['name']))
		{
			$post = new Validation($_POST);
			$post->filter('trim');
			$post->rule('Valid::not_empty',               'name');
			$post->rule('Content_Detail::name_available', 'name');
			$post_values = $post->as_array();

			if ($post->validate())
			{
				Content_Detail::new_detail($post_values['name']);
				$this->add_message('Detail "'.$post_values['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post_values);
			}
		}
	}

	public function action_rm_detail($detail_id)
	{
		$content_detail = new Content_detail(FALSE, $detail_id);
		$content_detail->rm_detail();

		$this->redirect();
	}

}
