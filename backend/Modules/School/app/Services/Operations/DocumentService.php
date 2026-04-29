<?php

namespace Modules\School\Services\Operations;

use Mpdf\Mpdf;
use Modules\School\Models\Operations\DocumentTemplate;
use Illuminate\Support\Facades\Blade;

class DocumentService
{
    /**
     * Generate PDF from template.
     * 
     * @param DocumentTemplate $template
     * @param array<string, mixed> $data
     * @return string PDF binary data
     */
    public function generatePdf(DocumentTemplate $template, array $data): string
    {
        $html = $this->renderTemplate($template, $data);
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML((string) $template->styles, 1);
        $mpdf->WriteHTML($html, 2);

        return $mpdf->Output('', 'S');
    }

    /**
     * Render template with data using Blade engine.
     * 
     * @param DocumentTemplate $template
     * @param array<string, mixed> $data
     * @return string
     */
    protected function renderTemplate(DocumentTemplate $template, array $data): string
    {
        // Use Blade to render the content with placeholders
        return Blade::render($template->content, $data);
    }
}
