<?php

namespace MatthiasWeb\RealMediaLibrary\api;

use MatthiasWeb\RealMediaLibrary\Assets;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Metadata content of a folder. The metadata can be changed in the arrow-down icon
 * in the folders sidebar toolbar. To handle metadata for folders you can
 * use the add_media_folder_meta function.
 *
 * To register the metadata class you must use the following API function add_rml_meta_box.
 */
interface IMetadata {
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
     * @param IFolder|null $folder The folder object
     * @return string
     */
    public function content($content, $folder);
    /**
     * Save the infos. Add an error to the array to show on the frontend dialog. Add an
     * successful data to receive it in JavaScript.
     *
     * ```php
     * $response["errors"][] = "Your error";
     * $response["data"]["myData"] = "Test";
     * ```
     *
     * Since v.4.0.8 the minimum PHP version is 4.0.8 and you can use traits in your meta
     * implementation: metadata\CommonTrait, metadata\CommonFolderTrait or usersettings\CommonUserSettingsTrait.
     *
     * @param string[] $response Array of errors and successful data.
     * @param \WP_REST_Request $request The server request
     * @param IFolder|null $folder The folder object
     * @return string[]
     */
    public function save($response, $folder, $request);
    /**
     * Enqueue scripts and styles for this meta box.
     *
     * @param Assets $assets The assets instance so you can enqueue library scripts
     */
    public function scripts($assets);
}
