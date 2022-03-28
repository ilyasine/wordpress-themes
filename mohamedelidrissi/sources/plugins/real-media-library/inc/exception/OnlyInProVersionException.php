<?php

namespace MatthiasWeb\RealMediaLibrary\exception;

use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * When a functionality is only available in PRO version throw an exception.
 */
class OnlyInProVersionException extends \Exception {
    /**
     * C'tor.
     *
     * @param string $method
     */
    public function __construct($method) {
        // translators:
        parent::__construct(
            // translators:
            \sprintf(__('This functionality is not available in the free version (%s).', RML_TD), $method)
        );
    }
}
