<?php

namespace App\Http\Controllers;

use PDF;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function index() 
    {
        $pdf = PDF::loadView('pdf.sample', [
            'title' => 'CodeAndDeploy.com Laravel Pdf Tutorial',
           
        ]);
    
        return $pdf->download('sample.pdf');
    }
}