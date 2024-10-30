<?php

/**
 * Load Backend related actions
 *
 * @class   LMSC_Backend
 */

if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('LMSC_Backend')){
class LMSC_Backend
{
    /**
     * settings from db
     * @var     object
     * @access  private
     */
    private $settings;

    /**
     * Class intance for singleton  class
     *
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

    /**
     * The main plugin file.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * The plugin assets URL.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    /**
     * The plugin hook suffix.
     *
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $hook_suffix = array();


    /**
     * Constructor function.
     *
     * @access  public
     * @param string $file plugin start file path.
     * @since   1.0.0
     */
    public function __construct($file = '')
    {
        $this->version = LMSC_VERSION;
        $this->token = LMSC_TOKEN;
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $plugin = plugin_basename($this->file);
        $this->set_init();

        if(LMSC_Helper()->is_any_cms_activated()){
            // add action links to link to link list display on the plugins page.
            add_filter("plugin_action_links_$plugin", array($this, 'pluginActionLinks'));

            // reg activation hook.
            register_activation_hook($this->file, array($this, 'install'));
            // reg deactivation hook.
            register_deactivation_hook($this->file, array($this, 'deactivation'));

            // reg admin menu.
            add_action('admin_menu', array($this, 'lmsc_register_root_page'), 30);

            // Init functions, you can use it for posttype registration and all.


            // enqueue scripts & styles.
            add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'adminEnqueueStyles'), 10, 1);

            //Metabox on course for teacher control
            add_action( 'add_meta_boxes', array($this, 'lmsc_course_metabox_callback') );
            add_action( 'save_post', array( $this, 'lmsc_save_course') );

        }else{
            add_action( 'admin_notices', array($this, 'notice_need_a_cms_plugin') );
        }
    }



    private function set_init(){
        $settings = get_option( 'lmsc_config', array() );
        $this->settings = $settings;
    }

    
    /**
     * @access  public 
     * @return  NULL 
     * @desc    Save custom metabox data to DB
     */
    public function lmsc_save_course($post_id){
            // Check if our nonce is set.
            if ( ! isset( $_POST['lmsc_course_nonce'] ) ) {
                return $post_id;
            }
            $nonce = $_POST['lmsc_course_nonce'];

            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $nonce, 'lmsc_course' ) ) {
                return $post_id;
            }

            /*
            * If this is an autosave, our form has not been submitted,
            * so we don't want to do anything.
            */
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }

            // Check the user's permissions.
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }

            // Sanitize the user input.
            $allow_conversation = sanitize_text_field( $_POST['allow_conversation'] );

            // Update the meta field.
            update_post_meta( $post_id, 'allow_conversation', $allow_conversation );
    }


    /**
     * @access  public 
     * @return  metabox on course page 
     * @desc    add metabox for control conversation on frontend for each course by course teacher / author
     */
    public function lmsc_course_metabox_callback(){
        if(isset($this->settings['allow_tacher_capability']) && $this->settings['allow_tacher_capability']){
            add_meta_box(
                'lmsc_metabox',             // Unique ID
                'LMS Conversation',    // Box title
                array($this, 'lmsc_course_metabox_controller_callback'),    // Content callback, must be of type callable
                array('sfwd-courses', 'course', 'lp_course', 'stm-courses', 'courses'),       // Post type
                'side'
            ); 
        }
    }



    /**
     * @access  public
     * @return  html
     * @desc    LMS Course conversation controller UI
     */
    public function lmsc_course_metabox_controller_callback($post){
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'lmsc_course', 'lmsc_course_nonce' );
        require_once($this->dir . '/view/backend-metabox.php');
    }



    /**
     * Ensures only one instance of Class is loaded or can be loaded.
     *
     * @param string $file plugin start file path.
     * @return Main Class instance
     * @since 1.0.0
     * @static
     */
    public static function instance($file = '')
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($file);
        }
        return self::$instance;
    }


    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links.
     *
     * @return array
     */
    public function pluginActionLinks($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=' . $this->token . '-admin-ui/') . '">'
                . __('Configure', 'cms-conversation') . '</a>'
        );

        return array_merge($action_links, $links);
    }


    /**
     * Installation. Runs on activation.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
    }

    /**
     * WooCommerce not active notice.
     *
     * @access  public
     * @return void Fallack notice.
     */
    public function notice_need_a_cms_plugin()
    {
        /* translators: %s: Plugin Name. */
        ?>
            <div class="error">
                <p> <?php esc_attr_e( LMSC_PLUGIN_NAME, 'cms-conversation'); ?> <?php esc_attr_e( 'requires any LMS plugin like', 'cms-conversation' ); ?> 
                    <a target="_blank" href="https://wordpress.org/plugins/learnpress/"><?php esc_attr_e( 'Learnpress' ); ?></a>, 
                    <a href="https://www.learndash.com/" target="_blank"><?php esc_attr_e( 'Learndash' ); ?></a>, 
                    <a href="https://wordpress.org/plugins/sensei-lms/" target="_blank"><?php esc_attr_e( 'Sensei' ); ?></a>, 
                    <a href="https://wordpress.org/plugins/lifterlms/" target="_blank"><?php esc_attr_e( 'LifterLms' ); ?></a>, 
                    <a href="https://wordpress.org/plugins/masterstudy-lms-learning-management-system/" target="_blank"><?php esc_attr_e( 'Masterstudy' ); ?></a> & 
                    <a href="https://wordpress.org/plugins/tutor/" target="_blank"><?php esc_attr_e( 'Tutor LMS' ); ?></a> <?php esc_attr_e( 'to be installed & activated!', 'cms-conversation' ); ?>
                </p>
            </div>
        <?php
    }

    /**
     * Creating admin pages
     */
    public function lmsc_register_root_page()
    {
        $this->hook_suffix[] = add_menu_page(
            LMSC_PLUGIN_NAME,
            LMSC_PLUGIN_NAME,
            'manage_options',
            $this->token . '-admin-ui',
            array($this, 'adminUi'),
            'dashicons-format-chat', 
            50
        );
    }

    /**
     * Calling view function for admin page components
     */
    public function adminUi()
    {
        echo (
            '<div id="' . $this->token . '_ui_root">
              <div class="' . $this->token . '_loader"><p>' . __('Loading User Interface...', 'acowebs-plugin-boiler-plate-text-domain') . '</p></div>
            </div>'
        );
    }


    /**
     * Load admin CSS.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function adminEnqueueStyles()
    {
        wp_register_style($this->token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->version);
        wp_enqueue_style($this->token . '-admin');
    }

    /**
     * Load admin Javascript.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function adminEnqueueScripts()
    {
        global $current_user;
        if (!isset($this->hook_suffix) || empty($this->hook_suffix)) {
            return;
        }

        $screen = get_current_screen();

        if (in_array($screen->id, $this->hook_suffix, true)) {
            // Enqueue WordPress media scripts.
            if (!did_action('wp_enqueue_media')) {
                wp_enqueue_media();
            }

            if (!wp_script_is('wp-i18n', 'registered')) {
                wp_register_script('wp-i18n', esc_url($this->assets_url) . 'js/i18n.min.js', array(), $this->version, true);
            }
            // Enqueue custom backend script.
            wp_enqueue_script($this->token . '-backend', esc_url($this->assets_url) . 'js/backend.js', array('wp-i18n'), $this->version, true);
            // Localize a script.
            wp_localize_script(
                $this->token . '-backend',
                $this->token . '_object',
                array(
                    'api_nonce' => wp_create_nonce('wp_rest'),
                    'root' => rest_url($this->token . '/v1/'),
                    'assets_url' => $this->assets_url,
                    'email' => $current_user->user_email 
                )
            );
        }
    }

    /**
     * Deactivation hook
     */
    public function deactivation()
    {
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