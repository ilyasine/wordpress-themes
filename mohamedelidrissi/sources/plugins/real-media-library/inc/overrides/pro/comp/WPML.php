<?php

namespace MatthiasWeb\RealMediaLibrary\lite\comp;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait WPML {
    // Documented in IOverrideWPML
    public function overrideInit() {
        add_filter('RML/Sortable/PostsClauses', [$this, 'sqlstatement_order'], 10, 3);
        add_filter('RML/Sortable/Ids', [$this, 'sortable_ids'], 10);
    }
    // Undocumented
    public function sqlstatement_order($pieces, $query, $folder) {
        global $sitepress, $wpdb;
        // Apply only when not-default language
        if (!$this->isDefaultLanguage()) {
            $pieces['fields'] = \str_replace('rmlorder.nr', 'IFNULL(rmlorderwpml.nr, rmlorder.nr)', $pieces['fields']);
            $pieces['join'] .=
                ' LEFT JOIN ' .
                $wpdb->prefix .
                'icl_translations rmlorderwpmlicl
            	ON rmlorderwpmlicl.trid = wpml_translations.trid AND rmlorderwpmlicl.source_language_code IS NULL
            LEFT JOIN ' .
                $this->getTableName('posts') .
                ' rmlorderwpml
            	ON rmlorderwpmlicl.element_id = rmlorderwpml.attachment ';
            $pieces['orderby'] = \str_replace(
                'rmlorder.nr',
                'IFNULL(rmlorderwpml.nr, rmlorder.nr)',
                $pieces['orderby']
            );
        }
        return $pieces;
    }
    // Undocumented
    public function sortable_ids($ids) {
        global $sitepress;
        // Apply only when not-default language
        if (!$this->isDefaultLanguage()) {
            $ids['attachment'] = $this->getDefaultId($ids['attachment']);
            if ($ids['next'] !== \false) {
                $ids['next'] = $this->getDefaultId($ids['next']);
            }
            if ($ids['lastInView'] !== \false) {
                $ids['lastInView'] = $this->getDefaultId($ids['lastInView']);
            }
        }
        return $ids;
    }
}
