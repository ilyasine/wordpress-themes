<?php

namespace MatthiasWeb\RealMediaLibrary\api;

use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This interface provides elementary action methods for folder content. All folder
 * types (Folder, Collection, Gallery, ...) have implemented this interface.
 * Also the root ("Unorganized") is a folder and implements this interface.
 *
 * @since 3.3.1
 */
interface IFolderContent {
    /**
     * (Pro only) See API function for more information.
     *
     * @param int $attachmentId
     * @param int $nextId
     * @param int|boolean $lastIdInView
     * @throws \Exception
     * @return true
     * @see wp_attachment_order_update()
     * @throws OnlyInProVersionException
     */
    public function contentOrder($attachmentId, $nextId, $lastIdInView = \false);
    /**
     * (Pro only) Start to order the given folder content by a given order type.
     *
     * @param string $orderby The order type key
     * @param boolean $writeMetadata
     * @return boolean
     * @throws OnlyInProVersionException
     */
    public function contentOrderBy($orderby, $writeMetadata = \true);
    /**
     * (Pro only) Index the order table.
     *
     * @param boolean $delete Delete the old order
     * @return boolean
     * @throws OnlyInProVersionException
     */
    public function contentIndex($delete = \true);
    /**
     * (Pro only) This function retrieves the order of the order
     * table and removes empty spaces, for example:
     * <pre>0 1 5 7 8 9 10 =>
     * 0 1 2 3 4 5 6</pre>
     *
     * @return boolean
     * @throws OnlyInProVersionException
     */
    public function contentReindex();
    /**
     * (Pro only) Enable the order functionality for this folder.
     *
     * @return boolean
     * @see IFolderContent::getContentCustomOrder()
     * @throws OnlyInProVersionException
     */
    public function contentEnableOrder();
    /**
     * (Pro only) Deletes the complete order for this folder.
     *
     * @return boolean
     * @see IFolderContent::getContentCustomOrder()
     * @throws OnlyInProVersionException
     */
    public function contentDeleteOrder();
    /**
     * (Pro only) Restore the current order number to the old custom order number.
     *
     * @return boolean
     * @throws OnlyInProVersionException
     */
    public function contentRestoreOldCustomNr();
    /**
     * Checks if the folder is allowed to use custom content order.
     *
     * @return boolean
     */
    public function isContentCustomOrderAllowed();
    /**
     * The content custom order defines the state of the content order functionality:
     *
     * <pre>0 = No content order defined
     * 1 = Content order is enabled
     * 2 = Custom content order is not allowed</pre>
     *
     * @return int The content custom order value
     * @see IFolderContent::isContentCustomOrderAllowed()
     * @see IFolderContent::contentEnableOrder()
     */
    public function getContentCustomOrder();
    /**
     * Override this functionality to force the content custom order
     * in the posts_clauses.
     *
     * @return boolean
     * @since 4.0.2
     */
    public function forceContentCustomOrder();
    /**
     * Override the default posts_clauses join and orderby instead of the RML standard.
     * This can be useful if you want to take the order from another relationship.
     * You have to return true if you have overwritten it.
     *
     * @param array $pieces The posts_clauses $pieces parameter
     * @return boolean
     * @since 4.0.2
     */
    public function postsClauses($pieces);
    /**
     * (Pro only) Get the next attachment row for a specific attachment. It returns false if
     * the attachment is at the end or the folder has no custom content order.
     *
     * @param int $attachmentId The attachment id
     * @return array or null
     * @since 4.0.8 Now the method returns an array instead of int
     * @throws OnlyInProVersionException
     */
    public function getAttachmentNextTo($attachmentId);
    /**
     * (Pro only) Gets the biggest order number;
     *
     * @param string $function The SQL aggregation function (MIN or MAX)
     * @return int
     * @throws OnlyInProVersionException
     */
    public function getContentAggregationNr($function = 'MAX');
    /**
     * (Pro only) Get the order number for a specific attachment in this folder.
     *
     * @param int $attachmentId The attachment id
     * @return int|boolean
     * @throws OnlyInProVersionException
     */
    public function getContentNrOf($attachmentId);
    /**
     * (Pro only) Get the old custom order number count so we can decide if already available.
     *
     * @return int
     * @throws OnlyInProVersionException
     */
    public function getContentOldCustomNrCount();
}
