<?php

namespace App\Exceptions;

class ProfileException extends \Exception
{
    public static function emailTaken(): ProfileException
    {
        return new self(__('messages.update_profile.email_taken'), 400);
    }

    public static function wrongCurrentPassword(): ProfileException
    {
        return new self(__('messages.change_password.wrong_current'), 400);
    }
}
