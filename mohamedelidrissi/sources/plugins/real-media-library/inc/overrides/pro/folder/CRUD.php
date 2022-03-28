<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait CRUD {
    // Documented in wp_rml_create_p()
    public function createRecursively($name, $parent, $type, $restrictions = [], $supress_validation = \false) {
        $name = \trim(\trim($name), '/');
        $names = \explode('/', $name);
        $parent = $parent;
        foreach ($names as $folderName) {
            $parent = $this->create($folderName, $parent, $type, $restrictions, $supress_validation, \true);
            if (\is_array($parent)) {
                return $parent;
            }
        }
        return $parent;
    }
}
