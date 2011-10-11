<?php
/*
 * Fixed (8/3/2010 17:25)
 * # Line number can not display
 * # PHP parse action not correct in non php language file (add "<" problem)
 *
 * Fixed (7/3/2010 23:12)
 * # Content base64 decode string no clear the dash
 */
require_once 'system/init.php';

class Index extends Controller {
	public function __construct() {
		parent::__construct();

		$sk = isset($this->params['sk']) ? $this->params['sk'] : null;

		switch($sk) {
			case 'save':
				$this->save();
				break;
			default:
				$this->index();
				break;
		}
	}

	private function index() {
		if (empty($this->params['id'])) {
			$language_list = array();
			foreach(glob(ROOT_PATH.'/library/geshi/*.php') as $file_name) {
				$file_info = pathinfo($file_name);
				$language_list[$file_info['basename']] = ucfirst(substr($file_info['basename'], 0, -4));
			}

			View::factory('index.html')->set('language_list', $language_list)
									   ->display();	
		}else{
			$id = intval($this->params['id']);
			$row = $this->db->getOne("SELECT content, language, add_date FROM content WHERE id = ".$id);

			if (empty($row['content'])) {
				Helper::show_message('Can not found out record: '.$id);
			}

			$language = strtolower($row['language']);
			$content = stripslashes(base64_decode($row['content']));
	
			if ($language == 'php' && substr($content, 2) == '<?') {
				$content = '<'.str_replace('<?','?', $content);
			}

			$geshi = new GeSHi($content, $language);
			$geshi->set_header_type(GESHI_HEADER_DIV);
			$geshi->enable_classes();
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 10);
			$geshi->set_tab_width(4);
			$geshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0; padding: 5px;', true);
			$geshi->set_line_style('font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;', 'font-weight: bold; color: #006060;', true);
			$geshi->set_code_style('color: #000020;', true);
			$geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
			$geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');

			View::factory('show.html')->set(array(
				'css' => $geshi->get_stylesheet(true),
				'content' => $geshi->parse_code(),
				'add_date' => Helper::date_format($row['add_date'], 'Y-m-d H:i:s (D)')
			))->display();
		}
	}

	private function save() {
		$content = $this->params['content'];
		$language = $this->params['language'];
		$back_to_url = $_SERVER['PHP_SELF'];
		$timestamp = time();

		$content = base64_encode($content);

		if (empty($content)) {
			Helper::show_message('Please enter your content !', ERR, $back_to_url);
		}

		if (empty($language)) {
			Helper::show_message('Please enter your language !', ERR, $back_to_url);
		}

		$this->db->query("
			INSERT INTO content 
			(content, language, add_date) 
			VALUES 
			('$content', '$language', '$timestamp')
		");

		Helper::redirect(sprintf("show/%d", $this->db->getLastId()));
	}
}

new Index();
?>