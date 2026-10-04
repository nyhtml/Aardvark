<?php
/*
Plugin Name: Aardvark Professional
Plugin URI: https://github.com/nyhtml/aardvark
Description: The <strong>Aardvark</strong> offers security, along with custom shortcodes that feature inline styling and responsive design.
Version: 5.5.0
Author: Stephan Pringle
Author URI: https://www.stephanpringle.com
Contributors: nyhtml
Text Domain: aardvark
License: AGPL-3.0 or later
License URI: https://www.gnu.org/licenses/agpl-3.0.en.html
*/

if (!defined('ABSPATH')) exit;

define('AARDVARK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AARDVARK_PLUGIN_URL', plugin_dir_url(__FILE__));

// --- Load required files ---
require_once AARDVARK_PLUGIN_DIR . 'plugin/phpVersion.php'; // 1.0.1
require_once AARDVARK_PLUGIN_DIR . 'plugin/securityRestBlock.php'; // 1.0.0
require_once AARDVARK_PLUGIN_DIR . 'plugin/securityXMLRPC.php'; // 1.5.0
require_once AARDVARK_PLUGIN_DIR . 'shortcode/cardNetworks.php';  // 1.0.1
require_once AARDVARK_PLUGIN_DIR . 'shortcode/cardResume.php'; // 1.0.1 
require_once AARDVARK_PLUGIN_DIR . 'shortcode/cardSkill.php'; // 1.0.1
require_once AARDVARK_PLUGIN_DIR . 'shortcode/itemcv.php'; // 1.1.0

// --- Admin Menu ---
add_action('admin_menu', 'aardvark_admin_menu');
function aardvark_admin_menu() {
    add_menu_page(
        'Aardvark Professional&trade;',
        'Aardvark Pro',
        'manage_options',
        'aardvark-settings',
        'aardvark_settings_page',
        'dashicons-shield'
    );
}

// --- Add Settings link on Plugins page ---
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'aardvark_custom_shortcodes_settings_link');
function aardvark_custom_shortcodes_settings_link($links) {
    $update_link = '<a title="Update" href="admin.php?page=aardvark-settings&tab=download">Update</a>';
    $settings_link = '<a title="Settings" href="admin.php?page=aardvark-settings">Settings</a>';
    array_unshift($links, $settings_link, $update_link);
    return $links;
}

// --- Register Settings ---
add_action('admin_init', 'aardvark_register_settings');
function aardvark_register_settings() {
    register_setting('aardvark_settings_group', 'aardvark_facebook');
    register_setting('aardvark_settings_group', 'aardvark_twitter');
    register_setting('aardvark_settings_group', 'aardvark_linkedin');
    register_setting('aardvark_settings_group', 'aardvark_github');
    register_setting('aardvark_settings_group', 'aardvark_youtube');
    register_setting('aardvark_settings_group', 'aardvark_instagram');
    register_setting('aardvark_settings_group', 'aardvark_pinterest');
    register_setting('aardvark_pro_group', 'aardvark_php_version_display');
	register_setting('aardvark_pro_group', 'aardvark_itemcv_enabled');
    register_setting('aardvark_secure_group', 'aardvark_rest_api_block');
    register_setting('aardvark_secure_group', 'aardvark_xmlrpc_block');
}

