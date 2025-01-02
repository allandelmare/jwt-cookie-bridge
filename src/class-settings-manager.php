<?php
namespace ChristendomSSO;

class Settings_Manager {
    private const OPTION_NAME = 'christendom_sso_settings';
    
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_menu']);
    }

    public function register_settings() {
        register_setting('christendom_sso', self::OPTION_NAME);
        
        add_settings_section(
            'christendom_sso_main',
            'SSO Settings',
            [$this, 'section_callback'],
            'christendom-sso-settings'
        );

        add_settings_field(
            'debug_mode',
            'Debug Mode',
            [$this, 'debug_mode_callback'],
            'christendom-sso-settings',
            'christendom_sso_main'
        );
    }

    public function section_callback() {
        echo '<p>Configure SSO integration settings.</p>';
    }

    public function debug_mode_callback() {
        $options = get_option(self::OPTION_NAME, ['debug_mode' => false]);
        $debug_enabled = isset($options['debug_mode']) && $options['debug_mode'];
        ?>
        <label>
            <input type="checkbox" name="<?php echo self::OPTION_NAME; ?>[debug_mode]" value="1"
                   <?php checked($debug_enabled); ?>>
            Enable debug mode
        </label>
        <?php
    }

    public function add_settings_menu() {
        add_options_page(
            'Christendom SSO Settings',
            'Christendom SSO',
            'manage_options',
            'christendom-sso-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('christendom_sso');
                do_settings_sections('christendom-sso-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function is_debug_enabled(): bool {
        $options = get_option(self::OPTION_NAME, ['debug_mode' => false]);
        return isset($options['debug_mode']) && $options['debug_mode'];
    }
}