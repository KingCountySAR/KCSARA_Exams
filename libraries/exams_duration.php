<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Exams_Duration
{

	/*
	 * Example Usage:
	 * 	
	 * 	$duration = new Exams_Duration( $seconds );
	 * 	echo $duration->format();
	 * 
	 */
    
	public $days = 0;
	public $hours = 0;
	public $minutes = 0;
	public $seconds = 0;
    
	public function __construct( $seconds )
	{
		$remaining_seconds = $seconds;
		if ( $remaining_seconds >= 86400 )
		{
			$this->days = floor( $remaining_seconds / 86400 );
			$remaining_seconds = $remaining_seconds - ($this->days * 86400);
		}
		if ( $remaining_seconds >= 3600 )
		{
			$this->hours = floor( $remaining_seconds / 3600 );
			$remaining_seconds = $remaining_seconds - ($this->hours * 3600);
		}
		if ( $remaining_seconds >= 60 )
		{
			$this->minutes = floor( $remaining_seconds / 60 );
			$remaining_seconds = $remaining_seconds - ($this->minutes * 60);
		}
		if ( $remaining_seconds > 0 )
		{
			$this->seconds = $remaining_seconds;
		}
	}
    
	public function format()
	{
		$out = '';
		if ( $this->days == 1 ) $out .= $this->days . ' day ';
		if ( $this->days > 1 ) $out .= $this->days . ' days ';
		if ( $this->hours == 1 ) $out .= $this->hours . ' hour ';
		if ( $this->hours > 1 ) $out .= $this->hours . ' hours ';
		if ( $this->minutes == 1 ) $out .= $this->minutes . ' minute ';
		if ( $this->minutes > 1 ) $out .= $this->minutes . ' minutes ';
		if ( $this->seconds == 1 ) $out .= $this->seconds . ' second';
		if ( $this->seconds > 1 ) $out .= $this->seconds . ' seconds';
		return $out;
	}
}