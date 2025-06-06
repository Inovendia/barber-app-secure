<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Admin;

class AdminCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $password;

    public function __construct(Admin $admin, $password)
    {
        $this->admin = $admin;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('【重要】管理者アカウントが作成されました')
                    ->view('emails.admin_created');
    }
}
