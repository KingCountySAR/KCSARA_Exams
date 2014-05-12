<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exams_Results
{

	/*
	 * Example Usage:
	 * 
	 *	$results = Exam_Results( 
	 *		questions array( array( queryid, query_text, optionid, correct?) ),
	 *		answers array( optionid )
	 *	);
	 * 
	 *	$results->score;
	 *	$results->number_correct;
	 *	$results->number_incorrect;
	 *	$results->incorrect_queries;
	 *	$results->query_count;
	 */
    
    public $query_count = 0;
    public $number_correct = 0;
    public $number_incorrect = 0;
    public $score = 0;
    public $incorrect_queries = array();

	public function __construct( $questions = array(), $answers = array() )
	{
		// print_r( $questions );
		if( $questions )
		{
			$wrong = array();
			$answer_states = array();

			foreach ( $questions as $query )
			{

				if ( ! array_key_exists( $query['queryid'], $answer_states) )
				{
					$answer_states[$query['queryid']] = 1;
					$this->query_count++;
				}

				$p = array_search( $query['optionid'], $answers[$query['queryid']] ) === FALSE ? 0 : 1;
				$q = $query['correct'];

				if( $answer_states[$query['queryid']] == 1 and $p != $q )
				{
					$answer_states[$query['queryid']] = 0;
					$wrong[] = '"' . $query['query_text'] . '"';
				}

			}
			$this->number_correct = 0;
			foreach ( $answer_states as $response )
			{
				$this->number_correct = $this->number_correct + $response;
			}
			$this->score = round( $this->number_correct / $this->query_count * 100, 2 );
			$this->incorrect_queries = $wrong;
		}
	}

}





