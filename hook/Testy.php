<?php
class Testy implements iHook {
	public static function run($args, $mail, stdClass $options) {
		return 'Nice! Geht: ' . $args;
	}
}
