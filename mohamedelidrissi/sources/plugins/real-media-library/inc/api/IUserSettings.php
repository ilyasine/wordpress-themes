<?php

namespace MatthiasWeb\RealMediaLibrary\api;

use MatthiasWeb\RealMediaLibrary\Assets;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Metadata content for the general user settings. The metadata can be changed in the cog icon
 * in the folders sidebar toolbar. To handle metadata for general user settings you can
 * use the default WordPress add_user_meta function.
 *
 * To register the metadata class you must use the following API function add_rml_user_settings_box.
 *
 * @see https://developer.wordpress.org/reference/functions/add_user_meta/
 * @since 3.2
 */
interface IUserSettings {
    /**
     * Return modified content for the meta box.
     *
     * <strong>Note:</strong> If you want to use a more complex content
     * in a meta table use something like this:
     * ```html
     * <tr>
     *  <th scope="row">Medium size</th>
     *  <td><fieldset>
     *      <legend class="screen-reader-text"><span>Medium size</span></legend>
     *      <label for="medium_size_w">Max Width</label>
     *      <input name="medium_size_w" type="number" step="1" min="0" id="medium_size_w" value="300" class="small-text">
     *      <label for="medium_size_h">Max Height</label>
     *      <input name="medium_size_h" type="number" step="1" min="0" id="medium_size_h" value="300" class="small-text">
     *  </fieldset></td>
     * </tr>
     * ```
     *
     * If you want to "group" your meta boxes you can use this code to create a empty space:
     * `<tr class="rml-meta-margin"></tr>`
     *
     * @param string $content the HTML formatted string for the dialog
     * @param int $user Current user id
     * @return string Content
     */
    public function content($content, $user);
    /**
     * Save the infos. Add an error to the array to show on the frontend dialog. Add an
     * successful data to receive it in JavaScript.
     *
     * ```php
     * $response["errors"][] = "Your error";
     * $response["data"]["myData"] = "Test";
     * ```
     *
     * @param string[] $response Array of errors and successful data.
     * @param int $user Current user id
     * @param \WP_REST_Request $request The server request
     * @return string[]
     */
    public function save($response, $user, $request);
    /**
     * Enqueue scripts and styles for this meta box.
     *
     * @param Assets $assets The assets instance so you can enqueue library scripts
     */
    public function scripts($assets);
}
