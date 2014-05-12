<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Exams extends Module {

	public $version = '1.0';

    public function __construct()
    {
        parent::__construct();

        $this->config->load('exams/exams');
        $this->template->active_section = 'exams';
    }

	/* 
	 * info() is a required method inside this class.
	 * returns an array with basic information about your module.
	 */
	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Exams'
			),
			'description' => array(
				'en' => 'Adds a customizable list of online exams to your site.'
			),
			'frontend' => TRUE,
			'backend' => TRUE,
			'menu' => 'content', // You can also place modules in their top level menu. For example try: 'menu' => 'Sample',
			'sections' => array(
				'exams' => array(
					'name' 	=> 'exams:exams_title', // These are translated from your language file
					'uri' 	=> 'admin/exams',
					'shortcuts' => array(
						'create' => array(
						'name' 	=> 'exams:create_title',
						'uri' 	=> 'admin/exams/create'
						)
					)
				),
				'agencies' => array(
					'name' 	=> 'exams:agencies_title', // These are translated from your language file
					'uri' 	=> 'admin/exams/agencies',
					'shortcuts' => array(
						'create' => array(
						'name' 	=> 'exams:create_agency_title',
						'uri' 	=> 'admin/exams/agencies/new'
						)
					)
				),
				'submissions' => array(
					'name' 	=> 'exams:submissions_title', // These are translated from your language file
					'uri' 	=> 'admin/exams/submissions',
					'shortcuts' => array(
						'create' => array(
						'name' 	=> 'exams:create_submission_title',
						'uri' 	=> 'admin/exams/submissions/new'
						)
					)
				)
			)
		);
	}

	/* 
	 * install() is a required method inside this class
	 * runs the queries for your database setup.
	 */
	public function install()
	{

		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->dbforge->drop_table($_table_prefix . 'log');
		$this->dbforge->drop_table($_table_prefix . 'agencies');
		$this->dbforge->drop_table($_table_prefix . 'agency_quizzes');
		$this->dbforge->drop_table($_table_prefix . 'quizzes');
		$this->dbforge->drop_table($_table_prefix . 'queries');
		$this->dbforge->drop_table($_table_prefix . 'options');
		$this->dbforge->drop_table($_table_prefix . 'answers');

		$this->db->delete('settings', array('module' => 'exams'));

		$tables = array(
			$_table_prefix . 'log' => array(
				'logid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'agencyid' => array('type' => 'CHAR', 'constraint' => 64),
				'signature' => array('type' => 'CHAR', 'constraint' => 64),
				'datetime' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'),
				'senttoagency' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'),
				'fullname' => array('type' => 'LONGTEXT'),
				'email' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'course' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'results' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'duration' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'spamscore' => array('type' => 'INT', 'constraint' => 32, 'default' => '0'),
				'spamexplanation' => array('type' => 'TEXT'),
				'spamscoreoverride' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => '0'),
				'submission' => array('type' => 'LONGTEXT')
			),

			$_table_prefix . 'agencies' => array(
				'agencyid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'name' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'email' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'fields' => array('type' => 'TEXT'),
				'digest' => array('type' => 'INT', 'constraint' => 64, 'default' => '0'),
				'digestlast' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'),
				'digestpin' => array('type' => 'CHAR', 'constraint' => 64)
			),

			$_table_prefix . 'agency_quizzes' => array(
				'quizid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'agencyid' => array('type' => 'CHAR', 'constraint' => 64)
			),

			$_table_prefix . 'quizzes' => array(
				'quizid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'name' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
				'description' => array('type' => 'TEXT'),
				'version' => array('type' => 'CHAR', 'constraint' => 32, 'default' => ''),
				'published' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => '0'),
				'enabled' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => '1')
			),

			$_table_prefix . 'queries' => array(
				'queryid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'quizid' => array('type' => 'CHAR', 'constraint' => 64),
				'text' => array('type' => 'LONGTEXT')
			),

			$_table_prefix . 'options' => array(
				'optionid' => array('type' => 'CHAR', 'constraint' => 64, 'primary' => true),
				'queryid' => array('type' => 'CHAR', 'constraint' => 64),
				'text' => array('type' => 'LONGTEXT'),
				'correct' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => '1')
			),

		);


		$module_settings = array(
			'slug' => 'exams_setting',
			'title' => 'Exams Setting',
			'description' => 'A Yes or No option for the Exams module',
			'`default`' => '1',
			'`value`' => '1',
			'type' => 'select',
			'`options`' => '1=Yes|0=No',
			'is_required' => 1,
			'is_gui' => 1,
			'module' => 'exams'
		);

		if( $this->install_tables($tables) AND
		   $this->db->insert('settings', $module_settings) )
		{

			if( $this->load_sample_data() )
			{
				return TRUE;
			}
		}
	}

	/* 
	 * uninstall() is a required method inside this class.
	 * cleans up your database, and returns true if successful.
	 */
	public function load_sample_data()
	{

		$_table_prefix = $this->config->item('exams._table_prefix');

		$sql = 'INSERT INTO `' . $this->db->dbprefix($_table_prefix . 'agencies') . '` (`agencyid`, `name`, `email`, `fields`)
				VALUES
					("cb573805-9add-45dc-a68f-4c56c545b261","KCSARA","webmaster@kcsara.org","ID");';
		$this->db->query($sql);

		$sql = 'INSERT INTO `' . $this->db->dbprefix($_table_prefix . 'agency_quizzes') . '` (`quizid`, `agencyid`)
				VALUES
					("ae480dda-13b3-4cf5-9da9-57e23047fef4","cb573805-9add-45dc-a68f-4c56c545b261");';
		$this->db->query($sql);

		$sql = 'INSERT INTO `' . $this->db->dbprefix($_table_prefix . 'options') . '` (`optionid`, `queryid`, `text`, `correct`)
				VALUES
					("002f1fa6-a579-46bb-a71f-50fe6fb3ffb2","fab3c4e3-2e4e-4875-ac29-a853e432f757","Physical – sign, clothing, equipment, litter, fire pits, disturbed vegetation, scrape or scuff marks.","1"),
					("18ae1e10-ae48-441e-931c-2c86c9d2c584","fab3c4e3-2e4e-4875-ac29-a853e432f757","Recorded – trail registers, summit logs, trail mail","1"),
					("1b3441b4-0eda-4fff-9147-e984ae9a0d42","fab3c4e3-2e4e-4875-ac29-a853e432f757","People – witnesses, the point last seen","1"),
					("84633c49-7a9b-4558-98c9-7327eec03834","fab3c4e3-2e4e-4875-ac29-a853e432f757","Events – light, smoke, sound, transmissions","1");';
		$this->db->query($sql);

		$sql = 'INSERT INTO `' . $this->db->dbprefix($_table_prefix . 'queries') . '` (`queryid`, `quizid`, `text`)
				VALUES
					("fab3c4e3-2e4e-4875-ac29-a853e432f757","ae480dda-13b3-4cf5-9da9-57e23047fef4","Provide examples of clues in a search and rescue operation (check all that apply)");';
		$this->db->query($sql);

		$sql = 'INSERT INTO `' . $this->db->dbprefix($_table_prefix . 'quizzes') . '` (`quizid`, `name`, `description`, `version`)
				VALUES
					("ae480dda-13b3-4cf5-9da9-57e23047fef4","Clue Awareness","Meets v80 standard for the written portion of Clue Awareness","1.0");';
		$this->db->query($sql);

		return TRUE;

	}

	/* 
	 * uninstall() is a required method inside this class.
	 * cleans up your database, and returns true if successful.
	 */
	public function uninstall()
	{

		$_table_prefix = $this->config->item('exams._table_prefix');

		$this->dbforge->drop_table($_table_prefix . 'log');
		$this->dbforge->drop_table($_table_prefix . 'quizzes');
		$this->dbforge->drop_table($_table_prefix . 'queries');
		$this->dbforge->drop_table($_table_prefix . 'options');
		$this->dbforge->drop_table($_table_prefix . 'answers');
		$this->dbforge->drop_table($_table_prefix . 'agency_quizzes');
		$this->dbforge->drop_table($_table_prefix . 'agencies');

		$this->db->delete('settings', array('module' => 'exams'));
		{
			return TRUE;
		}
	}

	/* 
	 * help() is a required method inside this class.
	 * returns a html markup string with help for your module.
	 */
	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return 'No documentation has been added for this module.<br />Contact the <a href="mailto:webmaster@kcsara.org">module developer</a> for assistance.';
	}


	/* 
	 * upgrade() is an optional method inside this class
	 */
	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}
}
/* End of file details.php */
