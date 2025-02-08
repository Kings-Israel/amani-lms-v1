<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Barryvdh\DomPDF\Facade as PDF;



class sendAutomatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $lo;
    public $count;
    public $branch_array;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lo, $count, $branch_array)
    {
        $this->lo = $lo;
        $this->count = $count;
        $this->branch_array = $branch_array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $today = Carbon::now()->format('D-M H:i:s');
        $pdf  = PDF::loadView('pdf.skipped-payments', ['lo'=>$this->lo, 'count'=>$this->count, 'branch_array'=>$this->branch_array])->setPaper('a4', 'landscape');
        return $this->from("info@litsacredit.co.ke", "LITSA CREDIT")
            ->Subject('Loan Arrears with skipped payments report '.$today)
            ->view('pdf.skipped-payments')
            ->attachData($pdf->output(), 'skipped-payments-'.$today.'.pdf');

    }
}
