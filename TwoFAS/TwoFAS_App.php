<?php

namespace TwoFAS;

use TwoFAS\Actions\Result\TwoFAS_Action_Result_Factory;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Channels\TwoFAS_Channel_Factory;
use TwoFAS\SDK\TwoFAS_SDK_Bridge;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Admin\TwoFAS_Main_Menu;
use TwoFAS\Request\TwoFAS_Request;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Templates\TwoFAS_Template;
use TwoFAS\User\TwoFAS_User;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;
use TwoFAS_Uninstaller;

class TwoFAS_App
{
    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @var TwoFAS_Request
     */
    private $request;

    /**
     * @var null|TwoFAS_Action
     */
    private $action;

    /**
     * @var TwoFAS_SDK_Bridge
     */
    private $sdk_bridge;

    /**
     * @var TwoFAS_Main_Menu
     */
    private $admin_menu;

    /**
     * @var TwoFAS_User
     */
    private $user;

    /**
     * @var TwoFAS_Uninstaller
     */
    private $uninstaller;

    /**
     * @var TwoFAS_Channel_Factory
     */
    private $channel_factory;

    /**
     * @var TwoFAS_Action_Result_Factory
     */
    private $result_factory;

    /**
     * TwoFAS_App constructor.
     *
     * @param TwoFAS_Storage         $storage
     * @param TwoFAS_Request         $request
     * @param TwoFAS_SDK_Bridge      $sdk_bridge
     * @param TwoFAS_Uninstaller     $uninstaller
     * @param TwoFAS_Channel_Factory $channel_factory
     * @param TwoFAS_Template        $template
     */
    public function __construct(
        TwoFAS_Storage         $storage,
        TwoFAS_Request         $request,
        TwoFAS_SDK_Bridge      $sdk_bridge,
        TwoFAS_Uninstaller     $uninstaller,
        TwoFAS_Channel_Factory $channel_factory,
        TwoFAS_Template        $template
    ) {
        $this->storage         = $storage;
        $this->request         = $request;
        $this->action          = $request->get_action();
        $this->sdk_bridge      = $sdk_bridge;
        $this->uninstaller     = $uninstaller;
        $this->channel_factory = $channel_factory;
        $this->template        = $template;
        $this->result_factory  = new TwoFAS_Action_Result_Factory($this->template, $this->request);

        $this->user = new TwoFAS_User(
            $this->storage->get_userdata(),
            $this->storage->get_options(),
            $this->sdk_bridge->get_api()
        );
    }

    /**
     * @return TwoFAS_User
     */
    public function get_user()
    {
        return $this->user;
    }

    /**
     * @return TwoFAS_Storage
     */
    public function get_storage()
    {
        return $this->storage;
    }

    /**
     * @return TwoFAS_Request
     */
    public function get_request()
    {
        return $this->request;
    }

    /**
     * @return null|TwoFAS_Action
     */
    public function get_action()
    {
        return $this->action;
    }

    /**
     * @return TwoFAS_SDK_Bridge
     */
    public function get_sdk_bridge()
    {
        return $this->sdk_bridge;
    }

    /**
     * @return TwoFAS_Uninstaller
     */
    public function get_uninstaller()
    {
        return $this->uninstaller;
    }

    /**
     * @return TwoFAS_Authentication_Channels
     *
     * @throws User_Zone_Exception
     */
    public function get_authentication_channels()
    {
        $user_zone               = $this->sdk_bridge->get_user_zone();
        $oauth_storage           = $this->storage->get_oauth();
        $token                   = $oauth_storage->retrieveToken('wordpress');
        $integration_id          = $token->getIntegrationId();
        $integration             = $user_zone->getIntegration($integration_id);
        $client                  = $user_zone->getClient();
        $authentication_channels = new TwoFAS_Authentication_Channels($integration, $client);

        return $authentication_channels;
    }

    /**
     * @return TwoFAS_Channel_Factory
     */
    public function get_channel_factory()
    {
        return $this->channel_factory;
    }

    /**
     * @return TwoFAS_Template
     */
    public function get_template()
    {
        return $this->template;
    }

    /**
     * @return TwoFAS_Action_Result_Factory
     */
    public function get_result_factory()
    {
        return $this->result_factory;
    }

    public function run()
    {
        if ($this->action) {
            $result = $this->action->execute_own_strategy($this);
            
            if ($result) {
                $result->handle($this->action);
            }
        }

        $this->admin_menu = new TwoFAS_Main_Menu($this);
    }
}
