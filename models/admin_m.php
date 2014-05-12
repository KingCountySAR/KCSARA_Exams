<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 * The galleries module enables users to create albums, upload photos and manage their existing albums.
 *
 * @author 		jjperezaguinaga
 * @package 	PyroCMS
 * @subpackage 	Contractors Module
 * @category 	Modules
 * @license 	Apache License v2.0
 */
class Admin_m extends MY_Model {

	public function get_all_published_exams()
	{
		$_table_prefix = $this->config->item('exams._table_prefix');
		$this->db->where('published', 1);
		return $this->db->get($this->db->dbprefix($_table_prefix . 'quizzes'))->result_array();
	}

	public function get_all_unpublished_exams()
	{
		$_table_prefix = $this->config->item('exams._table_prefix');
		$this->db->where('published', 0);
		return $this->db->get($this->db->dbprefix($_table_prefix . 'quizzes'))->result_array();
	}

	public function publish( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('quizid', $id);
		return $this->db->update($this->db->dbprefix($_table_prefix . 'quizzes'), array( 'published' => 1, 'enabled' => 1 )); 
	}

	public function depublish( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('quizid', $id);
		return $this->db->update($this->db->dbprefix($_table_prefix . 'quizzes'), array( 'published' => 0, 'enabled' => 1 )); 
	}

	public function enable( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('quizid', $id);
		return $this->db->update($this->db->dbprefix($_table_prefix . 'quizzes'), array( 'enabled' => 1 )); 
	}

	public function disable( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('quizid', $id);
		return $this->db->update($this->db->dbprefix($_table_prefix . 'quizzes'), array( 'enabled' => 0 )); 
	}

	public function override_spamscore( $logid )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('logid', $logid);
		$this->db->update($this->db->dbprefix($_table_prefix . 'log'), array('spamscoreoverride' => 1)); 
	}

}