<?php
class Err implements iHook {
	public static function run($args, $mail, stdClass $options) {
		@mail($mail, 'GitHook Error', $args);
	}
}
