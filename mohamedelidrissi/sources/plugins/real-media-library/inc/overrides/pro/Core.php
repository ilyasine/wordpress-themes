<?php

namespace MatthiasWeb\RealMediaLibrary\lite;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\CorePro;
use MatthiasWeb\RealMediaLibrary\lite\folder\Collection;
use MatthiasWeb\RealMediaLibrary\lite\folder\Creatable;
use MatthiasWeb\RealMediaLibrary\lite\folder\Gallery;
use MatthiasWeb\RealMediaLibrary\lite\order\Sortable;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption;
use MatthiasWeb\WPU\V4\WPLSController;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait Core {
    use CorePro;
    /**
     * Updater instance.
     *
     * @see https://github.com/Capevace/wordpress-plugin-updater
     */
    private $updater;
    // Documented in IOverrideCore
    public function overrideConstruct() {
        wp_rml_register_creatable(\MatthiasWeb\RealMediaLibrary\lite\folder\Collection::class, RML_TYPE_COLLECTION);
        wp_rml_register_creatable(\MatthiasWeb\RealMediaLibrary\lite\folder\Gallery::class, RML_TYPE_GALLERY);
        $this->getUpdater();
        // Initially load the updater
    }
    // Documented in IOverrideCore
    public function overrideInit() {
        add_filter(
            'mla_media_modal_query_final_terms',
            [\MatthiasWeb\RealMediaLibrary\lite\order\Sortable::class, 'mla_media_modal_query_final_terms'],
            10,
            2
        );
        add_filter('posts_clauses', [\MatthiasWeb\RealMediaLibrary\lite\order\Sortable::class, 'posts_clauses'], 10, 2);
        add_action(
            'RML/Item/MoveFinished',
            [\MatthiasWeb\RealMediaLibrary\lite\order\Sortable::class, 'item_move_finished'],
            1,
            4
        );
        add_action(
            'deleted_realmedialibrary_meta',
            [\MatthiasWeb\RealMediaLibrary\lite\folder\Creatable::class, 'deleted_realmedialibrary_meta'],
            10,
            3
        );
        // Show plugin notice
        if (!$this->getUpdater()->isActivated()) {
            add_action('after_plugin_row_' . plugin_basename(RML_FILE), [$this, 'after_plugin_row'], 10, 2);
        }
    }
    /**
     * Show a notice in the plugins list that the plugin is not activated, yet.
     *
     * @param string $file
     * @param array $plugin
     */
    public function after_plugin_row($file, $plugin) {
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        \printf(
            '<tr class="rml-update-notice active">
	<th colspan="%d" class="check-column">
    	<div class="plugin-update update-message notice inline notice-warning notice-alt">
        	<div class="update-message">%s</div>
    	</div>
    </th>
</tr>',
            $wp_list_table->get_column_count(),
            wpautop(
                __(
                    '<strong>You have not yet entered the license key</strong>. To receive automatic updates, please enter the key in "Enter license".',
                    RML_TD
                )
            )
        );
    }
    // Documented in IOverrideCore
    public function isLicenseNoticeDismissed($set = null) {
        $value = '1';
        $expireOption = new \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption(
            RML_OPT_PREFIX . '_licenseActivated',
            \false,
            365 * \constant('DAY_IN_SECONDS')
        );
        $expireOption->enableTransientMigration(
            \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption::TRANSIENT_MIGRATION_SITE_WIDE
        );
        if ($set !== null) {
            $expireOption->set($set ? $value : 0);
        }
        return $expireOption->get() === $value;
    }
    /**
     * Send an email newsletter if checked in the updater.
     *
     * @param string $email
     */
    public function handleNewsletter($email) {
        wp_remote_post('https://devowl.io/wp-json/devowl-site/v1/plugin-activation-newsletter', [
            'body' => [
                'email' => $email,
                'referer' => home_url(),
                'slug' => RML_SLUG,
                'language' => \explode('_', get_user_locale())[0]
            ]
        ]);
    }
    // Documented in IOverrideCore
    public function getUpdater() {
        if ($this->updater === null) {
            $this->updater = \MatthiasWeb\WPU\V4\WPLSController::initClient('https://license.matthias-web.com/', [
                'name' => 'WP Real Media Library',
                'version' => RML_VERSION,
                'path' => RML_FILE,
                'slug' => RML_SLUG,
                'newsletterPrivacy' => 'https://devowl.io/privacy-policy/'
            ]);
            add_action('wpls_email_' . RML_SLUG, [$this, 'handleNewsletter']);
        }
        return $this->updater;
    }
}
