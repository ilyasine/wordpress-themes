<?php

namespace MatthiasWeb\RealMediaLibrary\order;

use MatthiasWeb\RealMediaLibrary\folder\Creatable;
use MatthiasWeb\RealMediaLibrary\lite\order\Sortable as OrderSortable;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\order\IOverrideSortable;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handles the sortable content in the folder. The methods of this class contains
 * always the keyword "content".
 */
abstract class Sortable extends \MatthiasWeb\RealMediaLibrary\folder\Creatable implements
    \MatthiasWeb\RealMediaLibrary\overrides\interfce\order\IOverrideSortable {
    use OrderSortable;
    static $cachedContentOrders = null;
    // Documented in Creatable
    public function __construct(
        $id,
        $parent = -1,
        $name = '',
        $slug = '',
        $absolute = '',
        $order = -1,
        $cnt = 0,
        $row = []
    ) {
        $this->contentCustomOrder = isset($row->contentCustomOrder) ? \intval($row->contentCustomOrder) : 2;
        // Parent constructor
        parent::__construct($id, $parent, $name, $slug, $absolute, $order, $cnt, $row);
    }
    // Documented in IFolderContent
    public function isContentCustomOrderAllowed() {
        return $this->getContentCustomOrder() !== 2;
    }
    // Documented in IFolderContent
    public function getContentCustomOrder() {
        return $this->contentCustomOrder;
    }
    // Documented in IFolderContent
    public function forceContentCustomOrder() {
        return \false;
    }
    // Documented in IFolderContent
    public function postsClauses($pieces) {
        return \false;
    }
    /**
     * Get all available order methods.
     *
     * @param boolean $asMap
     * @return array
     */
    public static function getAvailableContentOrders($asMap = \false) {
        if (self::$cachedContentOrders === null) {
            $orders = [
                'date_asc' => ['label' => __('Order by date ascending', RML_TD), 'sqlOrder' => 'wp.post_date'],
                'date_desc' => ['label' => __('Order by date descending', RML_TD), 'sqlOrder' => 'wp.post_date'],
                'title_asc' => ['label' => __('Order by title ascending', RML_TD), 'sqlOrder' => 'wp.post_title'],
                'title_desc' => ['label' => __('Order by title descending', RML_TD), 'sqlOrder' => 'wp.post_title'],
                'filename_asc' => [
                    'label' => __('Order by filename ascending', RML_TD),
                    'sqlOrder' => "SUBSTRING_INDEX(wp.guid, '/', -1)"
                ],
                'filename_desc' => [
                    'label' => __('Order by filename descending', RML_TD),
                    'sqlOrder' => "SUBSTRING_INDEX(wp.guid, '/', -1)"
                ],
                'filenameNat_asc' => [
                    'label' => __('Natural order by filename ascending', RML_TD),
                    'sqlOrder' => "LENGTH(SUBSTRING_INDEX(wp.guid, '/', -1)), SUBSTRING_INDEX(wp.guid, '/', -1)"
                ],
                'filenameNat_desc' => [
                    'label' => __('Natural order by filename descending', RML_TD),
                    'sqlOrder' => "LENGTH(SUBSTRING_INDEX(wp.guid, '/', -1)) desc, SUBSTRING_INDEX(wp.guid, '/', -1)"
                ],
                'id_asc' => ['label' => __('Order by ID ascending', RML_TD), 'sqlOrder' => 'wp.ID'],
                'id_desc' => ['label' => __('Order by ID descending', RML_TD), 'sqlOrder' => 'wp.ID']
            ];
            /**
             * Add an available order criterium to folder content. If you pass
             * user input to the SQL Order please be sure the values are escaped!
             *
             * @example
             * $orders["id_asc"] = [
             *  "label" => __("Order by ID ascending", RML_TD),
             *  "sqlOrder" => "wp.ID"
             * )
             * @param {object[]} $orders The available orders
             * @return {object[]}
             * @hook RML/Order/Orderby
             */
            self::$cachedContentOrders = apply_filters('RML/Order/Orderby', $orders);
        }
        if ($asMap) {
            $sortables = [];
            foreach (self::$cachedContentOrders as $key => $value) {
                $sortables[$key] = $value['label'];
            }
            return $sortables;
        }
        return self::$cachedContentOrders;
    }
}
