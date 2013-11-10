<?php
class Err implements iHook {
	public static function run($args, $mail, stdClass $options) {
		return $args;  // Sorry, you're out!
	}
}
