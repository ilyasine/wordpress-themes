<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
interface ICore {
    /**
     * Additional constructor.
     */
    public function overrideConstructFreemium();
    /**
     * Set and/or get the value if the lite notice is dismissed.
     * We need this because the tree is always visible in the posts list
     * and if an user still wants to use the lite version it should be
     * able without annoying notices.
     *
     * @param boolean $set
     * @return boolean
     */
    public function isLiteNoticeDismissed($set = null);
}
