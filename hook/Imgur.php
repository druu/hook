<?php
class Imgur implements iHook {
	protected static $re_frontpage_posts = '~class="post".+?src="//([^"]+?)" title="([^<"]+?)[<"]~misu';

	public static function run($args, $mail, stdClass $options) {
		list($method, $args) = explode(' ', $args, 2);
		$method = strlen($method) ? $method: 'frontpage';

		if (is_callable('self::' . $method)) {
			$matches = self::fetch_images($args);
			return call_user_func_array(array('self', $method), array($matches));
		}
		else {
			throw new Exception('Unrecognised method: ' . $method . '()');
		}
	}

	protected static function imagemime($ext) {
		switch (strtolower($ext)) {
			case 'gif':
				return 'image/gif'; break;
			case 'png':
				return 'image/png'; break;
			case 'jpg':
			case 'jpeg':
			default:
				return 'image/jpeg';
		}
	}


	protected static function frontpage( $matches ) {
		$ct = 0;
		$output = '';
		foreach ($matches[1] as $index => $thumbnail) {
			$output .= self::render_image($thumbnail, $matches[2][$index]);

			if($ct++ > 3) break;
		}

		return $output;
	}

	protected static function fetch_images ($id = null) {
		$url = "http://www.imgur.com" . (is_null($id) ? '' : '/' . $id);
		$html = file_get_contents($url);

		if (!$html) {
			throw new Exception("Failed to fetch Imgur Homepage");
		}

		preg_match_all(self::$re_frontpage_posts, $html, $matches);

		if (!count($matches[1]) || !count($matches[2])) {
			throw new Exception("Failed to fetch Images");
		}

		return $matches;
	}

	protected static function render_image($thumbnail, $title) {
		$rev = strrev($thumbnail);

		$url = 'http://' . strrev(str_replace('.b', '.', $rev));
		$ext = strrev(preg_replace('~\..*~', '', $rev));

		$output  = '<h1>' . html_entity_decode($title) .'</h1>';
		$output .= '<img src="data:' . self::imagemime($ext) . ";base64," . base64_encode(file_get_contents($url)) . '" />';
		return $output;

	}

	protected function image($matches) {
		return self::render_image($matches[1][0] ,$matches[2][0]);
	}
}
