<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce\usersettings;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
interface IOverrideDefaultFolder {
    /**
     * Additional constructor.
     */
    public function overrideConstruct();
    // Documented in IMetadata
    public function overrideSave($response, $user, $request);
}
