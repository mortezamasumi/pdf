<?php

namespace Mortezamasumi\Pdf;

use Elibyy\TCPDF\TCPDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Mortezamasumi\Pdf\Pages\ReportPage;

class Pdf extends TCPDF
{
    private string $pdfPath = '';

    public function create(
        $title                = '',        // title on top of header
        $subtitle             = '',        // subtitle under title
        $pdfViewerTitle       = null,      // title on left top of pdf viewer
        $footerText           = '',        // footer text on right on ltr and left on rtl, can be html
        $orientation          = 'P',       // orientation P or L
        $format               = 'A4',      // page type A1-A9 B1-B9
        $mT                   = 5,         // margin top between edge and top of header
        $mB                   = 5,         // margin bottom between edge and bottom of footer
        $mR                   = 10,        // margin right
        $mL                   = 10,        // margin left
        $hH                   = 15,        // header height
        $fH                   = 5,         // footer height
        $headerHtml           = '',        // html under subtitle
        $footerHtml           = '',        // html above page number row
        $noHeader             = false,     // no header
        $noFooter             = false,     // no footer
        $headerFontSize       = 16,        // header font size
        $headerSubFontSize    = 12,        // header subtitle font size
        $footerFontSize       = 8,         // footer font size
        $headerFontType       = 'xtitre',  // header font
        $headerSubFontType    = 'xhoma',   // subtitle font
        $footerFontType       = null,      //
        $showFooterPageNumber = true,
        $showFooterDateTime   = true,
        $rtl                  = false,
    ) {
        if (!$noHeader) {
            $this->setHeaderCallback(function ($pdf) use ($title, $subtitle, $mL, $mT, $headerHtml, $headerFontSize, $headerSubFontSize, $headerFontType, $headerSubFontType) {
                if (app()->getLocale() === 'en') {
                    $titleStyle = "text-align:center;font-size:$headerFontSize;font-family:times;font-weight:bold;";
                    $subStyle   = "text-align:center;font-size:$headerSubFontSize;font-family:helvetica;";
                } else {
                    $titleStyle = "text-align:center;font-size:$headerFontSize;font-family:$headerFontType;";
                    $subStyle   = "text-align:center;font-size:$headerSubFontSize;font-family:$headerSubFontType;";
                }

                $html = "<table><tr><td style=\"$titleStyle\">$title</td></tr><tr><td style=\"line-height:25%\"></td></tr><tr><td style=\"$subStyle\">$subtitle</td></tr></table>";

                $pdf->writeHTMLCell(0, 0, $mL, $mT, "$html$headerHtml");
            });
        }

        if (!$noFooter) {
            $this->setFooterCallback(function ($pdf) use ($footerFontType, $footerFontSize, $footerText, $mL, $mB, $footerHtml, $showFooterPageNumber, $showFooterDateTime) {
                if (app()->getLocale() === 'fa') {
                    $pdf->SetFont($footerFontType ?? 'hiwebmitra', '', $footerFontSize);
                    $html = '
                <table>
                    <tr>
                    <td style="width:40%;text-align:right;" width="40%">' . $footerText . '</td>
                    <td style="width:20%;text-align:right;" width="20%">'
                        . ($showFooterPageNumber
                               ? __('pdf::pdf.page_full', [
                                   'page'  => $pdf->getAliasNumPage(),
                                   'total' => $pdf->getAliasNbPages(),
                               ])
                               : '')
                        . '</td>
                    <td style="width:40%;text-align:left;" width="40%">'
                        . ($showFooterDateTime
                               ? __('pdf::pdf.report_date', [
                                   'date' => fbDateTime('H:i Y/m/d', now()),
                               ])
                               : '')
                        . '</td>
                    </tr>
                </table>
            ';
                } else {
                    $pdf->SetFont($footerFontType ?? 'helvetica', '', $footerFontSize);
                    $html = '
              <table width="100%">
                <tr width="100%">
                  <td style="width:40%;text-align:left;" width="33%">' . $footerText . '</td>
                  <td style="width:20%;text-align:center;" width="33%">'
                        . ($showFooterPageNumber
                               ? __('pdf::pdf.page_full', [
                                   'page'  => $pdf->getAliasNumPage(),
                                   'total' => $pdf->getAliasNbPages(),
                               ])
                               : '')
                        . '</td>
                  <td style="width:40%;text-align:right;" width="33%">'
                        . ($showFooterDateTime
                               ? __('pdf::pdf.report_date', [
                                   'date' => now()->format('Y/m/d H:i'),
                               ])
                               : '')
                        . '</td>
                </tr>
              </table>
            ';
                }

                $pdf->writeHTMLCell(0, 0, $mL, $pdf->getPageHeight() - $pdf->getStringHeight(0, 'A') - $mB, "$footerHtml$html");
            });
        }

        $this->SetTitle($pdfViewerTitle ?? 'PDF');
        $this->SetAutoPageBreak(true, $mB + $fH);
        $this->SetMargins($mL, $mT + $hH, $mR);
        $this->AddPage($orientation, $format);
        $this->SetRTL($rtl);

        if (!Storage::disk('public')->exists('/temp')) {
            Storage::disk('public')->makeDirectory('/temp');
        }

        $this->pdfPath = str('temp/')->append(str()->random(30))->append('.pdf')->toString();

        return $this;
    }

    public function renderPdf(?string $title = null, ?string $back = null): Redirector|RedirectResponse
    {
        $this->Output($this->getPath(), 'F');

        return redirect()->action(
            ReportPage::class,
            [
                'path'  => $this->getEmbedPath(),
                'title' => $title,
                'back'  => $back,
            ],
        );
    }

    public function getEmbedPath(): ?string
    {
        return Storage::url($this->pdfPath);
    }

    public function getPath(): ?string
    {
        return Storage::disk('public')->path("/{$this->pdfPath}");
    }

    public function clear(int $days): void
    {
        if (!Storage::disk('public')->exists('/temp')) {
            return;
        }

        $tempDir = Storage::disk('public')->path('/temp');

        $files = File::files($tempDir);

        foreach ($files as $file) {
            /** @disregard */
            if ($days === 0 || Carbon::createFromTimestamp(File::lastModified($file))->lt(Carbon::now()->subDays($days))) {
                File::delete($file);
            }
        }
    }
}
