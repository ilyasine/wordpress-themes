<?php

namespace MatthiasWeb\RealMediaLibrary\comp\complexquery;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Reset names so slugs combined to a correct absolute path.
 *
 * This class is not yet used.
 */
class ResetNames extends \MatthiasWeb\RealMediaLibrary\comp\complexquery\ComplexQuery {
    /**
     * Single query.
     *
     * @return array
     */
    public function singleQuery() {
        // $sql = "SELECT t1.id, t2.name FROM ( SELECT t0.r_init AS id, IF(t0.reset_r = 1, (@r := t0.r_init), (@r := (select parent from $table_name where id = @r))) AS r, IF(t0.reset_r = 1, (@l := 1), (@l := @l + 1)) AS lvl FROM (SELECT m0.id as counter, m1.id AS r_init, ((SELECT min(id) FROM $table_name) = m0.id) AS reset_r FROM $table_name m0, $table_name m1 ORDER BY r_init, counter) t0 ORDER BY t0.r_init, t0.counter ) t1 INNER JOIN $table_name t2 ON t2.id = t1.r WHERE r <> -1 ORDER BY id, lvl DESC";
        // return $this->getWpdb()->get_results($sql, ARRAY_A);
        return [];
    }
    /**
     * We do not need a procedure.
     */
    public function procedure() {
        $procedure = 'wp_realmedialibrary_pr_resetnames';
        if (!$this->hasProcedure($procedure)) {
            $this->install([$this, 'installProcedure']);
        }
        // We have now a procedure and can call it
        return $this->getProcedureResults('CALL ' . $procedure . '()');
    }
    /**
     * We do not need a procedure.
     */
    public function installProcedure() {
        // Silence is golden.
    }
    /**
     * We do not need a fallback.
     */
    public function fallback() {
        return \false;
    }
}
