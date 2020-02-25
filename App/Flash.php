<?php

namespace App;

/**
 * Flash notification messages: messages for one-time display using the session
 * for storage between requests.
 *
 * PHP version 7.0
 */
class Flash
{

    /**
     * Success message type
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * Information message type
     * @var string
     */
    const INFO = 'info';

    /**
     * Ok message type
     * @var string
     */
    const OK = 'ok';

    /**
     * Warning message type
     * @var string
     */
    const WARNING = 'warning';

    /**
     * Danger message type
     * @var string
     */
    const DANGER = 'danger';

    /**
     * Add a message
     *
     * @param string $message  The message content
     * @param string $type  The optional message type, defaults to SUCCESS
     *
     * @return void
     */
    public static function addMessage($message, $type = 'success')
    {
        // Create array in the session if it doesn't already exist
        if (! isset($_SESSION['flash_notifications'])) {
            $_SESSION['flash_notifications'] = [];
        }

        // Append the message to the array
        $_SESSION['flash_notifications'][] = [
            'body' => $message,
            'type' => $type
        ];
    }

    /**
     * Add an alert
     *
     * @param string $message  The message content
     * @param string $type  The optional message type, defaults to warning
     *
     * @return void
     */
    public static function addAlert($message, $type = 'warning')
    {
        // Create array in the session if it doesn't already exist
        if (!isset($_SESSION['flash_alerts'])) {
            $_SESSION['flash_alerts'] = [];
        }

        // Append the message to the array
        $_SESSION['flash_alerts'][] = [
            'body' => $message,
            'type' => $type
        ];
    }


    /**
     * Get all the messages
     *
     * @return mixed  An array with all the messages or null if none set
     */
    public static function getMessages()
    {
        if (isset($_SESSION['flash_notifications'])) {
            //return $_SESSION['flash_notifications'];
            $messages = $_SESSION['flash_notifications'];
            unset($_SESSION['flash_notifications']);

            return $messages;
        }
    }

    /**
     * Get all the alerts
     *
     * @return mixed  An array with all the alerts or null if none set
     */
    public static function getAlerts()
    {
        if (isset($_SESSION['flash_alerts'])) {
            $alerts = $_SESSION['flash_alerts'];
            unset($_SESSION['flash_alerts']);

            return $alerts;
        }
    }
}
