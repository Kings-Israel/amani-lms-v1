<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Barryvdh\DomPDF\Facade as PDF;

class sendScoreSheetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $today = Carbon::now()->format('D-M H:i:s');
        $pdf  = PDF::loadView('pdf.score-sheet', ['data'=>$this->data])->setPaper('a4', 'landscape');
        return $this->from("info@litsacredit.co.ke", "LITSA CREDIT")
            ->Subject('Performance Tracker Score Sheet '. $today)
            ->view('pdf.score-sheet')
            ->attachData($pdf->output(), 'score-sheet-'.$today.'.pdf');
    }
}
