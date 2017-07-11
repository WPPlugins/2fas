<?php

namespace TwoFAS\Request;

use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Actions\TwoFAS_Action_Map;

class TwoFAS_Request
{
    /**
     * @var string
     */
    private $action_key = 'twofas-action';

    /**
     * @var string
     */
    private $submenu_key = 'page';

    /**
     * @var string
     */
    private $twofas_param_array = 'twofas';

    /**
     * @var null|TwoFAS_Action
     */
    private $action;

    /**
     * @var TwoFAS_Action_Map
     */
    private $action_map;

    public function __construct()
    {
        $this->action_map = new TwoFAS_Action_Map();
        $this->action     = $this->__fetch_action_from_request();
    }

    /**
     * @return null|TwoFAS_Action
     */
    private function __fetch_action_from_request()
    {
        $action_name  = isset($_GET[$this->action_key])  ? $_GET[$this->action_key]  : 'default';
        $submenu_name = isset($_GET[$this->submenu_key]) ? $_GET[$this->submenu_key] : '';

        return $this->action_map->get_action($submenu_name, $action_name);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has_param($key)
    {
        return isset($_POST[$this->twofas_param_array][$key]);
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function has_params(array $params)
    {
        $are_set = true;
        
        foreach ($params as $index => $param) {
            $are_set = $are_set && $this->has_param($param);
        }

        return $are_set;
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public function get_param($key)
    {
        if (isset($_POST[$this->twofas_param_array][$key])) {
            return $_POST[$this->twofas_param_array][$key];
        }

        return null;
    }

    /**
     * @param string $action_id
     *
     * @return bool
     */
    public function is_valid_action_call($action_id)
    {
        return $this->is_post_request()
            && $this->has_nonce()
            && (1 === wp_verify_nonce($this->get_nonce(), $action_id));
    }

    /**
     * @return bool
     */
    public function is_post_request()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * @return bool
     */
    public function has_nonce()
    {
        return isset($_POST['_wpnonce']);
    }

    /**
     * @return null|string
     */
    public function get_nonce()
    {
        if (isset($_POST['_wpnonce'])) {
            return $_POST['_wpnonce'];
        }

        return null;
    }

    /**
     * @return null|TwoFAS_Action
     */
    public function get_action()
    {
        return $this->action;
    }

    /**
     * @param string $parameter
     *
     * @return null|string
     */
    public function get_from_get($parameter)
    {
        return $this->get_from($_GET, $parameter);
    }

    /**
     * @param string $parameter
     *
     * @return null|string
     */
    public function get_from_post($parameter)
    {
        return $this->get_from($_POST, $parameter);
    }

    /**
     * @param string $parameter
     *
     * @return null|string
     */
    public function get_from_request($parameter)
    {
        return $this->get_from($_REQUEST, $parameter);
    }

    /**
     * @param array  $source
     * @param string $item
     *
     * @return string|null
     */
    private function get_from(array $source, $item)
    {
        if (isset($source[$item])) {
            return $source[$item];
        }

        return null;
    }
}
