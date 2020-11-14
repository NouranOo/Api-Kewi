<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
 
class VerifyUserLink extends Notification
{
 use Queueable;

/** @var user */
 public $username;
 public $verfiylink;

/**
 * @param code $loan
 */
 public function __construct($username,$verfiylink)
 {
 $this->username = $username;
 $this->verfiylink = $verfiylink;
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
 ->subject('Email Verfication Link!')
 ->greeting('Dear'.' '.$this->username)
 ->line('Please use the following Link to verify your email address '.' '.$this->verfiylink);
 

 }
}