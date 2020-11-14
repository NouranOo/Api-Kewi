<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
 
class VerifyUserEmail extends Notification
{
 use Queueable;

/** @var user */
 public $username;
 public $verfiycode;

/**
 * @param code $loan
 */
 public function __construct($username,$verfiycode)
 {
 $this->username = $username;
 $this->verfiycode = $verfiycode;
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
 ->subject('Email Verfication Code!')
 ->greeting('Dear'.' '.$this->username)
 ->line('Please use the following code to verify your email address '.' '.$this->verfiycode);
 

 }
}