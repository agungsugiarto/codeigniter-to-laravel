<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
    /**
     * {@inheritdoc}
     */
    public function view($view, $vars = [], $return = false)
    {
        $output = (function () use ($view, $vars, $return): string {
            ob_start();
            parent::view($view, $vars);

            if ($return) {
                $buffer = ob_get_contents();
                ob_end_clean();

                return $buffer;
            }

            return ob_get_clean();
        })();

        if ($return) {
            return $output;
        }

        echo $output;
    }
}
