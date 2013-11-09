<?php
class Testy implements iHook {
	public static function run($args, $mail, $options = array()) {
		@mail($mail, 'Nice!', "Geht: " . $args);
	}
}