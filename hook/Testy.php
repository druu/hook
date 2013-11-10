<?php
class Testy implements iHook {
	public static function run($args, $mail, stdClass $options) {
		@mail($mail, 'Nice!', "Geht: " . $args);
	}
}
