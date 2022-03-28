<?php

namespace MatthiasWeb\RealMediaLibrary\metadata;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Trait common folder meta and user settings helper methods.
 *
 * @since 4.0.8
 */
trait CommonTrait {
    /**
     * Reload the current selected folder after the metadata is successfully saved.
     *
     * @param string[] $response
     */
    private function reloadAfterSave(&$response) {
        if (!isset($response['data']['reload']) || $response['data']['reload'] === \false) {
            $response['data']['reload'] = \true;
        }
    }
    /**
     * Reload the current document if the `body` has a given class. This allows you e.g. to
     * only reload in Media Library view instead of while editing a post.
     *
     * @param string[] $response
     * @param string $class
     */
    private function hardReloadAfterSaveIfBodyHasClass(&$response, $class) {
        if (
            !isset($response['data']['hardReloadIfBodyHasClass']) ||
            $response['data']['hardReloadIfBodyHasClass'] === \false
        ) {
            $response['data']['hardReloadIfBodyHasClass'] = $class;
        }
    }
}
