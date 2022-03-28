<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce\folder;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
interface IOverrideCreatable {
    /**
     * Additional checks before creating a folder.
     *
     * @return IFolder
     */
    public function persistCheckParent();
}
