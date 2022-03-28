<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Cross-selling for Real Category Management.
 */
class CrossRealCategoryLibrary extends
    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\AbstractCrossSelling {
    const SLUG = 'real-category-library';
    const PRO_LINK = 'https://devowl.io/go/real-category-management?source=cross-rcm';
    const FILE_LITE = 'real-category-library-lite/index.php';
    const FILE_PRO = 'real-category-library/index.php';
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
     *
     * @codeCoveragIgnore
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
            'add-category' => [
                'title' => __('Do you manage many categories?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('full-control.gif'),
                'description' => __(
                    'Do you still create your categories this way? Get an explorer-like tree view directly in your posts/page tables!',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ],
            'add-wc-category' => [
                'title' => __('Manage product categories/attributes?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('feature-woocommerce.gif'),
                'description' => __(
                    'Do you still create your product categories and attributes this way? Get an explorer-like tree view directly into your WooCommerce products table!',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ],
            'pagination' => [
                'title' => __('Paginate without reload?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('feature-pagination.gif'),
                'description' => __(
                    'Do you want to paginate through your posts and pages without having to reload the complete page?',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ],
            'assign' => [
                'title' => __('Assigning much faster?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl('full-control.gif'),
                'description' => __(
                    'You can move or insert a post or page directly into a category without having to deal with checkboxes.',
                    REAL_UTILS_TD
                ),
                'link' => self::PRO_LINK
            ]
        ];
    }
}
