<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Pages extends Admincontroller {

	public function __construct()
	{
		parent::__construct();
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/pages';
		xml::to_XML(array('admin_page' => 'Pages'), $this->xml_meta);
	}

	public function action_index()
	{
		$page_model = new Page;

		$this->xml_content_pages = $this->xml_content->appendChild($this->dom->createElement('pages'));
		xml::to_XML($page_model->get_pages(), $this->xml_content_pages, 'page', 'id');
	}

	public function action_add_page()
	{
		if (count($_POST))
		{
			if ($_POST['uri'] == '') $_POST['uri'] = $_POST['name'];

			$_POST['uri'] = URL::title($_POST['uri'], '-', TRUE);

			$post = new Validate($_POST);
			$post->filter(TRUE, 'trim');
			$post->rule('name', 'not_empty');
			$post->rule('name', 'Page::page_name_available');
			$post->rule('uri', 'Page::page_uri_available');
			$post->label('content', 'Content');

			if ($post->check())
			{
				$page_id = Page::new_page($post['name'], $post['uri'], $post['content']);
				$this->add_message('Page "'.$post['name'].'" added');
			}
			else
			{
				// Form errors detected!

				$this->add_error('Fix errors and try again');
				$this->add_form_errors($post->errors());
				$this->set_formdata($post);
			}
		}
	}

	public function action_edit_page($id)
	{
		$page_model = new Page($id);
		if ($page_model->get_page_id())
		{
			$this->xml_content_page_data = $this->xml_content->appendChild($this->dom->createElement('page_data'));

			if (count($_POST))
			{

				if ($_POST['uri'] == '') $_POST['uri'] = $_POST['name'];

				$_POST['uri'] = URL::title($_POST['uri'], '-', TRUE);

				$post = new Validate($_POST);
				$post->filter(TRUE, 'trim');
				$post->rule('name', 'not_empty');
				$post->label('uri', 'URI');
				$post->label('content', 'Content');

				$valid = TRUE;
				if ($post->check())
				{
					$current_page_data = $page_model->get_page_data();

					if ($post['name'] != $current_page_data['name'] && !Page::page_name_available($post['name']))
					{
						$post->error('name', 'Page::page_name_available');
						$valid = FALSE;
					}

					if ($post['uri'] != $current_page_data['uri'] && !Page::page_uri_available($post['uri']))
					{
						$post->error('uri', 'Page::page_uri_available');
						$valid = FALSE;
					}

					if ($valid)
					{
						$page_model->update_page_data($post['name'], $post['uri'], $post['content']);
						$this->add_message('Page "'.$post['name'].'" updated');
						$this->set_formdata($page_model->get_page_data());
					}
				}
				else $valid = FALSE;

				if ($valid == FALSE)
				{
					// Form errors detected!

					$this->add_error('Fix errors and try again');
					$this->add_form_errors($post->errors());
					$this->set_formdata($post);
				}

			}
			else
			{
				$this->set_formdata($page_model->get_page_data());
			}

			xml::to_XML($page_model->get_page_data(), $this->xml_content_page_data, NULL, 'id');
		}
		else
		{
			$this->redirect();
		}
	}

	public function action_rm_page($id)
	{
		$page_model = new Page($id);
		$page_name  = $page_model->get_page_data('name');

		$page_model->rm_page();

		$this->redirect();
	}

}
