<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserRoleUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $updatedBy;
    public $newRole;
    public $oldRole;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, User $updatedBy, $newRole, $oldRole = null)
    {
        $this->user = $user;
        $this->updatedBy = $updatedBy;
        $this->newRole = $newRole;
        $this->oldRole = $oldRole;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'บทบาท (Role) ของคุณถูกเปลี่ยนแล้ว - ระบบจัดการฟาร์มหมู',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_role_updated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
