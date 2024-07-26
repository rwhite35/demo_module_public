<?php
/**
 * Notifications | Acme/model/Notifications.php
 * @package Acme Models
 * @subpackage Notifications
 */
namespace Acme\Notifications;

class Notifications
{
    /**
     * @var string hash of a users credentials with salt
     */
    public $csrf_token;
    /**
     * @var string user id without hashing or salt
     */
    public $user_id;
    /**
     * @var int interval to check hourly(1), daily(2), weekly(3), monthly(4)
     */
    public $check_status;
    /**
     * @var object instance of input filter factory
     */
    protected $inputFilter;
}


?>