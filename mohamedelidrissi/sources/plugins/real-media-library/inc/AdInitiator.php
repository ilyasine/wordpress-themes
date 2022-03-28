<?php

namespace MatthiasWeb\RealMediaLibrary;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\AbstractInitiator;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Initiate real-utils functionality.
 */
class AdInitiator extends \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\AbstractInitiator {
    use UtilsProvider;
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginBase() {
        return $this;
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginAssets() {
        return $this->getCore()->getAssets();
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getRateLink() {
        return $this->isPro()
            ? 'https://devowl.io/go/codecanyon/real-media-library/rate'
            : 'https://devowl.io/go/wordpress-org/real-media-library/rate';
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getKeyFeatures() {
        $isPro = $this->isPro();
        return [
            [
                'image' => $this->getAssetsUrl('full-control.gif'),
                'title' => __('Complete file and folder manager', RML_TD),
                'description' => __(
                    'Real Media Library is a WordPress plugin that empowers you with advanced media management. You can use this plugin to organize the thousands of images, audio, video and PDF files in your media library into folders. Basically it is a file manager like Windows Explorer or Mac Finder, but for WordPress.',
                    RML_TD
                ),
                'available_in' => $isPro
                    ? null
                    : [
                        ['Lite', \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::COLOR_BADGE_LITE],
                        ['Pro', \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::COLOR_BADGE_PRO]
                    ]
            ],
            [
                'image' => $this->getAssetsUrl('inserting-media-dialog.gif'),
                'title' => __('Filter in insert media dialog', RML_TD),
                'description' => __(
                    'No matter where you are, the folder structure of Real Media Library is always where you can select files. For example in the dialog for selecting a "Featured Image".',
                    RML_TD
                ),
                'available_in' => $isPro
                    ? null
                    : [['Pro', \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::COLOR_BADGE_PRO]],
                'highlight_badge' => $isPro
                    ? null
                    : [
                        'Lite',
                        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::COLOR_BADGE_LITE,
                        __('In the Lite version you can only select the folder by a simple dropdown.', RML_TD)
                    ]
            ],
            [
                'image' => $this->getAssetsUrl('order-content.gif'),
                'title' => __('Custom image order', RML_TD),
                'description' => __(
                    'Organizing your media files is really easy with the Real Media Library plugin. You can arrange the order of your files yourself by dragging and dropping. This allows you to move important files to the top for faster access.',
                    RML_TD
                ),
                'available_in' => $isPro
                    ? null
                    : [['Pro', \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::COLOR_BADGE_PRO]]
            ]
        ];
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getHeroButton() {
        return $this->isPro() ? null : [__('Get your PRO license now!', RML_TD), RML_PRO_VERSION];
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @param boolean $isFirstTime
     * @codeCoverageIgnore
     */
    public function getNextRatingPopup($isFirstTime) {
        return $this->isPro() ? \strtotime('+90 days') : parent::getNextRatingPopup($isFirstTime);
    }
}
