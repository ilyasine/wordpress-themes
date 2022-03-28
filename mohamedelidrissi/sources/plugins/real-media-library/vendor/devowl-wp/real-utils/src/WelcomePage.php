<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\AbstractInitiator;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle welcome page for a specific plugin.
 */
class WelcomePage {
    use UtilsProvider;
    const PAGE_SUFFIX = '-welcome';
    const COLOR_BADGE_LITE = '#28aa00';
    const COLOR_BADGE_PRO = '#0073aa';
    const EXCLUDE_DESCRIPTION_CONTAINS = 'micro add-on';
    const PLUGIN_SLUG_FIXER = [
        'real-category-library-lite' => 'real-category-library',
        'real-media-library-lite' => 'real-media-library',
        'real-thumbnail-generator-lite' => 'real-thumbnail-generator',
        'real-cookie-banner' => 'real-cookie-banner-pro'
    ];
    private $initiator;
    /**
     * C'tor.
     *
     * @param AbstractInitiator $initiator
     * @codeCoverageIgnore
     */
    private function __construct($initiator) {
        $this->initiator = $initiator;
    }
    /**
     * Add a link to the welcome page.
     *
     * @param string[] $links
     * @param string $plugin
     */
    public function plugin_row_meta($links, $plugin) {
        if ($plugin === plugin_basename($this->getInitiator()->getPluginFile())) {
            $linkTemplate = '<a href="%s" target="%s">%s</a>';
            $links[] = \sprintf(
                $linkTemplate,
                $this->getWelcomePageLink(),
                '_self',
                __('Learn more about this plugin', REAL_UTILS_TD)
            );
            $links[] = \sprintf(
                $linkTemplate,
                $this->getInitiator()->getSupportLink(),
                '_blank',
                __('Support', REAL_UTILS_TD)
            );
            $links[] = \sprintf(
                $linkTemplate,
                $this->getInitiator()->getRateLink(),
                '_blank',
                __('Rate plugin', REAL_UTILS_TD)
            );
        }
        return $links;
    }
    /**
     * Redirect to the welcome page once the plugin is activated.
     *
     * @param string $plugin
     */
    public function activated_plugin($plugin) {
        $fromBulkAction = isset($_POST['action']) && $_POST['action'] === 'activate-selected';
        $isWpCli = \defined('WP_CLI') && \constant('WP_CLI');
        if (!$fromBulkAction && !$isWpCli && $plugin === plugin_basename($this->getInitiator()->getPluginFile())) {
            $alreadyRedirected = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::get(
                $this->getInitiator(),
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_REDIRECT_AFTER_ACTIVATE,
                \false
            );
            if (!$alreadyRedirected && wp_safe_redirect($this->getWelcomePageLink())) {
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::set(
                    $this->getInitiator(),
                    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_REDIRECT_AFTER_ACTIVATE,
                    \true
                );
                exit();
            }
        }
    }
    /**
     * Register welcome page without menu entry.
     */
    public function admin_menu() {
        $slug = $this->getInitiator()->getPluginSlug();
        $data = get_plugin_data($this->getInitiator()->getPluginFile());
        $title = $data['Name'];
        add_submenu_page(null, $title, $title, 'activate_plugins', $slug . self::PAGE_SUFFIX, [
            $this,
            'welcome_page_output'
        ]);
    }
    /**
     * Create output for the welcome page. It does not use React because the interface
     * is not reactive.
     */
    public function welcome_page_output() {
        $keyFeaturesCount = \count($this->getInitiator()->getKeyFeatures());
        $ourPluginsOutput = $this->our_plugins_output();
        $string =
            '<div class="wrap about-wrap full-width-layout" data-slug="%slug">
    <h1>%title</h1>
    <p class="about-text">%subtitle</p>
    <div class="wp-badge" style="background-image: url(\'%logoUrl\');">' .
            __('Version', REAL_UTILS_TD) .
            ' %version</div>

    <nav class="nav-tab-wrapper wp-clearfix">
        <a href="#" class="nav-tab nav-tab-active">' .
            __('Info', REAL_UTILS_TD) .
            '</a>
        <a target="_blank" href="%supportLink" class="nav-tab">' .
            __('Support', REAL_UTILS_TD) .
            '</a>
        <a target="_blank" href="%rateLink" class="nav-tab">' .
            __('Rate plugin', REAL_UTILS_TD) .
            '</a>
    </nav>

    <div class="about-wrap-content">
    	<div class="feature-section has-1-columns column-links">
            %heroButton
            <h2>' .
            _n('Key feature', 'Key features', $keyFeaturesCount, REAL_UTILS_TD) .
            '</h2>
    	</div>

		%featuresHtml
        
        <div class="feature-section has-1-columns column-links real-utils-newsletter-box">
            <h2>' .
            __('Newsletter', REAL_UTILS_TD) .
            '</h2>
            <p class="about-description">' .
            __('Receive the latest news about devowl.io WordPress plugins directly in your inbox', REAL_UTILS_TD) .
            '</p>
            <p>
                <input type="text" name="newsletter-email" value="%currentEmail" placeholder="' .
            __('Enter your email…', REAL_UTILS_TD) .
            '" class="large-text" />
                <div>
                    <label>
                        <input type="checkbox" name="newsletter-privacy" />
                        ' .
            \sprintf(
                // translators:
                __(
                    'I want to receive WordPress news from devowl.io via email and agree to the <a %s>privacy policy</a>.',
                    REAL_UTILS_TD
                ),
                'href="' . esc_attr(__('https://devowl.io/privacy-policy/', REAL_UTILS_TD)) . '" target="_blank"'
            ) .
            '</span>
                    </label>
                </div>
            </p>
            <a href="#" class="button button-primary button-hero">' .
            __('Subscribe', REAL_UTILS_TD) .
            '</a>
            <div class="hidden error-msg"></div>
        </div>
        %ourPluginsHeader
    </div>
</div>';
        $pluginData = get_plugin_data($this->getInitiator()->getPluginFile());
        $heroButton = $this->getInitiator()->getHeroButton();
        $heroButton = isset($heroButton)
            ? \sprintf(
                '<a href="%s" class="button button-primary button-hero" style="margin-top: 40px;"><strong>%s</strong></a>',
                add_query_arg('feature', 'welcome-page', $heroButton[1]),
                $heroButton[0]
            )
            : '';
        $data = [
            '%slug' => $this->getInitiator()->getPluginSlug(),
            '%title' => $pluginData['Name'],
            '%version' => $pluginData['Version'],
            '%subtitle' => \str_replace(
                '<cite>',
                \sprintf(
                    '<br /><cite>',
                    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getBaseAssetsUrl(
                        'devowl.io-logo.svg'
                    )
                ),
                $pluginData['Description']
            ),
            '%logoUrl' => $this->getInitiator()->getAssetsUrl('logo.svg'),
            '%supportLink' => $this->getInitiator()->getSupportLink(),
            '%rateLink' => $this->getInitiator()->getRateLink(),
            '%heroButton' => $heroButton,
            '%featuresHtml' => $this->transformKeyFeaturesHtml(),
            '%currentEmail' => esc_attr(get_userdata(get_current_user_id())->user_email),
            '%ourPluginsHeader' => $ourPluginsOutput['header']
        ];
        echo \str_replace(\array_keys($data), \array_values($data), $string);
        echo $ourPluginsOutput['table'];
    }
    /**
     * Display our plugins by using the native list table.
     *
     * @see https://github.com/WordPress/WordPress/blob/b9869da1575ad61ffef0ebc6330a7a7f80e5d77c/wp-admin/includes/plugin-install.php#L9
     * @see https://github.com/WordPress/WordPress/blob/b9869da1575ad61ffef0ebc6330a7a7f80e5d77c/wp-admin/includes/class-wp-plugin-install-list-table.php#L154
     */
    public function our_plugins_output() {
        // Fake query, because it is hardcoded from $_REQUEST
        $outputTable = '';
        $outputHeader = '';
        $_REQUEST['type'] = 'author';
        $_REQUEST['s'] = 'devowl';
        $_GET['tab'] = 'search';
        $wp_list_table = _get_list_table('WP_Plugin_Install_List_Table');
        $wp_list_table->order = 'DESC';
        $wp_list_table->orderby = 'last_updated';
        add_filter('plugins_api_result', [$this, 'prepare_items'], 10, 3);
        $wp_list_table->prepare_items();
        remove_filter('plugins_api_result', [$this, 'prepare_items'], 10, 3);
        if ($wp_list_table->has_items()) {
            $outputTable .= '<div class="wrap plugin-install-tab-featured" id="plugin-filter">';
            // Get output of table
            \ob_start();
            $wp_list_table->display();
            $outputTable .= \ob_get_contents();
            \ob_end_clean();
            $outputTable .= '</div>';
            $outputHeader .= \sprintf(
                '<hr /><div class="feature-section has-1-columns column-links"><h2>%s</h2></div>',
                __('Additional plugins by devowl.io', REAL_UTILS_TD)
            );
        }
        return ['table' => $outputTable, 'header' => $outputHeader];
    }
    /**
     * Filter and sort result from Plugin API call.
     *
     * @param object $res API response
     * @param string $action [query_plugins]
     * @param object $args [per_page, order_by, order]
     * @see https://wordpress.stackexchange.com/a/76644/83335
     */
    public function prepare_items($res, $action, $args) {
        $pluginDir = \constant('WP_PLUGIN_DIR') . '/';
        foreach ($res->plugins as $key => $plugin) {
            if (\strpos($plugin['description'], self::EXCLUDE_DESCRIPTION_CONTAINS)) {
                unset($res->plugins[$key]);
                continue;
            }
            // Remove already installed plugins
            $slug = $plugin['slug'];
            $fix = isset(self::PLUGIN_SLUG_FIXER[$slug]) ? self::PLUGIN_SLUG_FIXER[$slug] : null;
            $exists = \is_dir($pluginDir . $slug) || ($fix !== null && \is_dir($pluginDir . $fix));
            if ($exists) {
                unset($res->plugins[$key]);
            }
        }
        \uasort($res->plugins, function ($a, $b) {
            return \strnatcmp(\strtolower($b['last_updated']), \strtolower($a['last_updated']));
        });
        return $res;
    }
    /**
     * Get key features and transform them to key features HTML including rows' `div`.
     */
    protected function transformKeyFeaturesHtml() {
        $features = $this->getInitiator()->getKeyFeatures();
        $chunks = \array_chunk($features, 3);
        $result = '';
        foreach ($chunks as $chunk) {
            $result .= \sprintf(
                '<div class="feature-section has-%d-columns is-fullwidth">%s</div>',
                \count($chunk),
                $this->transformChunkedKeyFeaturesHtml($chunk)
            );
        }
        return $result;
    }
    /**
     * Transform given key features to key features HTML. This function does not return only the
     * columns, no row!
     *
     * @param array $features
     */
    protected function transformChunkedKeyFeaturesHtml($features) {
        $badgeTemplate =
            '<span style="margin: 0 5px 0 0;padding: 3px 10px; border-radius: 4px; background: %s; font-style: normal; color: white;">%s</span>';
        foreach ($features as $key => $feature) {
            // Show "Available in" badges (available_in)
            $availableIn = '';
            if (isset($feature['available_in'])) {
                $availableIn .= '<p class="description">' . __('Available in', REAL_UTILS_TD) . ': ';
                foreach ($feature['available_in'] as $badge) {
                    $availableIn .= \sprintf($badgeTemplate, $badge[1], $badge[0]);
                }
                $availableIn .= '</p>';
            }
            // Show highlighting badge (highlight_badge)
            $highlightBadge = '';
            if (isset($feature['highlight_badge'])) {
                $highlightBadgeArr = $feature['highlight_badge'];
                $highlightBadge .=
                    '<p class="description">' .
                    \sprintf($badgeTemplate, $highlightBadgeArr[1], $highlightBadgeArr[0]) .
                    $highlightBadgeArr[2] .
                    '</p>';
            }
            // Generate HTML
            $features[$key] = \sprintf(
                '<div class="column key-feature">
    <p style="height:%dpx"><img src="%s"/></p>
    <h3>%s</h3>
    <p>%s</p>
    %s
    %s
</div>',
                $this->getInitiator()->getWelcomePageImageHeight(),
                $feature['image'],
                $feature['title'],
                $feature['description'],
                $availableIn,
                $highlightBadge
            );
        }
        return \join('', $features);
    }
    /**
     * Get the welcome page link.
     */
    public function getWelcomePageLink() {
        return admin_url('admin.php?page=' . $this->getInitiator()->getPluginSlug() . self::PAGE_SUFFIX);
    }
    /**
     * Get initiator.
     *
     * @return AbstractInitiator
     * @codeCoverageIgnore
     */
    public function getInitiator() {
        return $this->initiator;
    }
    /**
     * Checks if the current open page is the welcome page.
     */
    public function isCurrentPage() {
        return is_admin() &&
            isset($_GET['page']) &&
            $_GET['page'] ===
                $this->getInitiator()->getPluginSlug() .
                    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::PAGE_SUFFIX;
    }
    /**
     * Create instance.
     *
     * @param AbstractInitiator $initiator
     * @codeCoverageIgnore
     */
    public static function instance($initiator) {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage($initiator);
    }
}
