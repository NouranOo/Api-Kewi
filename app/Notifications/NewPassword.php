<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
class NewPassword extends Notification
{
 use Queueable;

/** @var username */
 public $username;
 public $newpassword;

/**
 * @param code $loan
 */
 public function __construct($username,$newpassword)
 {
 $this->username = $username;
 $this->newpassword = $newpassword;
 }

/**
 * Get the notification’s delivery channels.
 *
 * @param mixed $notifiable
 * @return array
 */
 public function via($notifiable)
 {
 return ['mail'];
 }

/**
 * Get the mail representation of the notification.
 *
 * @param mixed $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
 public function toMail($notifiable)
 {
 return (new MailMessage)
 ->subject('Wink Password Reset!')
 ->greeting('Dear'.'  '.$this->username)
 ->line('Please use the following New Password to sign in to your account   '.$this->newpassword.' ');

 }
}