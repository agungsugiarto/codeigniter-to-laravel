<?php

namespace App\Legacy\Core;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property \CI_Benchmark        $benchmark
 * @property \CI_Config           $config
 * @property \CI_DB_query_builder $db
 * @property \CI_DB_forge         $dbforge
 * @property \CI_Input            $input
 * @property \CI_Lang             $lang
 * @property \CI_Loader           $loader
 * @property \CI_Log              $log
 * @property \CI_Output           $output
 * @property \CI_Router           $router
 * @property \CI_Security         $security
 * @property \CI_Session          $session
 * @property \CI_URI              $uri
 * @property \CI_Utf8             $utf8
 */
class CI_Controller
{
    /**
     * Reference to the CI singleton
     *
     * @var	object
     */
    private static $instance;

    /**
     * CI_Loader
     *
     * @var	\CI_Loader|\MY_Loader
     */
    public $load;

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
        self::$instance = &$this;

        // Assign all the class objects that were instantiated by the
        // bootstrap file (CodeIgniter.php) to local class variables
        // so that CI can run as one big super object.
        foreach (is_loaded() as $var => $class) {
            $this->{$var} = &load_class($class);
        }

        $this->load = &load_class('Loader', 'core');
        $this->load->initialize();
        log_message('info', 'Controller Class Initialized');
    }

    /**
     * Get the CI singleton.
     *
     * @return $this
     */
    public static function &get_instance()
    {
        return self::$instance;
    }
}