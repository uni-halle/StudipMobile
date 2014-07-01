<?php

require_once $this->trails_root .'/models/helper.php';

define("TOOLBAR_THEME",         "a");
define("TOOLBAR_BUTTONS",       "c");
define("TOOLBAR_ABORT",         "e");
define("STANDARD_DIVIDER",      "a");

/**
 *    global usefull stuff
 *    @author Marcus Lunzenauer - mlunzena@uos.de
 *    @author André Klaßen - aklassen@uos.de
 *    @author Nils Bussmann - nbussman@uos.de
 */
class StudipMobileController extends Trails_Controller
{

    /**
     * Applikationsübergreifender before_filter mit Trick:
     *
     * Controller-Methoden, die mit "before" anfangen werden in
     * Quellcode-Reihenfolge als weitere before_filter ausgeführt.
     * Geben diese FALSE zurück, bricht Trails genau wie beim normalen
     * before_filter ab.
     */
    function before_filter(&$action, &$args)
    {
        $this->plugin_path = URLHelper::getURL($this->dispatcher->plugin->getPluginPath());
        list($this->plugin_path) = explode("?cid=",$this->plugin_path);

        // notify on mobile trails action
        $klass = substr(get_called_class(), 0, -10);
        $name = sprintf('mobile.performed.%s_%s', $klass, $action);
        \NotificationCenter::postNotification($name, $this);

        $this->flash = Trails_Flash::instance();

        // notify on automatic redirect
        if (Request::submitted("redirected")) {
            \NotificationCenter::postNotification("mobile.ClientDidRedirect", $this);
        }
    }

    /**
     * Return currently logged in user
     */
    function currentUser()
    {
        global $user;

        if (!is_object($user) || $user->id == 'nobody') {
            return null;
        }

        return $user;
    }


    /**
     * Helper method to ensure a logged in user
     */
    function requireUser()
    {
        if (!$this->currentUser()) {
            # TODO (mlunzena): store_location
            $this->flash["notice"] = "You must be logged in to access this page";
            \NotificationCenter::postNotification('mobile.SessionIsMissing', $this);
            $this->redirect("session/new");
            return FALSE;
        }
    }

    function render_json($data)
    {
        $this->response->add_header('Content-Type', 'application/json');
        $this->render_text(json_encode($this->filter_utf8($data)));
    }


    function filter_utf8($items)
    {
        foreach ($items as &$item) {
            foreach ($item as &$value) {
                if (is_string($value)) {
                    $value = utf8_encode($value);
                }
            }
        }
        return $items;
    }


    function url_for($to)
    {
        if (Studip\Mobile\Helper::isExternalLink($to)) {
            return $to;
        }

        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
}
