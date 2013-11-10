<?php
// FUCK DEBUGGING
ini_set('display_errors',1); error_reporting(-1);
ob_start();
function die_goddammit( $msg ) { echo "DIED!".PHP_EOL.PHP_EOL; mail('david@druul.in', 'USER FILE ERROR', $msg); }
var_dump($_POST); echo str_repeat(PHP_EOL, 5);

// Because why the fuck not
interface iHook {
	public static function run($args, $mail, $options = array());
}

$basepath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$hookpath = $basepath . 'hook' . DIRECTORY_SEPARATOR;
$confpath = $basepath . 'conf' . DIRECTORY_SEPARATOR;

// Get users and make sure the "error" user is declared
$users = json_decode(file_get_contents($confpath . 'users.json'));
if (!$users || !@$users->{'!error'}->mail || !file_exists($hookpath . 'Err.php')) { die_goddammit( ob_get_clean() ); };
$errormail = $users['!error']['mail'] ;

// Extract payload or die
$payload = @json_decode(str_replace("\n", '\n', stripslashes(@$_POST['payload'])));
var_dump($payload);
if (!(
	is_object($payload)
	&& (property_exists($payload, 'commits') || property_exists($payload, 'head_commit') )
	&& is_array($payload->commits)
)) { die_goddammit( ob_get_clean() );  }
$commits = empty($payload->commits) ? array($payload->head_commit) : $payload->commits;

foreach ($commits as $commit) {
	try {

		list($hook, $args) =  @explode(' ', @$commit->message, 2);
		$hook = ucfirst(strtolower($hook));
		$args = trim($args);

		$user = $commit->committer->username;
		$mail = $users->$user->mail;
		$opts = $users->$user->options;

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
		require_once($hookpath . 'Err.php');
		Err::run($args, $errormail);
		die_goddammit(ob_get_clean());
		die('YUP we\'ve gone here...');
	}
}
