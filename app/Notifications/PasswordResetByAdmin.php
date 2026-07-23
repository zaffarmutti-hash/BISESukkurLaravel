<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetByAdmin extends Notification
{
    use Queueable;

    public function __construct(public User $admin) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password was reset')
            ->line('Your account password was reset by '.$this->admin->name.'.')
            ->line('Please log in with your new password and change it immediately.');
    }
}
