<?php
class Err implements iHook {
	public static function run($args, $mail, $options = array()) {
		@mail($mail, 'GitHook Error', $args);
	}
}