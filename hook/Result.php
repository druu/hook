<?php
final class Result { // Yup we're special... final and no interfacce! PAH!
    public static function run($hook, $content, $args, $mail, $options) {
        $args          = strlen($args) ? $args : 'run';
        $options       = is_object($options) ? $options : (object) $option;
        $markers       = array('#HOOK#', '#CONTENT#');
        $replacement   = array($hook, $content);
        $type          = isset($options->type) ? $options->type : 'plain';
        $template_name = isset($options->mail_tpl) ? $options->mail_tpl : 'default';
        $template      = file_get_contents(
            file_exists( $filename = DEPSPATH . 'mail_tpl' . DIRECTORY_SEPARATOR . $template_name . DIRECTORY_SEPARATOR . $type . '.php' )
            ? $filename                                           // Template found
            : str_replace($template_name, 'default', $filename)   // Fallback
        );

        $subject = $hook . '::' . $args . ' [' . date('d.m.Y H:i:s') . ']' ;
        $message = self::render($template, $markers, $replacement);

        $header  = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/' . $type . '; charset=UTF' . "\r\n";

        mail($mail, $subject, $message, $header);
    }

    protected static function render( $template, $markers, $replacement ) {
        return str_replace($markers, $replacement, $template);
    }
}
