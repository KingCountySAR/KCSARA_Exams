KCSARA_Exams
============

A custom exams module for kcsara.org, built for PyroCMS.

## DEPENDENCIES ##
KCSARA_Exams has several dependencies that it expects to be in the libraries
directory of PyroCMS. These dependencies include the following.

### [PHPMailer](https://github.com/PHPMailer/PHPMailer) ###

Used to provide email-based feedback to the visitor and administrators. the 
PHPMailer directory has an accompaning custom file named `my_phpmailer.pdf`.
This file extends the PHPMailer class, allowing access by PyroCMS.

	if (!defined('BASEPATH')) exit('No direct script access allowed');

	require_once('PHPMailer/class.phpmailer.php');

	class My_PHPMailer extends PHPMailer
	{

	}

### [FPDF](http://www.fpdf.org) ###

Used to create the PDF-based completion-certificates. The `fpdf`-directory resides
alongside a custom file named `pdf.php`. This file extends the FPDF class, and 
allows an access point for PyroCMS.

	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	define("FPDF_FONTPATH", SHARED_ADDONPATH . "libraries/fpdf/font/");

	require('fpdf/fpdf.php');

	class Pdf extends FPDF
	{
		// Extend FPDF using this class
		// More at fpdf.org -> Tutorials

		function __construct($orientation='P', $unit='mm', $size='A4')
		{
			// Call parent constructor
			parent::__construct($orientation,$unit,$size);
		}
	}

### [SixtySix_Heuristics](mailto:webmaster@kcsara.org) ###

This is a custom built Turing test, that stays out of the way of visitors. Until we
place this in a public repository. please email us for the code.





