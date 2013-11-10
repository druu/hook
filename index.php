<?php
// Because why the fuck not
interface iHook {
	public static function run($args, $mail, stdClass $options);
}

// Define essential paths
define('BASEPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('HOOKPATH', BASEPATH . 'hook' . DIRECTORY_SEPARATOR);
define('CONFPATH', BASEPATH . 'conf' . DIRECTORY_SEPARATOR);
define('DEPSPATH', BASEPATH . 'deps' . DIRECTORY_SEPARATOR);

// Get users and make sure the "error" user is declared
$users = json_decode(file_get_contents(CONFPATH . 'users.json'));
if (!$users || !$users->{'!error'}->mail || !file_exists(HOOKPATH . 'Err.php')) { die(); };
$errormail = $users->{'!error'}->mail ;

// Extract payload or die
$payload = json_decode(str_replace("\n", '\n', stripslashes($_POST['payload'])));
if (!(
	is_object($payload)
	&& (property_exists($payload, 'commits') || property_exists($payload, 'head_commit') )
	&& is_array($payload->commits)
)) { die(); }
$commits = empty($payload->commits) ? array($payload->head_commit) : $payload->commits;

foreach ($commits as $commit) {
	try {

		list($exec, $hook, $args) =  @explode(' ', @$commit->message, 3);
		if(strtoupper($exec) !== 'EXEC') { continue; }
		$hook = ucfirst(strtolower($hook));
		$args = trim($args);

		$user = $commit->committer->username;
		$mail = $users->$user->mail;
		$opts = $users->$user->options;

		if (!$user || !$mail) {
			throw new Exception(sprintf('Invalid user: %s, or email: %s', $user, $mail));
		}

		if (!$hook || !ctype_alpha($hook) || !file_exists(HOOKPATH . $hook . '.php')) {
			throw new Exception(sprintf('Invalid hook: %s', $hook));
		}

		require_once(HOOKPATH . $hook . '.php');
		if (!class_exists($hook)) {
			throw new Exception('Hook file found, but class not declared: %s', $hook);
		}

		$output = call_user_func_array(array($hook, 'run'), array($args, $mail, $opts));

		require_once(HOOKPATH . 'Result.php');
		Result::run($args, $mail, $opts);

	} catch (Exception $e) {
		$args = $e->getMessage() . "\n\nOriginal args: " . $args;
		require_once(HOOKPATH . 'Err.php');
		Err::run($args, $errormail);
	}
}
