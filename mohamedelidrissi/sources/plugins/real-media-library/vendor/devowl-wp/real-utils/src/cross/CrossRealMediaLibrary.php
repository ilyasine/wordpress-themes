<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Cross-selling for Real Media Library.
 */
class CrossRealMediaLibrary extends \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\AbstractCrossSelling {
    const SLUG = 'real-media-library';
    const PRO_LINK = 'https://devowl.io/go/real-media-library?source=cross-rml';
    const FILE_LITE = 'real-media-library-lite/index.php';
    const FILE_PRO = 'real-media-library/index.php';
    /**
     * Documented in AbstractCrossSelling.
     *
     * @codeCoverageIgnore
     */
    public function getSlug() {
        return self::SLUG;
    }
    /**
     * Documented in AbstractCrossSelling.
     */
    public function skip() {
        $handler = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getCrossSellingHandler();
        return $handler->isInstalled(self::FILE_LITE) || $handler->isInstalled(self::FILE_PRO);
    }
    /**
     * Documented in AbstractCrossSelling.
     *
     * @codeCoverageIgnore
     */
    public function getMeta() {
        return [
            // Grid and list view
            'attachment-details' => [
                'title' => __('File finally found?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('full-control.gif'),
                'description' => __(
                    'Your WordPress site will certainly continue to grow. Organize your media library with folders!',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ],
            'insert-dialog' => [
                'title' => __('Does it take long to find a file?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('inserting-media-dialog.gif'),
                'description' => __(
                    'Inserting media into a post or page can be stressful. Use folders to organize your media library!',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ]
        ];
    }
}
