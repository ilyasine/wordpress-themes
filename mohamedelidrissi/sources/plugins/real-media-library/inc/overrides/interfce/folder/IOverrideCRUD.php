<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce\folder;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
interface IOverrideCRUD {
    // Documented in wp_rml_create_p()
    public function createRecursively($name, $parent, $type, $restrictions = [], $supress_validation = \false);
}
