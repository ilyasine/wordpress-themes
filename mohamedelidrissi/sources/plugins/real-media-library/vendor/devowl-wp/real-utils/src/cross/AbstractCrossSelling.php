<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * An abstract cross-selling implementation which can be used for each pro product of devowl.io.
 * Do not use any constants as they are not available when the plugin is not active.
 */
abstract class AbstractCrossSelling {
    use UtilsProvider;
    const NEXT_POPUP = '+7 days';
    const NEXT_POPUP_IN_PRO = '+14 days';
    /**
     * Get the slug for this plugin.
     *
     * @return string
     */
    abstract public function getSlug();
    /**
     * Get available popup types. See CrossRealMediaLibrary as example implementation.
     *
     * @return string
     */
    abstract public function getMeta();
    /**
     * Check if the plugin is already installed so the ad can be skipped.
     *
     * @return boolean
     */
    abstract public function skip();
    /**
     * Get the external URL to assets.
     *
     * @param string $path
     */
    public function getAssetsUrl($path = '') {
        return \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getBaseAssetsUrl(
            \sprintf('wp-%s/%s', $this->getSlug(), $path)
        );
    }
    /**
     * Get or update the action counter for a given action.
     *
     * @param string $action
     * @param boolean $increment
     * @return int
     */
    public function actionCounter($action, $increment = \false) {
        $optionName =
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_CROSS_COUNTER .
            '.' .
            $this->getSlug() .
            '.' .
            $action;
        $cnt = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::get(
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_INITIATOR_CROSS,
            $optionName,
            0
        );
        if ($increment) {
            $cnt++;
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::set(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_INITIATOR_CROSS,
                $optionName,
                $cnt
            );
        }
        return $cnt;
    }
    /**
     * Get or update the hidden action status for a given action. This can not be undone if once set.
     *
     * @param string $action
     * @param boolean $force
     * @return boolean
     */
    public function forceHide($action, $force = \false) {
        $skip = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::get(
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_INITIATOR_CROSS,
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_CROSS_SKIP,
            []
        );
        if ($force) {
            $skip[] = $this->getSlug() . '.' . $action;
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::set(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_INITIATOR_CROSS,
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_CROSS_SKIP,
                $skip
            );
        }
        return \in_array($this->getSlug() . '.' . $action, $skip, \true);
    }
    /**
     * Dismiss a cross popup for a product.
     *
     * @param string $action
     * @param boolean $force
     * @return boolean
     */
    public function dismiss($action, $force) {
        // Increment dismisses
        $this->actionCounter($action, \true);
        $this->forceHide($action, $force);
        // Update next timestamp
        $ts = \strtotime(
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                ->getCrossSellingHandler()
                ->isAnyProInstalled()
                ? self::NEXT_POPUP_IN_PRO
                : self::NEXT_POPUP
        );
        if (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::set(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_INITIATOR_CROSS,
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler::TRANSIENT_NEXT_CROSS_SELLING,
                $ts
            )
        ) {
            return $ts;
        }
        return \false;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
