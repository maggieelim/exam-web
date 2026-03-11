<?php

namespace App\Mail;

use App\Models\Exam;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamPublishedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $exam;
    public $user;
    public $categoriesResult;
    public $coordinator;
    /**
     * Create a new message instance.
     */
    public function __construct(Exam $exam, User $user, $categoriesResult, $coordinator)
    {
        $this->exam = $exam;
        $this->user = $user;
        $this->categoriesResult = $categoriesResult;
        $this->coordinator = $coordinator;
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
            view: 'emails.exams.published',
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
        $nim = optional($this->user->student)->nim ?? $this->user->name;

        $pdf = Pdf::loadView('pssk.grading.result-pdf', [
            'exam' => $this->exam,
            'student' => $this->user,
            'scores' => $this->categoriesResult,
            'coordinator' => $this->coordinator,
        ])->setPaper('A4', 'potrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);;


        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                $nim . '_' . $this->user->name . '.pdf'
            )->withMime('application/pdf')
        ];
    }
}
