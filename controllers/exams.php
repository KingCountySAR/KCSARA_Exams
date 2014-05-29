<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exams extends Public_Controller
{
    
	public function __construct()
	{
		parent::__construct();

		$this->load->model('exams_m');
		$this->lang->load('exams');
		$this->load->library('exams_results');
		$this->load->library('exams_uuid4');
		$this->load->library('exams_duration');
	}

	private function signature_seed()
	{
		return sha1( $this->exams_m->db->password );
	}

	private function show_exam( $quizid, $timestamp = NULL, $nonce = NULL, $signature = NULL )
    {

		$timestamp = ( $timestamp != NULL ? $timestamp : time() );
		$nonce = ( $nonce != NULL ? $nonce : new Exams_UUID4() );
		$signature = ( $signature != NULL ? $signature : sha1( $this->signature_seed() . $timestamp . $nonce ) );

		//data from quizzes:
		$exam_data = $this->exams_m->get_exam( $quizid );
		if ( ! $exam_data )
		{
			$this->session->set_flashdata('message', lang('exams:unexpected_error') );
			redirect($this->uri_base('exams'));
		}

		// data from agency
		$agencies = $this->exams_m->get_exam_agency_list( $quizid );
		$agency_list = array('empty'=>lang('exams:select_agency'));
		$agency_list['disabled-1'] = '-';
		foreach($agencies as $data) $agency_list[$data['agencyid']] = $data['name'];
		$agency_list['disabled-2'] = '-';
		$agency_list['none'] = lang('exams:no_affiliation');

		// custom fields
		$custom_fields = $this->agency_fields( $quizid );
		$agency_fields = $custom_fields['fields'];
		$default_agency = $custom_fields['default'];

		// data from queries, options, and answers
		$question_data = $this->exams_m->get_exam_questions( $quizid, $this->config->item('exams.random_query_order') );

		$prior_attempt = $this->session->flashdata('failed_attempt');
		if ( is_array( $prior_attempt ))
		{
			$timestamp = $prior_attempt['timestamp'];
			$nonce = $prior_attempt['nonce'];
			$signature = $prior_attempt['signature'];
		}

        $this->template
        	->prepend_metadata('<script type="text/javascript" src="' . $this->config->item('exams.jquery_location') . '"></script>')
			->append_css('module::exams.css')
			->title($exam_data[0]['name'])
			->set('timestamp', $timestamp)
			->set('nonce', $nonce)
			->set('signature', $signature)
			->set('exam_data', $exam_data)
			->set('agency_list', $agency_list)
			->set('agency_fields', $agency_fields)
			->set('default_agency', $default_agency)
			->set('question_data', $question_data)
			->set('prior_attempt', $prior_attempt)
			->set('form_uri', $this->uri_base( 'exams/' . $exam_data[0]['quizid'] ))
        	->build('exam');
     }

    private function convert_to_fieldname( $str )
    {
    	// $this->convert_to_fieldname($str)
    	return preg_replace('/[^(\x61-\x7A)(\x30-\x39)]*/','', strtolower( trim( $str )));
     }

    private function agency_fields( $quizid )
    {
		$fields = $this->exams_m->get_exam_agency_fields( $quizid );

		$agency_fields = array();
		foreach($fields as $data)
		{
			if ( ! array_key_exists( $data['agencyid'], $agency_fields ) )
			{
				$agency_fields[$data['agencyid']] = array();
			}

			foreach( explode( ',', $data['fields'] ) as $custom_field )
			{
				$agency_fields[$data['agencyid']][] = array('label'=>trim( $custom_field ), 'name'=>$this->convert_to_fieldname( $custom_field ));
			}

			$default_agency = '';
			if ($data['name'] == $this->config->item('exams.default_agency_name'))
			{
				$default_agency = $data['agencyid'];
			}
		}
		return array( 'fields'=>$agency_fields, 'default'=> $default_agency);
	}

	private function uri_segment( $number )
    {
		$segments = explode( "/", str_replace(site_url(), "", current_url()));
		$base = array_search( 'exams', $segments );
    	return $this->uri->segment( $base + $number );
	}

	private function uri_base( $str )
    {
		$segments = explode( "/", str_replace(site_url(), "", current_url()));

		$base = array();
		$i = 0;
		while ( $segments[$i] != 'exams' )
		{
			$base[] = $segments[$i]; 
			$i++;
		}
		$base[] = $str;

    	return implode( '/', $base );
	}

	public function signature_check( $signature )
    {
    	if ( $this->exams_m->signature_exists( $signature ) )
    	{
    		$this->form_validation->set_message('signature_check', 'A successful exam may not be resubmitted, you will need to <a href="' . $this->uri_base('exams/' . $this->uri_segment(2)) . '">reload the page</a>.');
    		return FALSE;
    	}
    	else
    	{
    		return TRUE;
    	}
    }

	public function query_check( $value )
    {
    	if ( empty( $value) )
    	{
    		$this->form_validation->set_message('query_check', 'You must select at least one answer for the question %s.');
    		return FALSE;
    	}
    	else
    	{
    		return TRUE;
    	}
    }

	private function score( $quizid, $exam_data )
    {

    	$this->load->helper(array('form', 'url'));
    	$this->load->library('form_validation');
    	$this->load->library('formheuristics');

		$input = $_POST;
		$serialized_input = json_encode( $_POST );

		$signature = $input['signature'];
		$timestamp = $input['timestamp'];
		$nonce = $input['nonce'];
		$fullname = $input['fullname'];
		$email = $input['email'];
		$agency = $input['agency'];
		$notes = $input['notes'];
		$current_timestamp = time();
		$agency_is_digest = FALSE;

		/*
		 * Retrieve agency and questions.
		 */
		$question_data = $this->exams_m->get_exam_questions( $quizid, FALSE );
		$agency_list = array('none'=>lang('exams:no_affiliation'));
		$agency_fields = array();
		$agency_name = lang('exams:no_affiliation');
		$agency_email = '';
		foreach($this->exams_m->get_agency( $agency ) as $data)
		{
			$agency_list[$data['agencyid']] = $data['name'];
			$agency_fields[$data['agencyid']] = $data['fields'];
			$agency_name = $data['name'];
			$agency_email = $data['email'];
			if ( $data['digest'] > 0) $agency_is_digest = TRUE;
		}

		/*
		 * Validate Form Entries.
		 */
		$this->form_validation->set_error_delimiters('<p class="alert">', '</p>');
		$this->form_validation->set_rules('signature', 'Signature', 'callback_signature_check');
		$this->form_validation->set_rules('fullname', 'Full Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'trim');
		$this->form_validation->set_rules('agency', 'Agency', 'required');
		$this->form_validation->set_rules('remember', 'Remember Me', 'trim');
		$agency_field_report = array();
		$af = $this->agency_fields($quizid);
		$af = $af['fields'];
		if( array_key_exists( $agency, $af ) )
		{
			foreach( $af[$agency] as $field )
			{
				$agency_field_report[] = $field['label'] . ': ' . $input[$field['name']];
				$this->form_validation->set_rules($field['name'], $field['name'], 'trim');
			}
		}
		$last_queryid = '';
		foreach($question_data as $query_data)
		{
			if( $query_data['queryid'] != $last_queryid )
			{
				$this->form_validation->set_rules($query_data['queryid'] . '[]', '"' . $query_data['query_text'] . '"', 'callback_query_check');
			}
			$last_queryid = $query_data['queryid'];
		}
		$this->form_validation->set_rules('notes', 'Notes', 'trim');

		if ($this->form_validation->run() == FALSE)
		{
			$this->template->prepend_metadata('<script type="text/javascript" src="' . $this->config->item('exams.jquery_location') . '"></script>');
			$this->show_exam( $quizid, $timestamp, $nonce, $signature );
		}
		else
		{
			/*
			 * Calculate results.
			 * 		- log_id
			 * 		- score
			 */

			$logid = new Exams_UUID4();
			$results = new Exams_Results( 
				$question_data,
				$input
			);
			$score = $results->score;
			$number_correct = $results->number_correct;
			$incorrect_queries = $results->incorrect_queries;
			$query_count = $results->query_count;

			/*
			 * Generate spam-score.
			 * 		- delay (high / low)
			 * 		- successive submissions
			 * 		- urls
			 * 		- language
			 */
			$blacklistedstrings = explode( ',', lang('exams:blacklisted_words') );

			$heuristics = $this->formheuristics;
			$heuristics->assert_time_not_in_range( $this->config->item('exams.time_minimum'), $this->config->item('exams.time_maximum'), $timestamp, $current_timestamp, $this->config->item('exams.time_not_in_range_score') );
			$heuristics->assert_language_is_inappropriate( $serialized_input, $blacklistedstrings, $this->config->item('exams.language_is_inappropriate') );
			$heuristics->assert_contains_too_many_urls( ( $fullname . ' ' . $email . ' ' . $notes ), $this->config->item('exams.too_many_urls'), 3, 150 );

			$spam_score = $heuristics->score;
			$spam_result = $heuristics->result;
			if($signature != sha1( $this->signature_seed() . $timestamp . $nonce ))
			{
				$spam_score += $this->config->item('exams.signature_checksum');
				$spam_result[] = 'Form signature does not match checksum';
			}
	
			/*
			 * Create a detailed text-based report.
			 * 
			 */
			$detailed_report = $this->detailed_report( 
				$timestamp, 
				$current_timestamp, 
				$fullname, 
				$email, 
				$agency_name, 
				$agency_field_report, 
				$exam_data, 
				$query_count, 
				$number_correct, 
				$score, 
				$notes, 
				$logid, 
				$incorrect_queries
			);

			/*
			 * If not passing, inform visitor
			 * 
			 */
			if ( $score < $this->config->item('exams.passing_score') )
			{
				$this->session->set_flashdata('failed_attempt', $input);

				$this->template
					->title( $exam_data[0]['name'] )
					->set('title', lang('exams:title_notpass'))
					->set('text', lang('exams:text_notpass'))
					->set('score', $score)
					->set('logid', '')
					->set('certificate_uri', $this->uri_base( 'exams/certificate/' . $logid ))
					->set('exams_uri', $this->uri_base( 'exams' ))
					->set('sit_uri', $this->uri_base( 'exams/' . $quizid ))
					->set('detailed_report', $detailed_report)
					->build('results');

				return NULL;
			 }

			/*
			 * Store content in log. If error, report to webmaster.
			 * 
			 */
			 $this->exams_m->set_log_row( $logid, $signature, $agency, $agency_is_digest, $fullname, $email, $exam_data[0]['name'], ($number_correct . ' out of ' . $query_count), $score, abs($current_timestamp - $timestamp), $spam_score, $spam_result, $serialized_input );

			/*
			 * If spam-score is out of range, report to webmaster.
			 * Otherwise, report to visitor and agency.
			 * 
			 */
			if( $spam_score > $this->config->item('exams.max_spam_score') )
			{
				/* TODO Test */
				$subj = htmlspecialchars( $this->config->item('exams.spam_email_subject'));
				$mssg  = htmlspecialchars( lang('exams:spam_score')) . ': ' . $spam_score . "\n";
				$mssg .= 'Explanation: ' . @implode( ', ', $spam_result ) . "\n";
				$mssg .= 'Override: ' . site_url('admin/exams/override/' . $logid) . "\n\n";
				$mssg .= $detailed_report;
				$this->send_email( $this->config->item('exams.webmaster_email'), $subj, $mssg, $logid, $spam_score );
			}
			else
			{
				$subj = htmlspecialchars( $this->config->item('exams.results_email_subject'));
				$this->send_email( $email, $subj, $detailed_report, $logid );
				if ( ! $agency_is_digest )
				{
					$this->send_email( $agency_email, $subj, $detailed_report, $logid );
				}
			}

			/*
			 * Display results
			 * Send email
			 * 
			 */
			$this->template
				->title( $exam_data[0]['name'] )
				->set('title', lang('exams:title_pass'))
				->set('text', lang('exams:text_pass'))
				->set('score', $score)
				->set('logid', $logid)
				->set('certificate_uri', $this->uri_base( 'exams/certificate/' . $logid ))
				->set('exams_uri', $this->uri_base( 'exams' ))
				->set('sit_uri', $this->uri_base( 'exams/' . $quizid ))
				->set('detailed_report', $detailed_report)
				->build('results');
		}

	}

	private function detailed_report( $timestamp, $current_timestamp, $fullname, $email, $agency_name, $agency_field_report, $exam_data, $query_count, $number_correct, $score, $notes, $logid, $incorrect_queries )
    {
		$current_time = new DateTime( "@$current_timestamp" );
		$current_time->setTimezone(new DateTimeZone( $this->config->item('exams.default_timezone')));

		$detailed_report = array();
		$detailed_report[] = lang('exams:agency') . ': ' . $agency_name;
		$detailed_report[] = lang('exams:full_name') . ': ' . $fullname;
		if ( $email )
		{
			$detailed_report[] = lang('exams:email') . ': ' . $email;
		}
		$detailed_report = array_merge( $detailed_report, $agency_field_report );
		$detailed_report[] = '';
		$detailed_report[] = lang('exams:course') . ': ' . $exam_data[0]['name'];
		$detailed_report[] = lang('exams:results') . ': ' . $number_correct . ' out of ' . $query_count . ', ' . $score . '%';
		$detailed_report[] = lang('exams:taken_on') . ': '. $current_time->format(DATE_RFC2822);

		$duration = new Exams_Duration( abs($current_timestamp - $timestamp) );
		$detailed_report[] = lang('exams:duration') . ': ' . $duration->format();

		$detailed_report[] = lang('exams:submission_id') . ': ' . $logid;
		if ( $notes )
		{
			$detailed_report[] = lang('exams:notes') . ': ' . $notes;
		}
		if ( count( $incorrect_queries ) > 0 )
		{
			$detailed_report[] = '';
			$detailed_report[] = lang('exams:answered_incorrectly') . ':';
			$detailed_report = array_merge( $detailed_report, $incorrect_queries );
		}
		return implode( "\n", $detailed_report);
     }

	private function send_email( $to, $subj, $detailed_report, $logid = '', $spam_score = 0 )
    {
		$this->load->library('my_phpmailer');

    	if ( ! filter_var($to, FILTER_VALIDATE_EMAIL) )
    	{
    		return NULL;
    	}

    	$mssg = $detailed_report;
		if ( $logid != '' )
		{
			$mssg .= "\n\n" . htmlspecialchars( lang('exams:download_your')) . ' ' . htmlspecialchars( lang('exams:printable_link')) . ": " . site_url( $this->uri_base( 'exams/certificate/' . $logid ));
		}
		$mssg .= htmlspecialchars( strip_tags( $this->config->item('exams.email_signature')));

		$email = new My_PHPMailer();

		$email->Subject		= $subj;
		$email->Body		= $mssg;

		$email->SetFrom($this->config->item('exams.do-not-reply_address'), $this->config->item('exams.do-not-reply_name'));
		$email->AddAddress($to);

		$email->Send();
     }

	public function index()
    {
		$data_published_exams = $this->exams_m->get_all_published_exams();

		$this->template
			->title( 'Available Exams' )
			->append_css('module::exams.css')
			->set('data_published_exams', $data_published_exams)
        	->build('index');
     }

	public function sit()
    {

    	$quizid = $this->uri_segment(2);
		$exam_data = $this->exams_m->get_exam( $quizid );
		if ( ! $exam_data) redirect($this->uri_base('exams'));

		if (
			! isset( $_POST['nonce'] ) OR
			! isset( $_POST['timestamp'] ) OR
			! isset( $_POST['signature'] ) OR
			! isset( $_POST['fullname'] ) OR
			! isset( $_POST['email'] ) OR
			! isset( $_POST['agency'] ) OR
			! isset( $_POST['notes'] )
			)
		{
			$this->show_exam( $quizid );
		}
		else
		{
			$this->score( $quizid, $exam_data );
		}

     }

	public function certificate()
    {
		$this->load->library('pdf'); // Load library

    	$attemptid = $this->uri_segment(3);
		$attempt = $this->exams_m->get_attempt( $attemptid );
		$img_width = 60;
		$taken_on = new DateTime( $attempt[0]['datetime'] );
		/* $attempt[0]['datetime'] is stored in database as local time. No need to convert the timezone */

		if( $attempt[0]['spamscore'] > $this->config->item('exams.max_spam_score') and $attempt[0]['spamscoreoverride'] == 0 )
		{
    		redirect($this->uri_base('exams'));
		}
		else
		{
			$this->pdf->AddPage('L','Letter');
			$this->pdf->SetMargins(25, 0, 25);
			$this->pdf->SetAutoPageBreak(true, 0);

			$this->pdf->SetFont('Times', 'B', 34);
			$this->pdf->Ln(9);
			$this->pdf->Cell(0, 20, utf8_decode( lang('exams:certificate_title')), 0, 1, 'C');

			$this->pdf->Ln(8);
			$this->pdf->Image($this->config->item('exams.certificate_img'), 109, null, $img_width);
			$this->pdf->Ln(10);
		

			$this->pdf->SetFont('Helvetica', '', 18);
			$this->pdf->Cell(0,8.5, utf8_decode( lang('exams:certificate_lead')), 0, 1, 'C');

			$this->pdf->SetFont('Times', 'B', 30);
			$this->pdf->Cell(0, 24, utf8_decode( $attempt[0]['fullname'] ), 0, 1, 'C');

			$this->pdf->SetFont('Helvetica', '', 18);
			$this->pdf->Cell(0, 8.5, utf8_decode( lang('exams:certificate_tail1')), 0, 1, 'C');
			$this->pdf->Cell(0, 8.5, utf8_decode( lang('exams:certificate_tail2')), 0, 1, 'C');
		
			$this->pdf->SetFont('Times', 'B', 26);
			$this->pdf->Cell(0, 24, utf8_decode( $attempt[0]['course'] ), 0, 1, 'C');

			$this->pdf->SetFont('Helvetica', '', 15);
			$this->pdf->Cell(0, 8.5, utf8_decode( $taken_on->format(lang('exams:cerificate_dateformat'))), 0, 1, 'C');

			$this->pdf->SetFont('Helvetica', '', 7);
			$this->pdf->SetTextColor(200, 200, 200);
			$this->pdf->SetY(200);
			$this->pdf->Cell(0, 8.5, utf8_decode( 'http://www.kcsara.org/exams'), 0, 1, 'R');
			$this->pdf->SetY(200);
			$this->pdf->Cell(0, 8.5, utf8_decode( 'Submission: ' . utf8_decode( $attempt[0]['logid'] )), 0, 1, 'L');

			$this->pdf->Output();
		}
     }

	private function send_digest( $agency )
    {
		$this->load->library('my_phpmailer');

    	$output = array();
		$now = new DateTime();
		$now->setTimezone(new DateTimeZone( $this->config->item('exams.default_timezone')));

		$digest_last = new DateTime( $agency['digestlast'] );
		/* $agency['digestlast'] is stored in database as local time. No need to convert the timezone */

		$attempts = $this->exams_m->get_agency_attempts( $agency['agencyid'] );

		$to = $agency['email'];
		$from = $this->config->item('exams.do-not-reply_address');
		$subj = '[KCSARA Online Exams] Report for ' . $agency['name'];
		$mssg = '';
		$csv = '';
		$headers = 'From: ' > $from . "\r\n";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		// generate email
		if ( $attempts )
		{
			$mssg .= "<html>";
			$mssg .= "\r\n<body>";

			$mssg .= "\r\n<style>";
			$mssg .= "\r\n td {";
			$mssg .= "\r\n    white-space:nowrap;";
			$mssg .= "\r\n    padding:0.25em 0.5em;";
			$mssg .= "\r\n}";
			$mssg .= "\r\n tbody tr:nth-child(odd) {";
			$mssg .= "\r\n    background:#ccc;";
			$mssg .= "\r\n}";
			$mssg .= "\r\n</style>";

			$mssg .= "\r\n<p>Online written evaluation report for " . $agency['name'] . " on " . $now->format('l, F jS, Y \a\t g:i a') . ".</p>";

			$mssg .= "\r\n<table cellspacing=\"2\" cellpadding=\"8\">";
			$mssg .= "\r\n<thead>";
			$mssg .= "\r\n<tr align=\"left\">";
			$mssg .= "\r\n<th nowrap=\"nowrap\">Full Name</th>";
			$csv .= '"Full Name",';

			$mssg .= "\r\n<th nowrap=\"nowrap\">Email</th>";
			$csv .= '"Email",';

			foreach( explode( ',', $agency['fields'] ) as $field )
			{
				$mssg .= "\r\n<th nowrap=\"nowrap\">" . $field . "</th>";
				$csv .= '"' . $field . '",';
			}

			$mssg .= "\r\n<th nowrap=\"nowrap\">Course</th>";
			$csv .= '"Course",';

			$mssg .= "\r\n<th nowrap=\"nowrap\">Results</th>";
			$csv .= '"Results",';

			$mssg .= "\r\n<th nowrap=\"nowrap\">Score</th>";
			$csv .= '"Score",';

			$mssg .= "\r\n<th nowrap=\"nowrap\">Taken On</th>";
			$csv .= '"Taken On",';

			$mssg .= "\r\n<th nowrap=\"nowrap\">Duration (in Seconds)</th>";
			$csv .= "\"Duration (in Seconds)\"\n";

			$csv .= "\"Link to Certificate\"\n";

			$mssg .= "\r\n</tr>";
			$mssg .= "\r\n</thead>";

			$mssg .= "\r\n<tbody>";

			$logids = array();
			$i = 0;
			foreach( $attempts as $data )
			{
				$submission = json_decode( $data['submission'], TRUE );
				$dt = new DateTime( $data['datetime']);
				/* $data['datetime'] is stored in database as local time. No need to convert the timezone */
				$mssg .= "\r\n<tr" . ($i % 2 == 0 ? " style=\"background:#ccc;\"" : "") . ">";

				$mssg .= "\r\n<td nowrap=\"nowrap\">" . $data['fullname'] . "</td>";
				$csv .= '"' . str_replace( '"', '""', $data['fullname'] ) . '",';

				$mssg .= "\r\n<td nowrap=\"nowrap\"><a href=\"mailto:" . $data['email'] . "\">" . $data['email'] . "</a></td>";
				$csv .= '"' . str_replace( '"', '""', $data['email'] ) . '",';

				foreach( explode( ',', $agency['fields'] ) as $field )
				{
					$mssg .= "\r\n<td nowrap=\"nowrap\">" . $submission[$this->convert_to_fieldname( $field )] . "</td>";
					$csv .= '"' . str_replace( '"', '""', $submission[$this->convert_to_fieldname( $field )] ) . '",';
				}

				$mssg .= "\r\n<td nowrap=\"nowrap\">" . $data['course'] . "</td>";
				$csv .= '"' . str_replace( '"', '""', $data['course'] ) . '",';

				$mssg .= "\r\n<td nowrap=\"nowrap\">" . $data['results_ratio'] . "</td>";
				$csv .= '"' . str_replace( '"', '""', $data['results_ratio'] ) . '",';

				$mssg .= "\r\n<td nowrap=\"nowrap\">" . $data['results_percentage'] . "%</td>";
				$csv .= '"' . str_replace( '"', '""', $data['results_percentage'] ) . '%",';

				$mssg .= "\r\n<td nowrap=\"nowrap\"><a href=\"" . site_url( $this->uri_base('exams/certificate/' . $data['logid'] )) . "\">" . $dt->format('d/m/Y H:i') . "</a></td>";
				$csv .= '"' . str_replace( '"', '""', $dt->format('Y-m-d H:i:00') ) . '",';

				$mssg .= "\r\n<td nowrap=\"nowrap\">" . $data['duration'] . "</td>";
				$csv .= '"' . str_replace( '"', '""', $data['duration'] ) . "\"\n";

				$csv .= '"' . str_replace( '"', '""', site_url( $this->uri_base('exams/certificate/' . $data['logid'] )) ) . "\"\n";

				$mssg .= "\r\n</tr>";

				$logids[] = $data['logid'];
				$i++;
			}

			$mssg .= "\r\n</tbody>";
			$mssg .= "\r\n</table>";

			$mssg .= "\r\n<p>This report shows all submissions received or approved since last digest on " . $digest_last->format('l, F jS, Y \a\t g:i a') . ". Your agency has requested this email be sent to you every day. Have the most recent submissions for your agency emailed to you at anytime by <a href=\"" . site_url( $this->uri_base('exams/digest/' . $agency['digestpin'] )) . "\">visiting this webpage</a>.</p>";
			$mssg .= "\r\n<p>You're receiving this email by official request of " . $agency['name'] . ". To have each submission emailed to you as they happen, change the frequency of these messages, or change the address these emails are sent to, please have your agency contact <a href=\"mailto:webmaster@kcsara.org\">webmaster@kcsara.org</a>.</p>";
			$mssg .= $this->config->item('exams.email_signature');

			$mssg .= "<p>&nbsp;</p>";
			$mssg .= "\r\n</body>";
			$mssg .= "\r\n</html>";

			$email = new My_PHPMailer();

			$email->Subject		= $subj;
			$email->Body		= $mssg;
			$email->AltBody		= 'To view the message, please use an HTML compatible email viewer.';

			$email->SetFrom($this->config->item('exams.do-not-reply_address'), $this->config->item('exams.do-not-reply_name'));
			$email->AddAddress($to);
			$email->addStringAttachment($csv, $now->format('YmdHis') . "_report.csv");

			if ( $email->Send() )
			{
				$this->exams_m->mark_senttoagency( $logids, $now );
				$this->exams_m->mark_lastdigest( $agency['agencyid'], $now );
				return '<p>Report sent to ' . $agency['name'] . '.</p>';
			}
			else
			{
				return '<p>An error was encountered while sending the report for ' . $agency['name'] . '. Please report this to <a href="mailto:webmaster@kcsara.org">webmaster@kcsara.org</a>.</p>';
			}

		}
		else
		{
			return '<p>No submissions made for ' . $agency['name'] . ' since last digest on ' . $digest_last->format('l, F jS, Y \a\t g:i a') . '.</p>';
		}
	}

	public function digest()
    {
    	$agency_pin = $this->uri_segment(3);
    	
    	if ( $agency_pin == 'all' )
    	{
			$agencies = $this->exams_m->get_digest_agencies();
    	}
    	else
    	{
    		$agencies = $this->exams_m->get_agency( $agency_pin );
    	}

    	if ( $agencies )
    	{
    		$output = array();
			foreach( $agencies as $agency )
			{
				$output[] = $this->send_digest( $agency );
			}

			$this->template
				->title( 'Exams Report' )
				->append_css('module::exams.css')
				->set('output', $output)
				->build('report');
    	}
    	else
    	{
    		redirect($this->uri_base('exams'));
    	}
     }

}