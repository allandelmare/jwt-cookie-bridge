<?php
namespace ChristendomSSO;

class Settings_Manager {
    private const OPTION_GROUP = 'christendom_sso_settings';
    private const OPTION_NAME = 'christendom_sso_options';
    private const SETTINGS_PAGE = 'christendom-sso-settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'init_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            'Christendom SSO Settings',
            'Christendom SSO',
            'manage_options',
            self::SETTINGS_PAGE,
            [$this, 'render_settings_page']
        );
    }

    public function init_settings() {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            [
                'type' => 'array',
                'default' => [
                    'debug_mode' => false,
                    'samesite_policy' => 'Lax',
                    'http_only' => true
                ]
            ]
        );

        add_settings_section(
            'general_settings',
            'General Settings',
            [$this, 'render_section_info'],
            self::SETTINGS_PAGE
        );

        add_settings_field(
            'debug_mode',
            'Debug Mode',
            [$this, 'render_debug_mode_field'],
            self::SETTINGS_PAGE,
            'general_settings'
        );

        add_settings_field(
            'samesite_policy',
            'SameSite Cookie Policy',
            [$this, 'render_samesite_field'],
            self::SETTINGS_PAGE,
            'general_settings'
        );

        add_settings_field(
            'http_only',
            'HttpOnly Cookie Setting',
            [$this, 'render_http_only_field'],
            self::SETTINGS_PAGE,
            'general_settings'
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
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    public function render_section_info() {
        echo '<p>Configure the SSO plugin settings below.</p>';
    }

    public function render_debug_mode_field() {
        $options = get_option(self::OPTION_NAME);
        ?>
        <input type="checkbox" 
               id="debug_mode" 
               name="<?php echo self::OPTION_NAME; ?>[debug_mode]" 
               value="1" 
               <?php checked(isset($options['debug_mode']) && $options['debug_mode']); ?>>
        <label for="debug_mode">Enable debug mode</label>
        <?php
    }

    public function render_samesite_field() {
        $options = get_option(self::OPTION_NAME);
        $current = $options['samesite_policy'] ?? 'Lax';
        ?>
        <select id="samesite_policy" name="<?php echo self::OPTION_NAME; ?>[samesite_policy]">
            <option value="Strict" <?php selected($current, 'Strict'); ?>>Strict</option>
            <option value="Lax" <?php selected($current, 'Lax'); ?>>Lax</option>
            <option value="None" <?php selected($current, 'None'); ?>>None</option>
        </select>
        <p class="description">
            Strict: Highest security, might affect SSO functionality<br>
            Lax: Balanced security and functionality (recommended for SSO)<br>
            None: Lowest security, use only if required
        </p>
        <?php
    }

    public function render_http_only_field() {
        $options = get_option(self::OPTION_NAME);
        ?>
        <input type="checkbox" 
               id="http_only" 
               name="<?php echo self::OPTION_NAME; ?>[http_only]" 
               value="1" 
               <?php checked(isset($options['http_only']) && $options['http_only']); ?>>
        <label for="http_only">Enable HttpOnly cookie flag</label>
        <p class="description">
            When enabled, the cookie cannot be accessed through JavaScript (recommended for security).
            Disable only if you need client-side access to the token.
        </p>
        <?php
    }

    // Static methods to get settings values
    public static function get_option($key, $default = null) {
        $options = get_option(self::OPTION_NAME, []);
        return $options[$key] ?? $default;
    }

    public static function is_debug_enabled(): bool {
        return (bool) self::get_option('debug_mode', false);
    }

    public static function get_samesite_policy(): string {
        return self::get_option('samesite_policy', 'Lax');
    }

    public static function is_http_only(): bool {
        return (bool) self::get_option('http_only', true);
    }
}