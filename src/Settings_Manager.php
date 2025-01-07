<?php
namespace ChristendomSSO;

class Settings_Manager {
    private const OPTION_NAME = 'christendom_sso_options';
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public static function is_debug_enabled(): bool {
        $options = get_option(self::OPTION_NAME);
        return isset($options['debug_mode']) && $options['debug_mode'];
    }

    public static function is_http_only(): bool {
        $options = get_option(self::OPTION_NAME);
        return isset($options['http_only']) && $options['http_only'];
    }

    public static function get_samesite_policy(): string {
        $options = get_option(self::OPTION_NAME);
        return $options['samesite_policy'] ?? 'Lax';
    }

    public static function get_cookie_name(): string {
        $options = get_option(self::OPTION_NAME);
        return $options['cookie_name'] ?? 'sso_jwt';
    }

    public function add_settings_page() {
        add_options_page(
            'Christendom SSO Settings',
            'Christendom SSO',
            'manage_options',
            'christendom-sso-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(
            'christendom_sso_settings',
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings']
            ]
        );

        add_settings_section(
            'christendom_sso_main',
            'Main Settings',
            [$this, 'render_section_info'],
            'christendom-sso-settings'
        );

        add_settings_field(
            'cookie_name',
            'Cookie Name',
            [$this, 'render_cookie_name_field'],
            'christendom-sso-settings',
            'christendom_sso_main'
        );

        add_settings_field(
            'debug_mode',
            'Debug Mode',
            [$this, 'render_debug_mode_field'],
            'christendom-sso-settings',
            'christendom_sso_main'
        );

        add_settings_field(
            'http_only',
            'HTTP Only Cookie',
            [$this, 'render_http_only_field'],
            'christendom-sso-settings',
            'christendom_sso_main'
        );

        add_settings_field(
            'samesite_policy',
            'SameSite Policy',
            [$this, 'render_samesite_field'],
            'christendom-sso-settings',
            'christendom_sso_main'
        );
    }

    public function sanitize_settings($input) {
        $sanitized = [];
        
        $sanitized['cookie_name'] = sanitize_text_field($input['cookie_name'] ?? 'sso_jwt');
        $sanitized['debug_mode'] = isset($input['debug_mode']);
        $sanitized['http_only'] = isset($input['http_only']);
        
        $valid_policies = ['Strict', 'Lax', 'None'];
        $sanitized['samesite_policy'] = in_array($input['samesite_policy'], $valid_policies) 
            ? $input['samesite_policy'] 
            : 'Lax';
            
        return $sanitized;
    }

    public function render_section_info() {
        echo '<p>Configure the SSO integration settings below:</p>';
    }

    public function render_cookie_name_field() {
        $options = get_option(self::OPTION_NAME);
        $value = $options['cookie_name'] ?? 'sso_jwt';
        echo '<input type="text" name="' . self::OPTION_NAME . '[cookie_name]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">The name of the cookie used to store the JWT token. Default: sso_jwt</p>';
    }

    public function render_debug_mode_field() {
        $options = get_option(self::OPTION_NAME);
        echo '<input type="checkbox" name="' . self::OPTION_NAME . '[debug_mode]" ' . 
            checked(isset($options['debug_mode']) && $options['debug_mode'], true, false) . '>';
    }

    public function render_http_only_field() {
        $options = get_option(self::OPTION_NAME);
        echo '<input type="checkbox" name="' . self::OPTION_NAME . '[http_only]" ' . 
            checked(isset($options['http_only']) && $options['http_only'], true, false) . '>';
    }

    public function render_samesite_field() {
        $options = get_option(self::OPTION_NAME);
        $current = $options['samesite_policy'] ?? 'Lax';
        $policies = ['Strict', 'Lax', 'None'];
        
        echo '<select name="' . self::OPTION_NAME . '[samesite_policy]">';
        foreach ($policies as $policy) {
            echo '<option value="' . esc_attr($policy) . '" ' . 
                selected($current, $policy, false) . '>' . 
                esc_html($policy) . '</option>';
        }
        echo '</select>';
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
                settings_fields('christendom_sso_settings');
                do_settings_sections('christendom-sso-settings');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
}