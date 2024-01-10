<?php

const CI_VERSION = '3.1.13';

(static function () {
    define('ENVIRONMENT', $_SERVER['APP_ENV'] ?? 'local');

    switch (ENVIRONMENT) {
        case 'development':
        case 'local':
            error_reporting(-1);
            ini_set('display_errors', 1);
            break;

        case 'testing':
        case 'production':
            ini_set('display_errors', 0);
            if (version_compare(PHP_VERSION, '5.3', '>=')) {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            } else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
            }
            break;

        default:
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'The application environment is not set correctly.';

            exit(1); // EXIT_ERROR
    }

    $application_folder = __DIR__ . '/../application';

    $fcpath_folder = __DIR__ . '/../';

    $public_path = __DIR__ . '/../public';

    $system_path = __DIR__ . '/../vendor/codeigniter/framework/system';

    $view_folder = '';

    if (defined('STDIN')) {
        chdir($fcpath_folder);
    }

    if (($_temp = realpath($system_path)) !== false) {
        $system_path = $_temp . DIRECTORY_SEPARATOR;
    } else {
        $system_path = strtr(
            rtrim($system_path, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    if (!is_dir($system_path)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: ' . pathinfo(__FILE__, PATHINFO_BASENAME);

        exit(3); // EXIT_CONFIG
    }

    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

    // Path to the system directory
    define('BASEPATH', $system_path);

    // Path to the front controller (this file) directory
    define('FCPATH', strtr(
        rtrim($fcpath_folder, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    ) . DIRECTORY_SEPARATOR);

    // Path to the public directory
    define('PUBLICPATH', strtr(
        rtrim($public_path, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    ) . DIRECTORY_SEPARATOR);

    // Name of the "system" directory
    define('SYSDIR', basename(BASEPATH));

    // The path to the "application" directory
    if (is_dir($application_folder)) {
        if (($_temp = realpath($application_folder)) !== false) {
            $application_folder = $_temp;
        } else {
            $application_folder = strtr(
                rtrim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
            );
        }
    } elseif (is_dir(BASEPATH . $application_folder . DIRECTORY_SEPARATOR)) {
        $application_folder = BASEPATH . strtr(
            trim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    } else {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: ' . self;

        exit(3); // EXIT_CONFIG
    }

    define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);

    // The path to the "views" directory
    if (!isset($view_folder[0]) && is_dir(APPPATH . 'views' . DIRECTORY_SEPARATOR)) {
        $view_folder = APPPATH . 'views';
    } elseif (is_dir($view_folder)) {
        if (($_temp = realpath($view_folder)) !== false) {
            $view_folder = $_temp;
        } else {
            $view_folder = strtr(
                rtrim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
            );
        }
    } elseif (is_dir(APPPATH . $view_folder . DIRECTORY_SEPARATOR)) {
        $view_folder = APPPATH . strtr(
            trim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    } else {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: ' . self;

        exit(3); // EXIT_CONFIG
    }

    define('VIEWPATH', $view_folder . DIRECTORY_SEPARATOR);

    if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
        require_once APPPATH . 'config/' . ENVIRONMENT . '/constants.php';
    }

    if (file_exists(APPPATH . 'config/constants.php')) {
        require_once APPPATH . 'config/constants.php';
    }

    require_once BASEPATH . 'core/Common.php';

    if (!is_php('5.4')) {
        ini_set('magic_quotes_runtime', 0);

        if ((bool) ini_get('register_globals')) {
            $_protected = [
                '_SERVER',
                '_GET',
                '_POST',
                '_FILES',
                '_REQUEST',
                '_SESSION',
                '_ENV',
                '_COOKIE',
                'GLOBALS',
                'HTTP_RAW_POST_DATA',
                'system_path',
                'application_folder',
                'view_folder',
                '_protected',
                '_registered',
            ];

            $_registered = ini_get('variables_order');

            foreach (['E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER'] as $key => $superglobal) {
                if (strpos($_registered, $key) === false) {
                    continue;
                }

                foreach (array_keys(${$superglobal}) as $var) {
                    if (isset($GLOBALS[$var]) && !in_array($var, $_protected, true)) {
                        $GLOBALS[$var] = null;
                    }
                }
            }
        }
    }

    set_error_handler('_error_handler');
    set_exception_handler('_exception_handler');
    register_shutdown_function('_shutdown_handler');

    if (!empty($assign_to_config['subclass_prefix'])) {
        get_config(['subclass_prefix' => $assign_to_config['subclass_prefix']]);
    }

    if ($composer_autoload = config_item('composer_autoload')) {
        if ($composer_autoload === true) {
            file_exists(FCPATH . 'vendor/autoload.php')
                ? require_once(FCPATH . 'vendor/autoload.php')
                : log_message('error', '$config[\'composer_autoload\'] is set to TRUE but ' . FCPATH . 'vendor/autoload.php was not found.');
        } elseif (file_exists($composer_autoload)) {
            require_once $composer_autoload;
        } else {
            log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: ' . $composer_autoload);
        }
    }

    $BM = &load_class('Benchmark', 'core');
    $BM->mark('total_execution_time_start');
    $BM->mark('loading_time:_base_classes_start');

    $EXT = &load_class('Hooks', 'core');
    $EXT->call_hook('pre_system');

    $CFG = &load_class('Config', 'core');

    if (isset($assign_to_config) && is_array($assign_to_config)) {
        foreach ($assign_to_config as $key => $value) {
            $CFG->set_item($key, $value);
        }
    }

    $charset = strtoupper(config_item('charset'));
    ini_set('default_charset', $charset);

    if (extension_loaded('mbstring')) {
        define('MB_ENABLED', true);
        // mbstring.internal_encoding is deprecated starting with PHP 5.6
        // and it's usage triggers E_DEPRECATED messages.
        @ini_set('mbstring.internal_encoding', $charset);
        // This is required for mb_convert_encoding() to strip invalid characters.
        // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
        mb_substitute_character('none');
    } else {
        define('MB_ENABLED', false);
    }

    // There's an ICONV_IMPL constant, but the PHP manual says that using
    // iconv's predefined constants is "strongly discouraged".
    if (extension_loaded('iconv')) {
        define('ICONV_ENABLED', true);
        // iconv.internal_encoding is deprecated starting with PHP 5.6
        // and it's usage triggers E_DEPRECATED messages.
        @ini_set('iconv.internal_encoding', $charset);
    } else {
        define('ICONV_ENABLED', false);
    }

    if (is_php('5.6')) {
        ini_set('php.internal_encoding', $charset);
    }

    require_once BASEPATH . 'core/compat/mbstring.php';
    require_once BASEPATH . 'core/compat/hash.php';
    require_once BASEPATH . 'core/compat/password.php';
    require_once BASEPATH . 'core/compat/standard.php';

    $UNI = &load_class('Utf8', 'core');

    $URI = &load_class('URI', 'core');

    $RTR = &load_class('Router', 'core', $routing ?? null);

    $OUT = &load_class('Output', 'core');

    if ($EXT->call_hook('cache_override') === false && $OUT->_display_cache($CFG, $URI) === true) {
        exit;
    }

    $SEC = &load_class('Security', 'core');

    $IN = &load_class('Input', 'core');

    $LANG = &load_class('Lang', 'core');

    function &get_instance()
    {
        return \App\Legacy\Core\CI_Controller::get_instance();
    }

    if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
        require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
    }

    $BM->mark('loading_time:_base_classes_end');

    $EXT->call_hook('pre_controller');

    $BM->mark('controller_execution_time_( '.$RTR->class.' / '.$RTR->method.' )_start');

    $EXT->call_hook('post_controller_constructor');

    $BM->mark('controller_execution_time_( '.$RTR->class.' / '.$RTR->method.' )_end');

    $EXT->call_hook('post_controller');

    if ($EXT->call_hook('display_override') === FALSE) {
        $OUT->_display();
    }

    $EXT->call_hook('post_system');
})();
