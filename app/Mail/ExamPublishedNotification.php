<?php

namespace App\Mail;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamPublishedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $exam;
    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct(Exam $exam, User $user)
    {
        $this->exam = $exam;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $courseName = optional($this->exam->course)->name ?? '';
        return new Envelope(
            subject: 'Hasil Persentase Penguasaan Materi '
                . $this->exam->title . ' '
                . $courseName . ' - '
                . $this->user->name
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.exams.published',
            with: [
                'exam' => $this->exam,
                'user' => $this->user,
                'course' => $this->exam->course,
            ]
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
