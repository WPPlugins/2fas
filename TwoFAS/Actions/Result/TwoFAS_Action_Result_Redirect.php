<?php

namespace TwoFAS\Actions\Result;

use InvalidArgumentException;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Notifications\TwoFAS_Notifications_Collection;

class TwoFAS_Action_Result_Redirect implements TwoFAS_Action_Result
{
    /**
     * @var string
     */
    private $page_to_be_redirected;
    
    /**
     * @var string
     */
    private $action_to_be_redirected;
    
    /**
     * @var array
     */
    private $notification_keys = array();

    /**
     * @param TwoFAS_Action $action
     */
    public function handle(TwoFAS_Action $action)
    {
        $url = $this->generate_url_to_redirect_to(
            $action->get_current_url(),
            $this->page_to_be_redirected,
            $this->action_to_be_redirected,
            $this->notification_keys
        );

        $action->redirect($url);
    }

    /**
     * @param TwoFAS_Notifications_Collection $collection
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function consume_notifications(TwoFAS_Notifications_Collection $collection)
    {
        $this->notification_keys = array('twofas-notifications' => $collection->get_notifications_as_keys());
        return $this;
    }

    /**
     * @param string      $current_url
     * @param string|null $target_page
     * @param string|null $target_action
     * @param array       $additional_arguments
     *
     * @return string
     */
    public function generate_url_to_redirect_to($current_url, $target_page = null, $target_action = null, array $additional_arguments = array())
    {
        $parsed_url = parse_url($current_url);
        $url_query  = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $url_scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] : '';
        $url_host   = $parsed_url['host'];
        $url_path   = $parsed_url['path'];
        $arguments  = array();

        parse_str($url_query, $arguments);

        $arguments_to_be_rewritten = array();

        foreach ($arguments as $key => $argument) {
            if ($key != 'page' && $key != 'twofas-action') {
                $arguments_to_be_rewritten[$key] = $argument;
            }
        }

        // If no page has been supplied, redirect to the current page but with a different action
        // If no page has been supplied, and no page info has been found in url, just add additional arguments
        if (isset($arguments['page']) || $target_page) {
            $arguments_to_be_rewritten['page']          = $target_page ? $target_page : $arguments['page'];
            $arguments_to_be_rewritten['twofas-action'] = $target_action;
        }

        $arguments_to_be_rewritten = array_merge($arguments_to_be_rewritten, $additional_arguments);

        return $url_scheme . '://' . $url_host . $url_path . '?' . http_build_query($arguments_to_be_rewritten);
    }

    /**
     * @param string|null $page
     * @param string|null $action
     *
     * @return TwoFAS_Action_Result_Redirect
     *
     * @throws InvalidArgumentException
     */
    public function set_redirection_target($page = null, $action = null)
    {
        // Arguments have to be strings or nulls
        if ($this->is_null_or_string($page) && $this->is_null_or_string($action)) {
            $this->page_to_be_redirected   = $page;
            $this->action_to_be_redirected = $action;
            return $this;
        }

        throw new InvalidArgumentException('Invalid argument passed to set_redirection_target');
    }

    /**
     * @param string|null $arg
     *
     * @return bool
     */
    private function is_null_or_string($arg)
    {
        return is_null($arg) || is_string($arg);
    }
}
