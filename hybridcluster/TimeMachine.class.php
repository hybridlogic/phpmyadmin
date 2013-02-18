<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package HybridCluster
 */

/**
 * Handles the time machine navigation.
 */
class HC_TimeMachine {

    /**
     * HC_TimeMachine instance.
     *
     * @var HC_TimeMachine
     */
    private static $_instance;

    public function __construct() {
        $this->current_login_user = $GLOBALS['cfg']['Server']['user'];
        $this->user = $this->getUsername($this->current_login_user);
    }

    /**
     * Returns class instance.
     *
     * @return HC_TimeMachine
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new HC_TimeMachine();
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
     * Log the user into a new snapshot
     */
    public function handleChangeSnapshot($snapshot) {
        global $cfg;
        $cfg['Server']['user'] = strlen($snapshot) ? "{$this->user}#{$snapshot}" : $this->user;

        # In order to change the login username we must change the single
        # signon values
        PMA_clearUserCache();
        $old_session = session_name();
        $old_id = session_id();

        session_write_close();

        session_set_cookie_params(0, '/phpmyadmin/', '', 0);

        $session_name = 'SignonSession';
        session_name($session_name);
        session_id($_COOKIE[$session_name]);
        session_start();

        $_SESSION['PMA_single_signon_user'] = $cfg['Server']['user'];

        session_write_close();

        /* End single signon session */
        session_write_close();

        /* Restart phpMyAdmin session */
        session_name($old_session);
        if (!empty($old_id)) {
            session_id($old_id);
        }
        session_start();

        PMA_ajaxResponse('');
    }
}
