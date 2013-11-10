<?php
class Update implements iHook {
    public static function run($args, $mail, $options = array()) {
        $output = shell_exec('./update.sh 2>&1');
        mail($mail, "REPO UPDATED", $output);
    }
}
