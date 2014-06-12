<?php

require "StudipMobileController.php";

/**
 *    create, destroy (...) sessions
 *    login process
 *    @author Marcus Lunzenauer - mlunzena@uos.de
 *    @author André Klaßen - aklassen@uos.de
 *    @author Nils Bussmann - nbussman@uos.de
 */
class SessionController extends StudipMobileController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($action === "destroy") {
            $this->requireUser();
        }
    }

    function new_action()
    {
    }

    function create_action()
    {

        $username = Request::get("username");
        $password = Request::get("password");

        if (isset($username) && isset($password)) {
            $result = StudipAuthAbstract::CheckAuthentication($username, $password);
        }

        if (!isset($result) || $result['uid'] === false) {
            $this->flash["notice"] = "login unsuccessful!";
            \NotificationCenter::postNotification('mobile.SessionDidNotCreate', $this);
            $this->flash["failed_login"] = true;
            $this->redirect("session/new");
            return;
        }

        $user_id = get_userid($username);

        if (isset($user_id)) {
            $this->start_session($user_id);
        }

        $this->flash["notice"] = "login successful!";
        \NotificationCenter::postNotification('mobile.SessionDidCreate', $this);
        $this->redirect("activities");
    }

    function destroy_action()
    {
        global $perm, $user, $auth, $sess, $forced_language, $_language;
        $auth->logout();
        $perm = null;
        $user = null;
        $forced_language = null;
        $_language = null;
        $sess->delete();
        closeObject();
        \NotificationCenter::postNotification('Mobile.SessionDidDestroy', $this);
        $this->redirect("session/new");
    }

    protected function start_session($user_id)
    {

        // TODO: für Stud.IP v2.3
        if (is_callable(array('Seminar_User', 'start'))) {
            global $perm, $user, $auth, $sess, $forced_language, $_language;


            $user = new Seminar_User();
            $user->start($user_id);

            foreach (array(
                         "uid" => $user_id,
                         "perm" => $user->perms,
                         "uname" => $user->username,
                         "auth_plugin" => $user->auth_plugin,
                         "exp" => time() + 60 * 15,
                         "refresh" => time()
                     ) as $k => $v) {
                $auth->auth[$k] = $v;
            }

            $auth->nobody = false;

            $sess->regenerate_session_id(array('auth', 'forced_language','_language'));
            $sess->freeze();
        }

        // TODO: für Stud.IP v2.5
        else {

            $user = User::find($user_id);

            $GLOBALS['auth'] = new Seminar_Auth();
            $GLOBALS['auth']->auth = array(
                'uid'   => $user->user_id,
                'uname' => $user->username,
                'perm'  => $user->perms,
                "auth_plugin" => $user->auth_plugin,
            );

            $GLOBALS['user'] = new Seminar_User($user);

            $GLOBALS['perm'] = new Seminar_Perm();
            $GLOBALS['MAIL_VALIDATE_BOX'] = false;
        }
    }
}
