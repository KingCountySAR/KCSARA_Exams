<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exams_UUID4
{

	/*
	 * Example Usage:
	 * 
	 * 	$id = exams_uuid();
	 * 	echo $id;
	 * 
	 */

    public $value = '';

	public function __construct( $questions = array(), $answers = array() )
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

		$this->value = $str;
	}

	public function __toString()
	{
		return $this->value;
	}

}





