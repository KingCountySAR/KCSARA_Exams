<?php defined('BASEPATH') or exit('No direct script access allowed');

class Exams_m extends MY_Model {

	public function get_all_published_exams()
	{
		$_table_prefix = $this->config->item('exams._table_prefix');
		$this->db->where('published', 1);
		$this->db->order_by("name");
		return $this->db->get($this->db->dbprefix($_table_prefix . 'quizzes'))->result_array();
	}

	public function get_exam( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where('quizid', $id);
		return $this->db->get($this->db->dbprefix($_table_prefix . 'quizzes'))->result_array();
	}

	public function get_exam_agency( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`quizid`, a.`agencyid`, `name`, `fields`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'agencies') . ' AS a');
		$this->db->join($this->db->dbprefix($_table_prefix . 'agency_quizzes') . ' AS aq', 'aq.agencyid = a.agencyid');
		$this->db->where('quizid', $id);

		return $this->db->get()->result_array();
	}

	public function get_agency( $id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`agencyid`, `name`, `email`, `fields`, `digest`, `digestlast`, `digestpin`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'agencies'));
		$this->db->where('agencyid', $id);
		$this->db->or_where('digestpin', $id);

		return $this->db->get()->result_array();
	}

	public function get_digest_agencies()
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`agencyid`, `name`, `email`, `fields`, `digest`, `digestlast`, `digestpin`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'agencies'));
		$this->db->where('digest > ', 0);

		return $this->db->get()->result_array();
	}

	public function get_exam_agency_list( $quizid )
	{
		// array( 'agencyid'=>'agencyname' )
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('a.`agencyid`, `name`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'agencies') . ' AS a');
		$this->db->join($this->db->dbprefix($_table_prefix . 'agency_quizzes') . ' AS aq', 'aq.agencyid = a.agencyid');
		$this->db->where('quizid', $quizid);
		$this->db->order_by('name', 'asc');

		return $this->db->get()->result_array();
	}

	public function get_exam_agency_fields( $quizid )
	{
		// array( 'agencyid'=>array( array( name=>'fieldid', label=>'fieldname' ) ) )
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`quizid`, a.`agencyid`, `name`, `fields`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'agencies') . ' AS a');
		$this->db->join($this->db->dbprefix($_table_prefix . 'agency_quizzes') . ' AS aq', 'aq.agencyid = a.agencyid');
		$this->db->where('quizid', $quizid);

		return $this->db->get()->result_array();
	}

	public function get_exam_questions( $id, $random = FALSE )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('q.`queryid`, q.`text` AS `query_text`, o.`optionid`, o.`text` AS `option_text`, `correct`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'queries') . ' AS q');
		$this->db->join($this->db->dbprefix($_table_prefix . 'options') . ' AS o', 'q.queryid = o.queryid');
		$this->db->where('quizid', $id);
		$this->db->order_by("queryid", "asc");
		$results = $this->db->get()->result_array();
		if( $random )
		{
			$result_tree = array();
			$result_set = array();
			foreach($results as $row)
			{
				if ( ! array_key_exists( $row['queryid'], $result_tree ))
				{
					$result_tree[$row['queryid']] = array('queryid'=>$row['queryid'],'query_text'=>$row['query_text'],'options'=>array());
				}
				$result_tree[$row['queryid']]['options'][] = array('optionid'=>$row['optionid'],'option_text'=>$row['option_text'],'correct'=>$row['correct']);
			}
			shuffle( $result_tree );
			foreach($result_tree as $query)
			{
				shuffle( $query['options'] );
				foreach($query['options'] as $option)
				{
					$result_set[] = array( 'queryid'=>$query['queryid'], 'query_text'=>$query['query_text'], 'optionid'=>$option['optionid'], 'option_text'=>$option['option_text'], 'correct'=>$option['correct']);
				}
			}
			$results = $result_set;
		}

		return $results;
	}

	public function set_log_row( $logid, $signature, $agencyid, $agency_is_digest, $fullname, $email, $course_name, $results_ratio, $results_percentage, $duration, $spam_score, $spam_explanation, $serialized_input )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$taken_on = new DateTime( "@".time() );
		$taken_on->setTimezone(new DateTimeZone( $this->config->item('exams.default_timezone')));

		$data = array(
			'logid' => $logid,
			'agencyid' => $agencyid,
			'signature' => $signature,
			'datetime' => $taken_on->format('Y-m-d H:i:s'),
			'senttoagency' => ( $agency_is_digest ? '0000-00-00 00:00:00' : $taken_on->format('Y-m-d H:i:s') ),
			'fullname' => $fullname,
			'email' => $email,
			'course' => $course_name,
			'results_ratio' => $results_ratio,
			'results_percentage' => $results_percentage,
			'duration' => $duration,
			'spamscore' => $spam_score,
			'spamexplanation' => implode( '\n', $spam_explanation ),
			'spamscoreoverride' => 0,
			'submission' => $serialized_input,
			'detailed_report' => ""
		);

		$this->db->insert($_table_prefix . 'log', $data);
	}

	public function get_attempt( $attempt_id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`logid`, `datetime`, `fullname`, `course`, `spamscore`, `spamscoreoverride`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'log'));
		$this->db->where('logid', $attempt_id);
		return $this->db->get()->result_array();
	}

	public function get_agency_attempts( $agency_id )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');
		$max_spam_score = $this->config->item('exams.max_spam_score');

		$this->db->select('`logid`, `datetime`, `fullname`, `email`, `course`, `results_ratio`, `results_percentage`, `duration`, `submission`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'log'));
		$this->db->where('agencyid', $agency_id);
		$this->db->where('senttoagency', '0000-00-00 00:00:00');
		$this->db->where("( `spamscore` < '" . $max_spam_score . "' OR `spamscoreoverride` > 0 )");
		$this->db->order_by("datetime", "asc");

		return $this->db->get()->result_array();
	}

	public function mark_lastdigest( $agency, $date )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->where( 'agencyid', $agency );
		$this->db->set('digestlast', $date->format('Y-m-d H:i:s'));
		$this->db->update($this->db->dbprefix($_table_prefix . 'agencies')); 
		
	}

	public function mark_senttoagency( $logids, $date )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');
		$logid_string = implode( ',', $logids );

		$this->db->where("FIND_IN_SET(`logid`, '" . $logid_string . "')");
		$this->db->set('senttoagency', $date->format('Y-m-d H:i:s'));
		$this->db->update($this->db->dbprefix($_table_prefix . 'log')); 

	}

	public function signature_exists( $signature )
	{
		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->db->select('`signature`');
		$this->db->from($this->db->dbprefix($_table_prefix . 'log'));
		$this->db->where('signature', $signature);
		return $this->db->get()->result_array();
	}

}