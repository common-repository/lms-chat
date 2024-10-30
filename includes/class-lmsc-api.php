<?php

if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('LMSC_Api')){
class LMSC_Api
{


    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $instance = null;

    /**
     * The version number.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $version;
    /**
     * The token.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $token;

    public function __construct()
    {
        $this->token = LMSC_TOKEN;

        add_action(
            'rest_api_init',
            function () {
                
                // Get Config from DB
                register_rest_route(
                    $this->token . '/v1',
                    '/config/',
                    array(
                        'methods' => 'GET',
                        'callback' => array($this, 'getConfig'),
                        'permission_callback' => array($this, 'getPermission'),
                    )
                );



                // Save Config to DB
                register_rest_route(
                    $this->token . '/v1',
                    '/save/',
                    array(
                        'methods' => 'POST',
                        'callback' => array($this, 'setConfig'),
                        'permission_callback' => array($this, 'getPermission'),
                    )
                );

            }
        );
    }


    /**
     * @access  public
     * @param   post array 
     * @return  success message
     */
    public function setConfig($data){
        $config = $data['config'];
        update_option( 'lmsc_config', $config, 'yes' );

        $return = array(
            'msg' => 'success'
        );
        return new WP_REST_Response($return, 200);
    }


    /**
     * @access  public
     * @return  config from DB
     */
    public function getConfig()
    {
        $config = get_option( 'lmsc_config', array() );
        return new WP_REST_Response($config, 200);
    }

    /**
     *
     * Ensures only one instance of APIFW is loaded or can be loaded.
     *
     * @param string $file Plugin root path.
     * @return Main APIFW instance
     * @see WordPress_Plugin_Template()
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Permission Callback
     **/
    public function getPermission()
    {
        if (current_user_can('administrator') || current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }
}
}