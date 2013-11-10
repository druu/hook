<?php
class Update implements iHook {
    public static function run($args, $mail, stdClass $options) {
        $output = shell_exec('./update.sh 2>&1');
        return isset($options->type) && $options->type === 'html'
            ? '<pre>' . $output . '</pre>'
            : $output;
    }
}
