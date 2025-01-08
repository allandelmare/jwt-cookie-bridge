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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    public function enqueue_admin_styles($hook): void {
        if ($hook !== 'settings_page_jwt-cookie-bridge') {
            return;
        }

        wp_enqueue_style(
            'jwt-cookie-bridge-admin',
            JWT_COOKIE_BRIDGE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            JWT_COOKIE_BRIDGE_VERSION
        );
    }

    private static function load_options(): array {
        if (self::$options === null) {
            self::$options = wp_parse_args(
                get_option(self::OPTION_NAME, []),
                [
                    'debug_mode' => false,
                    'samesite_policy' => 'Lax',
                    'http_only' => false,
                    'cookie_name' => 'mo_jwt',
                    'cookie_duration' => 3600,
                ]
            );
        }
        return self::$options;
    }

    public function add_settings_menu(): void {
        add_options_page(
            __('JWT Cookie Bridge Settings', 'jwt-cookie-bridge'),
            __('JWT Cookie Bridge', 'jwt-cookie-bridge'),
            'manage_options',
            'jwt-cookie-bridge',
            [$this, 'render_settings_page']
        );
    }

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
                    'http_only' => false,
                    'cookie_name' => 'mo_jwt',
                    'cookie_duration' => 3600,
                ]
            ]
        );

        add_settings_section(
            'jwt_cookie_bridge_main',
            __('Cookie Settings', 'jwt-cookie-bridge'),
            [$this, 'render_settings_description'],
            'jwt-cookie-bridge'
        );

        $this->add_settings_fields();
    }

    public function render_settings_description(): void {
        ?>
        <div class="jwt-settings-description">
            <p>
                <?php esc_html_e('Configure how JWT tokens from your Identity Provider are stored and managed in browser cookies.', 'jwt-cookie-bridge'); ?>
            </p>
            <div class="notice notice-info inline">
                <p>
                    <?php esc_html_e('These settings affect how your single sign-on (SSO) solution works across different domains and applications. Make sure to coordinate these settings with your security requirements.', 'jwt-cookie-bridge'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    private function add_settings_fields(): void {
        $fields = [
            'cookie_name' => [
                'label' => __('Cookie Name', 'jwt-cookie-bridge'),
                'type' => 'text',
                'default' => 'mo_jwt',
                'description' => __('The name of the cookie that will store the JWT token.', 'jwt-cookie-bridge')
            ],
            'cookie_duration' => [
                'label' => __('Cookie Duration', 'jwt-cookie-bridge'),
                'type' => 'number',
                'default' => 3600,
                'description' => __('How long (in seconds) the cookie should persist. Default is 3600 (1 hour). Should align with your token expiration time.', 'jwt-cookie-bridge')
            ],
            'samesite_policy' => [
                'label' => __('SameSite Policy', 'jwt-cookie-bridge'),
                'type' => 'select',
                'options' => self::VALID_SAMESITE_VALUES,
                'default' => 'Lax',
                'description' => sprintf(
                    '%s<br><br><strong>%s</strong> %s<br><strong>%s</strong> %s<br><strong>%s</strong> %s',
                    __('Controls how the cookie behaves in cross-site browsing contexts:', 'jwt-cookie-bridge'),
                    __('Lax:', 'jwt-cookie-bridge'),
                    __('Cookies are sent on GET requests to your domain. Best for most cases.', 'jwt-cookie-bridge'),
                    __('Strict:', 'jwt-cookie-bridge'),
                    __('Cookies only sent to your domain. Most secure but may break some functionality.', 'jwt-cookie-bridge'),
                    __('None:', 'jwt-cookie-bridge'),
                    __('Cookies sent everywhere. Requires secure context (HTTPS). Least secure.', 'jwt-cookie-bridge')
                )
            ],
            'http_only' => [
                'label' => __('HTTP Only', 'jwt-cookie-bridge'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('When enabled, prevents JavaScript access to the cookie. Highly recommended for security.', 'jwt-cookie-bridge')
            ],
            'debug_mode' => [
                'label' => __('Debug Mode', 'jwt-cookie-bridge'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Enables detailed logging and the debug dashboard. Only enable temporarily for troubleshooting.', 'jwt-cookie-bridge')
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
                    'default' => $field['default'],
                    'description' => $field['description']
                ]
            );
        }
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'jwt-cookie-bridge'));
        }
        ?>
        <div class="wrap jwt-settings-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="jwt-settings-container">
                <form action="options.php" method="post">
                    <?php
                    settings_fields(self::OPTION_NAME);
                    do_settings_sections('jwt-cookie-bridge');
                    submit_button(__('Save Settings', 'jwt-cookie-bridge'));
                    ?>
                </form>

                <div class="jwt-settings-sidebar">
                    <div class="jwt-help-widget">
                        <h3><?php esc_html_e('Need Help?', 'jwt-cookie-bridge'); ?></h3>
                        <ul>
                            <li>
                                <a href="https://github.com/allandelmare/jwt-cookie-bridge/wiki" target="_blank">
                                    <?php esc_html_e('Documentation', 'jwt-cookie-bridge'); ?> →
                                </a>
                            </li>
                            <li>
                                <a href="https://github.com/allandelmare/jwt-cookie-bridge/issues" target="_blank">
                                    <?php esc_html_e('Report Issues', 'jwt-cookie-bridge'); ?> →
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_text_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <input type="text" 
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text">
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo wp_kses_post($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    public function render_number_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <input type="number"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               min="0"
               step="1">
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo wp_kses_post($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    public function render_select_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>" 
                class="regular-text">
            <?php foreach ($args['options'] as $option): ?>
                <option value="<?php echo esc_attr($option); ?>"
                        <?php selected($value, $option); ?>>
                    <?php echo esc_html($option); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo wp_kses_post($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    public function render_checkbox_field(array $args): void {
        $options = self::load_options();
        $value = $options[$args['field']] ?? $args['default'];
        ?>
        <label class="jwt-toggle">
            <input type="checkbox"
                   name="<?php echo esc_attr(self::OPTION_NAME . '[' . $args['field'] . ']'); ?>"
                   <?php checked($value, true); ?>
                   value="1">
            <span class="jwt-toggle-slider"></span>
        </label>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo wp_kses_post($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

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