<?php
class Imgur implements iHook {
	protected static $re_frontpage_posts = '~class="post".+?src="//([^"]+?)" title="([^<"]+?)[<"]~misu';

	public static function run($args, $mail, $options = array()) {
		if ($args === "frontpage") {
			return self::frontpage();
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
	
	protected static function frontpage() {
		header("Content-Type:text/html;charset=utf-8");
		
		$html = file_get_contents("http://www.imgur.com");
		
		if (!$html) {
			throw new Exception("Failed to fetch Imgur Homepage");
		}
		
		preg_match_all(self::$re_frontpage_posts, $html, $matches);
		
		if (!count($matches[1]) || !count($matches[2])) {
			throw new Exception("Failed to fetch Images");
		}
		
		$ct = 0;
		foreach ($matches[1] as $index => $thumbnail) {
			$title = $matches[2][$index];
			$rev = strrev($thumbnail);
			
			$url = 'http://' . strrev(str_replace('.b', '.', $rev));
			$ext = strrev(preg_replace('~\..*~', '', $rev));
			
			
			echo '<h1>' . html_entity_decode($title) .'</h1>';
			echo '<img src="data:' . self::imagemime($ext) . ";base64," . base64_encode(file_get_contents($url)) . '" />';
			
			
			if($ct++ > 3) die();
		}
		
		
		die();
	}
}