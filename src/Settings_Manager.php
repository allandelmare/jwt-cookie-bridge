<?php
namespace JWTCookieBridge;

/**
 * Manages plugin settings and options
 */
class Settings_Manager {
    private const OPTION_NAME = 'jwt_cookie_bridge_options';
    private static $options = null;
    private const VALID_SAMESITE_VALUES = ['Lax', 'Strict', 'None'];

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Load plugin options
     *
     * @return array Plugin options
     */
    private static function load_options(): array {
        if (self::$options === null) {
            self::$options = wp_parse_args(
                get_option(self::OPTION_NAME, []),
                [
                    'debug_mode' => false,
                    'samesite_policy' => 'Lax',
                    'http_only' => true,
                    'cookie_name' => 'mo_jwt',
                    'cookie_duration' => 3600,
                ]
            );
        }
        return self::$options;
    }

    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_menu(): void {
        add_options_page(
            __('JWT Cookie Bridge Settings', 'jwt-cookie-bridge'),
            __('JWT Cookie Bridge', 'jwt-cookie-bridge'),
            'manage_options',
            'jwt-cookie-bridge',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings(): void {
        register_setting(
            self::OPTION_NAME,
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => [
                    'debug_mode' => false,
                    'samesite_policy' => 'Lax',
                    'http_only' => true,
                    'cookie_name' => 'mo_jwt',
                    'cookie_duration' => 3600,
                ]
            ]
        );

        add_settings_section(
            'jwt_cookie_bridge_main',
            __('Cookie Settings', 'jwt-cookie-bridge'),
            null,
            'jwt-cookie-bridge'
        );

        $this->add_settings_fields();
    }

    /**
     * Add settings fields to the form
     */
    private function add_settings_fields(): void {
        $fields = [
            'cookie_name' => [
                'label' => __('Cookie Name', 'jwt-cookie-bridge'),
                'type' => 'text',
                'default' => 'mo_jwt'
            ],
            'cookie_duration' => [
                'label' => __('Cookie Duration (seconds)', 'jwt-cookie-bridge'),
                'type' => 'number',
                'default' => 3600
            ],
            'samesite_policy' => [
                'label' => __('SameSite Policy', 'jwt-cookie-bridge'),
                'type' => 'select',
                'options' => self::VALID_SAMESITE_VALUES,
                'default' => 'Lax'
            ],
            'http_only' => [
                'label' => __('HTTP Only Cookie', 'jwt-cookie-bridge'),
                'type' => 'checkbox',
                'default' => true
            ],
            'debug_mode' => [
                'label' => __('Debug Mode', 'jwt-cookie-bridge'),
                'type' => 'checkbox',
                'default' => false
            ]
        ];

        foreach ($fields as $key => $field) {
            add_settings_field(
                $key,
                $field['label'],
                [$this, 'render_' . $field['type'] . '_field'],
                'jwt-cookie-bridge',
                'jwt_cookie_bridge_main',
                [
                    'field' => $key,
                    'options' => $field['options'] ?? null,
                    'default' => $field['default']
                ]
            );
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'jwt-cookie-bridge'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_NAME);
                do_settings_sections('jwt-cookie-bridge');
                submit_button(__('Save Settings', 'jwt-cookie-bridge'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render text field
     *
     * @param array $args Field arguments
     */
    public function render_text_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <input type="text" 
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text">
        <?php
    }

    /**
     * Render number field
     *
     * @param array $args Field arguments
     */
    public function render_number_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <input type="number"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text">
        <?php
    }

    /**
     * Render select field
     *
     * @param array $args Field arguments
     */
    public function render_select_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>">
            <?php foreach ($args['options'] as $option): ?>
                <option value="<?php echo esc_attr($option); ?>"
                        <?php selected($value, $option); ?>>
                    <?php echo esc_html($option); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render checkbox field
     *
     * @param array $args Field arguments
     */
    public function render_checkbox_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <input type="checkbox"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
               <?php checked($value, true); ?>
               value="1">
        <?php
    }

    /**
     * Sanitize settings
     *
     * @param array $input Settings input
     * @return array Sanitized settings
     */
    public function sanitize_settings(array $input): array {
        $sanitized = [];
        
        if (isset($input['cookie_name'])) {
            $sanitized['cookie_name'] = sanitize_text_field($input['cookie_name']);
        }
        
        if (isset($input['cookie_duration'])) {
            $sanitized['cookie_duration'] = absint($input['cookie_duration']);
        }
        
        if (isset($input['samesite_policy'])) {
            $sanitized['samesite_policy'] = in_array($input['samesite_policy'], self::VALID_SAMESITE_VALUES, true)
                ? $input['samesite_policy']
                : 'Lax';
        }
        
        $sanitized['http_only'] = !empty($input['http_only']);
        $sanitized['debug_mode'] = !empty($input['debug_mode']);
        
        return $sanitized;
    }

    // Static getter methods

    public static function is_debug_enabled(): bool {
        $options = self::load_options();
        return (bool) ($options['debug_mode'] ?? false);
    }

    public static function get_cookie_name(): string {
        $options = self::load_options();
        return sanitize_text_field($options['cookie_name'] ?? 'mo_jwt');
    }

    public static function get_samesite_policy(): string {
        $options = self::load_options();
        $policy = $options['samesite_policy'] ?? 'Lax';
        return in_array($policy, self::VALID_SAMESITE_VALUES, true) ? $policy : 'Lax';
    }

    public static function is_http_only(): bool {
        $options = self::load_options();
        return (bool) ($options['http_only'] ?? true);
    }

    public static function get_cookie_duration(): int {
        $options = self::load_options();
        return absint($options['cookie_duration'] ?? 3600);
    }
}