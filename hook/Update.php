<?php
class Update implements iHook {
    public static function run($args, $mail, $options = array()) {
        $output = shell_exec('./update.sh 2>&1');
        mail($payload->head_commit->author->email, "REPO UPDATED", $output);
    }
}