// --- Settings Page ---
function aardvark_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'help';
    ?>
    <div class="wrap">
        <h1>🛡️ Aardvark Pro&trade; v<?php echo esc_html(get_plugin_data(__FILE__)['Version']); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=aardvark-settings&tab=help" class="nav-tab <?php echo ($active_tab == 'help') ? 'nav-tab-active' : ''; ?>">ℹ️ Help</a>
            <a href="?page=aardvark-settings&tab=settings" class="nav-tab <?php echo ($active_tab == 'settings') ? 'nav-tab-active' : ''; ?>">⚙️ Settings</a>
            <a href="?page=aardvark-settings&tab=social" class="nav-tab <?php echo ($active_tab == 'social' || $active_tab == 'default') ? 'nav-tab-active' : ''; ?>">👥 Platforms</a>
            <a href="?page=aardvark-settings&tab=secure" class="nav-tab <?php echo ($active_tab == 'secure') ? 'nav-tab-active' : ''; ?>">👮 Security</a>
            <a href="?page=aardvark-settings&tab=download" class="nav-tab <?php echo ($active_tab == 'download') ? 'nav-tab-active' : ''; ?>">💾 Download</a>
            <a href="?page=aardvark-settings&tab=reset" class="nav-tab <?php echo ($active_tab == 'reset') ? 'nav-tab-active' : ''; ?>">🔄 Reset</a>
        </h2>

        <?php
        // --- Settings Tab ---
        if ($active_tab == 'settings') : ?>
            <h1>⚙️ Aardvark Professional Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('aardvark_pro_group'); ?>
                <table class="form-table">
                    <tr>
                        <th>⚡ Enable PHP Version Display</th>
                        <td>
                            <input type="checkbox" name="aardvark_php_version_display" value="1" <?php checked(1, get_option('aardvark_php_version_display'), true); ?> />
                            Show PHP &amp; MySQL versions on <a title="Dashboard" href="<?php echo admin_url('index.php'); ?>">Dashboard</a>.
                        </td>
                    </tr>

            <tr>
                <th>📄 Enable Legacy CV Shortcode</th>
                <td>
                    <input type="checkbox"
                           name="aardvark_itemcv_enabled"
                           value="1"
                           <?php checked(1, get_option('aardvark_itemcv_enabled'), true); ?> />
                    Enable <code>[itemcv]</code> compatibility for legacy CV/resume content.
                </td>
            </tr>

                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ($active_tab == 'social') : ?>
            <h1>👥 Social Media Platforms</h1>
            <form method="post" action="options.php">
                <?php settings_fields('aardvark_settings_group'); ?>
                <table class="form-table">
                    <?php
                    $platforms = ['facebook','twitter','linkedin','github','youtube','instagram','pinterest'];
                    foreach($platforms as $p) : ?>
                        <tr>
                            <th><?php echo ucfirst($p); ?> URL or Username</th>
                            <td>
                                <input type="text" name="aardvark_<?php echo $p; ?>" value="<?php echo esc_attr(get_option('aardvark_'.$p)); ?>" placeholder="e.g. username or URL">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ($active_tab == 'secure') : ?>
            <h1>👮 Security Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('aardvark_secure_group'); ?>
                <table class="form-table">
                    <tr>
                        <th>⛔ Block User REST Endpoint</th>
                        <td>
                            <input type="checkbox" name="aardvark_rest_api_block" value="1" <?php checked(1, get_option('aardvark_rest_api_block'), true); ?> />
                            Disable REST API endpoint
                        </td>
                    </tr>
                    <tr>
                        <th>🔒 Block XML-RPC</th>
                        <td>
                            <input type="checkbox" name="aardvark_xmlrpc_block" value="1" <?php checked(1, get_option('aardvark_xmlrpc_block'), true); ?> />
                            Disable XML-RPC endpoint in .htaccess.
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ($active_tab == 'help') : ?>
            <h1>ℹ️ Help & Notes</h1>
            <div class="postbox">
                <h2 class="hndle"><span>🔒 XML-RPC Blocking</span></h2>
                <div class="inside">
                    <p>This <a href="<?php echo admin_url('admin.php?page=aardvark-settings&tab=secure'); ?>">security</a> feature of the plugin will insert its own <code># Aardvark Security — Block XML-RPC</code> into your <code>.htaccess</code> file.</p>
                </div>
            </div>
            <div class="postbox">
                <h2 class="hndle"><span>🧼 Reset Feature</span></h2>
                <div class="inside">
                    <p>Use the <a href="admin.php?page=aardvark-settings&tab=reset">Reset</a> tab to remove all saved options...</p>
                </div>
            </div>

        <?php elseif ($active_tab == 'download') : ?>
            <h1>💾 Downloads</h1>
            <div class="postbox">
                <h2 class="hndle"><span>📌 Version</span></h2>
                <div class="inside">
                    <p>Aardvark Professional v<?php echo esc_html(get_plugin_data(__FILE__)['Version']); ?></p>
                    <p>View the <a title="changelog" href="https://github.com/nyhtml/Aardvark/blob/main/CHANGELOG.md" target="_blank" rel="noopener noreferrer">changelog</a> for <a href="https://github.com/nyhtml/Aardvark/" target="_blank" rel="noopener noreferrer">Aardvark</a> on GitHub</p>
                </div>
            </div>

        <?php elseif ($active_tab == 'reset') : ?>
            <?php
            // Handle reset form submission
            if (isset($_POST['aardvark_reset_confirm'])) {
                $options = ['facebook','twitter','linkedin','github','youtube','instagram','pinterest','php_version_display','rest_api_block','xmlrpc_block'];
                foreach ($options as $opt) {
                    delete_option('aardvark_'.$opt);
                }
                echo '<div class="updated"><p>Aardvark plugin data has been deleted.</p></div>';
            }
            ?>
            <h1>🔄 Aardvark Reset Settings</h1>
            <form method="post">
                <p><strong>Clicking the button below will delete all Aardvark settings from the database.<br>
                ⚠ This action does NOT <a href="plugins.php?s=aardvark&plugin_status=all">deactivate</a> the plugin.<br>
                ⚠ This action does NOT <a href="plugins.php?s=aardvark&plugin_status=inactive">uninstall</a> the plugin.<br>
                ⚠ This action cannot be undone.</strong></p>
                <input type="hidden" name="aardvark_reset_confirm" value="1">
                <?php submit_button('Reset Plugin Data', 'delete'); ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

// --- Custom Admin Menu Styling ---
add_action('admin_head', 'aardvark_custom_admin_menu_styles');
function aardvark_custom_admin_menu_styles() {
    ?>
    <style>
        /* Top-level menu: Aardvark Pro - green */
        #adminmenu .toplevel_page_aardvark-settings > a {
            color: #ffffff !important;
            font-weight: bold;
        }
        /* Tabs submenu - green */
        #adminmenu .toplevel_page_aardvark-settings ul.wp-submenu li a[href*="page=aardvark-settings"]:not([href*="aardvark-reset"]) {
            color: #27ae60 !important;
            font-weight: bold;
        }
        /* Reset submenu - red */
        #adminmenu .toplevel_page_aardvark-settings ul.wp-submenu li a[href*="aardvark-reset"] {
            color: #e74c3c !important;
            font-weight: bold;
        }
    </style>
    <?php
}
