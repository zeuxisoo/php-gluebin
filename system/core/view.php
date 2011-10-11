<?php
define('FILE_NOT_FOUND', 1);
define('CONTENT_IS_EMPTY', 2);

class View {

	private $view_folder		= 'template';
	private $view_cache_folder	= 'template_c';
	private $theme				= '';
	private $word_wrap			= true;
	
	private $view_content;
	private $view_file_path;
	private $view_file_cached_path;

	private static $instance = null;

	public function __construct($view_file_name, $theme) {
		$this->theme = empty($theme) ? 'default' : $theme;

		$this->view_file_path = $this->view_folder.'/'.$this->theme.'/'.$view_file_name;
		$this->view_file_cached_path = $this->view_cache_folder.'/'.$this->theme.'/'.$view_file_name.'.php';

		if (is_file($this->view_file_path) === false || file_exists($this->view_file_path) === false) {
			$this->error(FILE_NOT_FOUND);
		}else{

			if (file_exists($this->view_file_cached_path) && (filemtime($this->view_file_path) <= filemtime($this->view_file_cached_path))) {
				return $this;
			}else{
				$this->view_content = file_get_contents($this->view_file_path);

				if (strlen(trim($this->view_content)) <= 0) {
					$this->error(CONTENT_IS_EMPTY);
				}else{

					$view_file_cached_theme_folder = $this->view_cache_folder.'/'.$this->theme;

					if (file_exists($view_file_cached_theme_folder) === false) {
						mkdir($view_file_cached_theme_folder, 0777);
					}
					unset($view_file_cached_theme_folder);

					$this->compile();

					return $this;
				}
			}
		
		}
	}

	public static function factory($view_file_name, $theme = null) {
		return new View($view_file_name, $theme);
	}

	public function display() {
		if (is_array($this->view_local_data) === true) {
			extract($this->view_local_data, EXTR_OVERWRITE);
		}

		include_once $this->view_file_cached_path;
	}

	public function set($name, $value = null) {
		if (is_array($name)) {
			foreach($name as $k => $v) {
				$this->view_local_data[$k] = $v;
			}
		}else{
			$this->view_local_data[$name] = $value;
		}

		return $this;
	}

	private function error($exception) {
		$message = '';

		switch($exception) {
			case FILE_NOT_FOUND:
				$message = "File not found";
				break;
			case CONTENT_IS_EMPTY:
				$message = "File content is empty";
		}

		exit($message);
	}

	private function compile() {
		$var = '(\$[a-zA-Z_][a-zA-Z0-9_\->\.\[\]\'\$\(\)]*)';

		$search  = array(
					'#{(\$[a-zA-Z_][a-zA-Z0-9_\->\.\[\]\'\$\(\)]*)}#s',
					'#{set:(.+?)}#i',
					'#<!--{include:(.*?)}-->#i',
					'#<!--{func:(.*?)}-->#i',
					'#<!--{call:(.*?)}-->#i',
					'#<!--{foreach:(\S+)\s+(\S+)\s+(\S+)\}-->#i',
					'#<!--{foreach:(\S+)\s+(\S+)}-->#i',
					'#<!--{for:(.*?)\;(.*?)\;(.*?)}-->#i',
					'#<!--{if:(.*?)}-->#i',
					'#<!--{elseif:(.*?)}-->#i',
					'#<!--{else}-->#i',
					'#<!--{if:(.*?):(.*?):(.*?)}-->#i',
				);

		$replace = array(
					'<?php echo \1; ?>',
					'<?php \1; ?>',
					'<?php include_once \'\1\'; ?>',
					'<?php \1; ?>',
					'<?php echo \1; ?>',
					'<?php if (is_array(\1)) { foreach(\1 as \2 => \3) { ?>',
					'<?php if (is_array(\1)) { foreach(\1 as \2) { ?>',
					'<?php for(\1;\2;\3) { ?>',
					'<?php if (\1) { ?>',
					'<?php } elseif (\1) { ?>',
					'<?php } else { ?>',
					'<?php echo (\1) ? \2 : \3; ?>',
				);

		$this->view_content = preg_replace($search, $replace, $this->view_content);

		$search2 = array(
					'#<!--{/foreach}-->#i',
					'#<!--{/for}-->#i',
					'#<!--{/if}-->#i',
				);

		$replace2= array(
					'<?php } } ?>',
					'<?php } ?>',
					'<?php } ?>',
				);

		$this->view_content = preg_replace($search2, $replace2, $this->view_content);
		$this->view_content = $this->word_wrap === true ? preg_replace("/([\n|\r|\r\n|\t]+)/s", "", $this->view_content) : $this->view_content;
		$this->save();
	}

	private function save() {
		file_put_contents($this->view_file_cached_path, $this->view_content);
	}

}
?>