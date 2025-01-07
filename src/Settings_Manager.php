<?php
namespace JWTCookieBridge;

class Settings_Manager {
    private static $options = null;
    private const OPTION_NAME = 'jwt_cookie_bridge_options';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    private static function load_options() {
        if (self::$options === null) {
            self::$options = get_option(self::OPTION_NAME, [
                'debug_mode' => false,
                'samesite_policy' => 'Lax',
                'http_only' => true,
                'cookie_name' => 'mo_jwt',
                'cookie_duration' => 3600,
                'allowed_domains' => ''
            ]);
        }
        return self::$options;
    }

    public function add_settings_menu() {
        add_options_page(
            'JWT Cookie Bridge Settings',
            'JWT Cookie Bridge',
            'manage_options',
            'jwt-cookie-bridge',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(self::OPTION_NAME, self::OPTION_NAME, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);

        add_settings_section(
            'jwt_cookie_bridge_main',
            'Cookie Settings',
            null,
            'jwt-cookie-bridge'
        );

        add_settings_field(
            'cookie_name',
            'Cookie Name',
            [$this, 'render_text_field'],
            'jwt-cookie-bridge',
            'jwt_cookie_bridge_main',
            ['field' => 'cookie_name', 'default' => 'mo_jwt']
        );

        add_settings_field(
            'cookie_duration',
            'Cookie Duration (seconds)',
            [$this, 'render_number_field'],
            'jwt-cookie-bridge',
            'jwt_cookie_bridge_main',
            ['field' => 'cookie_duration', 'default' => 3600]
        );

        add_settings_field(
            'samesite_policy',
            'SameSite Policy',
            [$this, 'render_select_field'],
            'jwt-cookie-bridge',
            'jwt_cookie_bridge_main',
            [
                'field' => 'samesite_policy',
                'options' => ['Lax' => 'Lax', 'Strict' => 'Strict', 'None' => 'None'],
                'default' => 'Lax'
            ]
        );

        add_settings_field(
            'http_only',
            'HTTP Only Cookie',
            [$this, 'render_checkbox_field'],
            'jwt-cookie-bridge',
            'jwt_cookie_bridge_main',
            ['field' => 'http_only', 'default' => true]
        );

        add_settings_field(
            'debug_mode',
            'Debug Mode',
            [$this, 'render_checkbox_field'],
            'jwt-cookie-bridge',
            'jwt_cookie_bridge_main',
            ['field' => 'debug_mode', 'default' => false]
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
                settings_fields(self::OPTION_NAME);
                do_settings_sections('jwt-cookie-bridge');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    public function render_text_field($args) {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        echo '<input type="text" name="' . esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']') . 
             '" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_number_field($args) {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        echo '<input type="number" name="' . esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']') . 
             '" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_select_field($args) {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        echo '<select name="' . esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']') . '">';
        foreach ($args['options'] as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . 
                 esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function render_checkbox_field($args) {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']') . 
             '" ' . checked($value, true, false) . ' value="1">';
    }

    public function sanitize_settings($input) {
        $sanitized = [];
        
        if (isset($input['cookie_name'])) {
            $sanitized['cookie_name'] = sanitize_text_field($input['cookie_name']);
        }
        
        if (isset($input['cookie_duration'])) {
            $sanitized['cookie_duration'] = absint($input['cookie_duration']);
        }
        
        if (isset($input['samesite_policy'])) {
            $sanitized['samesite_policy'] = in_array($input['samesite_policy'], ['Lax', 'Strict', 'None']) 
                ? $input['samesite_policy'] 
                : 'Lax';
        }
        
        $sanitized['http_only'] = isset($input['http_only']);
        $sanitized['debug_mode'] = isset($input['debug_mode']);
        
        return $sanitized;
    }

    // Static getter methods for settings
    public static function is_debug_enabled(): bool {
        $options = self::load_options();
        return $options['debug_mode'] ?? false;
    }

    public static function get_cookie_name(): string {
        $options = self::load_options();
        return $options['cookie_name'] ?? 'mo_jwt';
    }

    public static function get_samesite_policy(): string {
        $options = self::load_options();
        return $options['samesite_policy'] ?? 'Lax';
    }

    public static function is_http_only(): bool {
        $options = self::load_options();
        return $options['http_only'] ?? true;
    }

    public static function get_cookie_duration(): int {
        $options = self::load_options();
        return $options['cookie_duration'] ?? 3600;
    }
}