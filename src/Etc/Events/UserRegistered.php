<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Event System
 */

namespace App\Etc\Events;

/**
 * UserRegistered
 *
 * Dispatched when a user account is created. Carries whatever the dispatcher
 * passes in — typically user_id, email, name.
 *
 * Its own file so PSR-4 can autoload it: the rest of the event taxonomy is
 * still parked, commented out, in Event.php.
 */
class UserRegistered extends Event
{
}
