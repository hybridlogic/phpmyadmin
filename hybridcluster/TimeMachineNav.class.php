<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package HybridCluster
 */

/**
 * Handles the time machine navigation.
 */
class HC_TimeMachineNav {
    /**
     * HC_TimeMachineNav instance.
     *
     * @var HC_TimeMachineNav
     */
    private static $_instance;

    public function __construct() {
        $this->current_login_user = $GLOBALS['cfg']['Server']['user'];
        $this->user = $this->getUsername($this->current_login_user);
        $this->db = $this->user; // XXX implementation detail?
        $this->snapshot = $this->getSnapshot($this->current_login_user);

        require_once "{$_SERVER['DOCUMENT_ROOT']}/include/HybridClusterAPI.class.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/include/HybridClusterAPIInternalException.class.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/include/jsonRPCClient.class.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/include/Spyc.class.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/include/Site.class.php";

        $api = HybridClusterAPI::get();
        try {
            $snapshots = $api->availableSnapshotsForDatabase($this->db);
            $snapshots = array_reverse($snapshots);
        } catch (HybridClusterAPIInternalException $e) {
            $snapshots = Array();
        }

        $this->snapshots = $snapshots;
    }


    /**
     * Returns class instance.
     *
     * @return HC_TimeMachineNav
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new HC_TimeMachineNav();
        }
        return self::$_instance;
    }

    /**
     * Return the logged in username (without snapshot)
     *
     * @return string
     */
    private function getUsername($login_username) {
        list($username) = explode("#", $login_username);
        return $username;
    }


    /**
     * Return the active snapshot
     *
     * @return string
     */
    private function getSnapshot($login_username) {
        list($username, $snapshot) = explode("#", $login_username);
        return (string)$snapshot;
    }


    /**
     * Return options for HTML select.
     *
     * @return string
     */
    public function getHtmlSelectOption()
    {
        $html = '<option value="">(' . __('View snapshot') . ') ...</option>';
        if (count($this->snapshots)) {
            $html .= '<option value="">' . __('Current') . '</option>';
            foreach ($this->snapshots as $snapshot) {
                $selected = $snapshot['name'] == $this->snapshot ? ' selected="selected"' : '';
                $html .= '<option value="' . htmlspecialchars($snapshot['name']) . '"'.$selected.'>' .
                         htmlspecialchars(date('jS M \'y – H:i', $snapshot['timestamp'])) . '</option>';
            }
        } else {
            $html .= '<option value="">' . __('There are no recent tables') . '</option>';
        }
        return $html;
    }

    /**
     * Return HTML select.
     *
     * @return string
     */
    public function getHtmlSelect()
    {
        $html  = '<input type="hidden" name="goto" id="LeftDefaultTabTable" value="' .
                         htmlspecialchars($GLOBALS['cfg']['LeftDefaultTabTable']) . '" />';
        $html .= '<select name="selected_time_machine_snapshot" id="timeMachineSnapshot">';
        $html .= $this->getHtmlSelectOption();
        $html .= '</select>';

        return $html;
    }
}
