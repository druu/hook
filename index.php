<?php
// Because why the fuck not
interface iHook {
	public static function run($args, $mail, define('OPTIONS', array()));
}

// Define essential paths
define('BASEPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('HOOKPATH', BASEPATH . 'hook' . DIRECTORY_SEPARATOR);
define('CONFPATH', BASEPATH . 'conf' . DIRECTORY_SEPARATOR);
define('DEPSPATH', BASEPATH . 'deps' . DIRECTORY_SEPARATOR);

// Get users and make sure the "error" user is declared
define('USERS', json_decode(file_get_contents(CONFPATH . 'users.json')));
if (!$users || !$users->{'!error'}->mail || !file_exists(HOOKPATH . 'Err.php')) { die(); };
define('ERRORMAIL', $users->{'!error'}->mail );

// Extract payload or die
define('PAYLOAD', json_decode(str_replace("\n", '\n', stripslashes($_POST['payload']))));
if (!(
	is_object($payload)
	&& (property_exists($payload, 'commits') || property_exists($payload, 'head_commit') )
	&& is_array($payload->commits)
)) { die(); }
define('COMMITS', empty($payload->commits) ? array($payload->head_commit) : $payload->commits);

foreach ($commits as $commit) {
	try {

		list($exec, $hook, $args) =  @explode(' ', @$commit->message, 3);
		if(strtoupper($exec) !== 'EXEC') { continue; }
		define('HOOK', ucfirst(strtolower($hook)));
		define('ARGS', trim($args));

		define('USER', $commit->committer->username);
		define('MAIL', $users->$user->mail);
		define('OPTS', $users->$user->options);

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

		define('OUTPUT', call_user_func_array(array($hook, 'run'), array($args, $mail, $opts)));

		require_once(HOOKPATH . 'Result.php');
		Result::run($args, $mail, $opts);

	} catch (Exception $e) {
		define('ARGS', $e->getMessage() . "\n\nOriginal args: " . $args);
		require_once(HOOKPATH . 'Err.php');
		Err::run($args, $errormail);
	}
}
