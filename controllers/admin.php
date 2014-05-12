<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('admin_m');
		$this->lang->load('exams');
	}

	private function generate_uuid4()
	{
		$a = '0123456789abcdef';
		$str = strrev( floor( microtime(TRUE) ) );

		$i = 0;
		while( strlen( $str ) < 30 )
		{
			$str = substr_replace( $str, substr( $a, rand(0, 15), 1), $i, 0);
			$i = $i + 2;
		}

		$str = substr_replace( $str, '-', 8, 0);
		$str = substr_replace( $str, '-4', 13, 0);
		$str = substr_replace( $str, '-', 18, 0);
		$str = substr_replace( $str, substr( '89ab', rand(0, 3), 1), 19, 0);
		$str = substr_replace( $str, '-', 23, 0);

		return $str;

	}

	public function index()
	{

		$data_published_exams = $this->admin_m->get_all_published_exams();
		$data_unpublished_exams = $this->admin_m->get_all_unpublished_exams();

		$this->template
			->set('data_published_exams', $data_published_exams)
			->set('data_unpublished_exams', $data_unpublished_exams)
			->build('admin/index');

	}

	public function edit()
	{

		$this->load->helper('form');
		$this->template
			->append_css('module::exams.css')
			->set('quiz', array())
			->build('admin/create');


	}

	public function agencies()
	{

		$data_published_exams = $this->admin_m->get_all_published_exams();
		$data_unpublished_exams = $this->admin_m->get_all_unpublished_exams();

		$this->template->active_section = 'agencies';
		$this->template
			->set('data_published_exams', $data_published_exams)
			->set('data_unpublished_exams', $data_unpublished_exams)
			->build('admin/index');

	}

	public function submissions()
	{

		$data_published_exams = $this->admin_m->get_all_published_exams();
		$data_unpublished_exams = $this->admin_m->get_all_unpublished_exams();

		$this->template->active_section = 'submissions';
		$this->template
			->set('data_published_exams', $data_published_exams)
			->set('data_unpublished_exams', $data_unpublished_exams)
			->build('admin/index');

	}

	public function item()
	{

		$this->edit();


	}

	public function create()
	{

		$this->edit();


	}

	public function publish()
	{

		$this->admin_m->publish($this->uri->segment(4));
		$this->session->set_flashdata('success', lang('exams:publish_success') );

		redirect('admin/exams');

	}

	public function depublish()
	{

		$this->admin_m->depublish($this->uri->segment(4));
		$this->session->set_flashdata('success', lang('exams:depublish_success') );

		redirect('admin/exams');

	}

	public function enable()
	{
		$this->admin_m->enable($this->uri->segment(4));
		$this->session->set_flashdata('success', lang('exams:enable_success') );

		redirect('admin/exams');

	}

	public function disable()
	{
		$this->admin_m->disable($this->uri->segment(4));
		$this->session->set_flashdata('success', lang('exams:disable_success') );

		redirect('admin/exams');

	}

	public function override()
    {
    	/*
    	 * Example usage:
    	 * 		http://pyro.douglasburchard.com/admin/exams/override/25a68601-8246-4055-9963-0182bf08610d
    	 * 
    	 */
    	$logid = $this->uri->segment(4);
		$this->admin_m->override_spamscore( $logid );
		$this->session->set_flashdata('success', 'The spam-score has been overridden.' );

		redirect('admin/exams');
	}

}