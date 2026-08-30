<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class PdfRenderingTest extends TestCase
{
    public function test_pdf_renderer_generates_valid_pdf_output(): void
    {
        $output = Pdf::loadHTML('<html><body><p>PermiGest</p></body></html>')->output();

        $this->assertStringStartsWith('%PDF-', $output);
    }
}
