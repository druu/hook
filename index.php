<?php
// DEBUG ALL THE THINGS
function custom_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    ob_start();
    var_dump(func_get_args());

    global $payload;

    mail($payload->head_commit->author->email, "DEBUG MESSAGE", ob_get_clean());
}

// Because why the fuck not
interface iHook {
	public static function run($args, $mail, $options = array());
}

$basepath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$hookpath = $basepath . 'hook' . DIRECTORY_SEPARATOR;
$confpath = $basepath . 'conf' . DIRECTORY_SEPARATOR;

// Get users and make sure the "error" user is declared
$users = @json_decode(@file_get_contents($confpath . 'users.json'), true);
if (!$users || !@$users['!error']['mail'] || !file_exists($hookpath . 'Err.php')) die();
$errormail = $users['!error']['mail'] ;

// Extract payload or die
$payload = @json_decode(@$_POST['payload'], true) or die();
if (!is_array($commits = @$payload['commits']) || !sizeof($commits)) die();

foreach ($commits as $commit) {
	try {

		list($hook, $args) =  @explode(' ', @$commit['message'], 2);
		$hook = ucfirst(strtolower($hook));
		$args = trim($args);

		$user = @$commit['committer']['username'];
		$mail = @$users[$user]['mail'];
		$opts = @$users[$user]['options'];

		if (!$user || !$mail) {
			throw new Exception(sprintf('Invalid user: %s, or email: %s', $user, $mail));
		}

		if (!$hook || !ctype_alpha($hook) || !file_exists($hookpath . $hook . '.php')) {
			throw new Exception(sprintf('Invalid hook: %s', $hook));
		}

		require_once($hookpath . $hook . '.php');
		if (!class_exists($hook)) {
			throw new Exception('Hook file found, but class not declared: %s', $hook);
		}

		call_user_func_array(array($hook, 'run'), array($args, $mail, $opts));

	} catch (Exception $e) {
		$args = $e->getMessage() . "\n\nOriginal args: " . $args;
		require_once($hookpath . 'err.php');
		Err::run($args, $errormail);
	}
}
