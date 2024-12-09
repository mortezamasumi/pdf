<?php

namespace Mortezamasumi\Pdfreport\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Mortezamasumi\PdfReport\Enums\ReporterFillColor;
use Mortezamasumi\PdfReport\Enums\ReporterLineStyle;
use Mortezamasumi\PdfReport\PdfReport;

trait HasPdfReport
{
    protected PDFReport $pdf;

    public function createPdf(): void
    {
        $this->pdf = \Mortezamasumi\PdfReport\Facades\PdfReport::create(
            title: $this->getReportTitleText(),
            subtitle: $this->getReportSubTitleText(),
            pdfViewerTitle: $this->getPdfViewerTitle(),
            footerText: $this->getFooterText(),
            orientation: $this->getOrientation(),
            format: $this->getFormat(),
            mT: $this->getTM(),
            mB: $this->getBM(),
            mR: $this->getSM(),
            mL: $this->getEM(),
            hH: $this->getHH(),
            fH: $this->getFH(),
            headerHtml: $this->getHeaderHtml(),
            footerHtml: $this->getFooterHtml(),
            noHeader: $this->getNoHeader(),
            noFooter: $this->getNoFooter(),
            headerFontSize: $this->getReportTitleFontSize(),
            headerSubFontSize: $this->getReportSubTitleFontSize(),
            footerFontSize: $this->getFooterFontSize(),
            headerFontType: $this->getReportTitleFontType(),
            headerSubFontType: $this->getReportSubTitleFontType(),
            footerFontType: $this->getFooterFontType(),
            showFooterPageNumber: $this->showPageNumber(),
            showFooterDateTime: $this->showDatetime(),
            rtl: $this->isRtl(),
        );
    }

    public function getPdf(): PDFReport
    {
        return $this->pdf;
    }

    public function cell(
        ?string $text            = '',
        ?float $x                = null,
        ?float $y                = 0,
        ?float $w                = null,
        ?float $h                = 0,
        string $tag              = 'span',
        string $font             = '',
        int $size                = 14,
        string $style            = '',
        mixed $border            = 0,
        int $ln                  = 0,
        ?ReporterFillColor $fill = ReporterFillColor::WHITE,
        bool $reseth             = true,
        string $align            = '',
        bool $autopadding        = true,
        string $lineHeight       = '150%',
        bool $fitWidth           = false,
    ): void {
        $x = $x === null ? $this->getSM() : $x;

        $w = $w === null
                 ? (
                     $fitWidth
                         ? $this->pdf->getPageDimensions()['wk'] - $this->getSM() - $this->getEM()
                         : $this->pdf->getPageDimensions()['wk'] - $this->getSM() - $this->getEM()
                 )
                 : $w;

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', $fill->value));
        }

        $this->pdf->setFont(family: $font, style: $style, size: $size);

        $this->pdf->WriteHTMLCell($w, $h, $x, $y, "<$tag style=\"line-height:$lineHeight;\">$text</$tag>", $border, $ln, !!$fill, $reseth, $align, $autopadding);

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', ReporterFillColor::WHITE->value));
        }
    }

    public function text(
        ?string $text            = '',
        string $font             = '',
        int $size                = 14,
        string $style            = '',
        bool $ln                 = true,
        ?ReporterFillColor $fill = ReporterFillColor::WHITE,
        bool $reseth             = true,
        string $align            = '',
        string $lineHeight       = '100%',
    ): void {
        $this->pdf->setFont(family: $font, style: $style, size: $size);

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', $fill->value));
        }

        $this->pdf->WriteHTML(html: "<span style=\"line-height:$lineHeight;\">$text</span>", ln: $ln, fill: !!$fill, reseth: $reseth, align: $align);

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', ReporterFillColor::WHITE->value));
        }
    }

    public function image(
        string $path,
        ?float $x       = null,
        ?float $y       = null,
        ?float $w       = null,
        ?float $h       = null,
        string $type    = '',
        mixed $link     = '',
        string $align   = '',     // T:top - M:middle - B:buttom - N:next line
        mixed $resize   = false,
        int $dpi        = 300,
        string $palign  = '',     // L:left align - C:center - R:right align
        bool $ismask    = false,
        mixed $imgmask  = false,
        mixed $border   = 0,      // 0:no border - 1:frame   or   L:left - T:top - R:right - B:buttom
        mixed $fitbox   = false,  // true: fitt box   or  two chars  L/C/R  + T/M/B  to fit w and h
        bool $hidden    = false,
        bool $fitonpage = false,  // true: not exceed page dimensions
        bool $alt       = false,  // true: id will print
        mixed $altimgs  = [],     // array of alt images
    ): mixed {
        return $this->pdf->Image(
            $path,
            $x,
            $y,
            $w,
            $h,
            $type,
            $link,
            $align,
            $resize,
            $dpi,
            $palign,
            $ismask,
            $imgmask,
            $border,
            $fitbox,
            $hidden,
            $fitonpage,
            $alt,
            $altimgs,
        );
    }

    public function rect(
        ?float $x                       = null,
        ?float $y                       = 0,
        ?float $w                       = null,
        ?float $h                       = 0,
        string $style                   = '',
        string $border                  = '',
        ReporterLineStyle $border_style = ReporterLineStyle::NORMAL_LINE,
        ReporterFillColor $color        = ReporterFillColor::WHITE,
    ): void {
        $x = $x === null ? (app()->getLocale() === 'fa' ? $this->getEM() : $this->getSM()) : $x;

        $w = $w === null ? $this->pdf->getPageDimensions()['wk'] - $this->getSM() - $this->getEM() : $w;

        $this->pdf->setLineStyle($border_style->getValue());

        $this->pdf->Rect($x, $y, $w, $h, $style, $border, explode(',', $color->value));

        $this->pdf->setLineStyle(ReporterLineStyle::NORMAL_LINE->getValue());
    }

    public function roundedRect(
        ?float $x                       = null,
        ?float $y                       = null,
        ?float $w                       = null,
        ?float $h                       = null,
        float $r                        = 1.5,
        string $round_corner            = '1111',
        string $style                   = '',
        ReporterLineStyle $border_style = ReporterLineStyle::NORMAL_LINE,
        ReporterFillColor $color        = ReporterFillColor::WHITE,
    ): void {
        $pd = $this->getPageDim();

        // dd($pd);

        $x = $x === null ? $pd['lm'] : $x;

        $y = $y === null ? $pd['tm'] : $y;

        $w = $w === null ? $pd['wk'] - $x - $pd['rm'] : $w;

        $h = $h === null ? $pd['hk'] - $y - $pd['bm'] : $h;

        $this->pdf->setLineStyle($border_style->getValue());

        $this->pdf->RoundedRect($x, $y, $w, $h, $r, $round_corner, $style, $border_style, $color->value);

        $this->pdf->setLineStyle(ReporterLineStyle::NORMAL_LINE->getValue());
    }

    public function textBox(
        array|Collection|string $data   = '',
        int $columns                    = 1,
        string $font                    = '',
        int $size                       = 14,
        string $textStyle               = '',
        string $tag                     = 'span',
        ?string $defaultLineHeight      = '100%',
        ?string $defaultAlign           = 'C',
        ?string $defaultBorder          = '0',
        ?float $rowHeight               = null,
        ?float $x                       = null,
        ?float $y                       = null,
        ?float $w                       = null,
        ?float $h                       = null,
        float $r                        = 1.5,
        string $round_corner            = '1111',
        string $style                   = '',
        ReporterLineStyle $border_style = ReporterLineStyle::NORMAL_LINE,
        bool $boxBorder                 = false,
        ?ReporterFillColor $fill        = ReporterFillColor::WHITE,
    ) {
        if ($columns < 1) {
            return;
        }

        $pd = $this->getPageDim();

        $x = $x === null ? $pd['lm'] : $x;

        $y = $y === null ? $pd['tm'] : $y;

        $w = $w === null ? $pd['wk'] - $x - $pd['rm'] : $w;

        if (is_string($data)) {
            $data = collect([collect(['text' => $data])])->each(fn(&$item) => $item['span'] ??= 1);
        } elseif (is_array($data)) {
            $data = collect($data)->map(fn($item) => is_array($item) || $item instanceof Collection ? collect($item) : collect(['text' => $item]))->each(fn(&$item) => $item['span'] ??= 1);
        } elseif ($data instanceof Collection) {
            $data = $data->map(fn($item) => is_array($item) || $item instanceof Collection ? collect($item) : collect(['text' => $item]))->each(fn(&$item) => $item['span'] ??= 1);
        }

        // $data = $data->map(fn($data) => Collection::wrap($data))->each(fn(&$item) => $item['span'] ??= 1)->dd();

        // $columns = min($columns, $data->count());

        // $totalSpan = $data->take($columns)->sum('span') ?? 1;
        $totalSpan = $columns;

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', $fill->value));
        }

        $this->pdf->setFont(family: $font, style: $textStyle, size: $size);

        $xPtr = $x;

        $yPtr = $y;

        $columnCounter = 1;

        // $tempPdf = clone ($this->getPdf());

        // $p1 = $tempPdf->GetY();

        // $tempPdf->WriteHTMLCell(10, 0, 0, 0, 'A', 1, false, true, 'C', true);

        // $rowHeight = $rowHeight === null ? $tempPdf->GetY() - $p1 : $rowHeight;

        $data = Collection::wrap($data)
                    ->map(fn($data) => Collection::wrap($data))
                    ->each(function (&$item) use ($totalSpan, $tag, $pd, $x, &$xPtr, &$yPtr, $w, $rowHeight, $fill, &$columnCounter, $columns, $defaultLineHeight, $defaultAlign, $defaultBorder) {
                        $text = $item['text'] ?? '';

                        $lineHeight = $item['lineHeight'] ?? $defaultLineHeight;

                        $cellWidth = Number::format($w * $item['span'] / $totalSpan, 4);

                        $this->pdf->WriteHTMLCell(
                            $cellWidth,
                            $rowHeight,
                            $xPtr,
                            $yPtr,
                            "<$tag style=\"line-height:$lineHeight;\">$text</$tag>",
                            $item['border'] ?? $defaultBorder,
                            0,
                            !!$fill,
                            true,
                            $item['align'] ?? $defaultAlign,
                            true
                        );

                        $columnCounter += $item['span'];
                        // $columnCounter++;

                        $xPtr += $cellWidth;

                        if ($columnCounter > $columns) {
                            $columnCounter = 1;

                            $xPtr = $x;

                            $yPtr += ($rowHeight);
                        }
                    });

        if ($boxBorder) {
            $h = $h === null ? $yPtr - $pd['tm'] : $h;

            $this->pdf->setLineStyle($border_style->getValue());

            $this->pdf->RoundedRect($x, $y, $w, $h, $r, $round_corner, $style, [], []);

            $this->pdf->setLineStyle(ReporterLineStyle::NORMAL_LINE->getValue());
        }

        if ($fill) {
            $this->pdf->SetFillColor(...explode(',', ReporterFillColor::WHITE->value));
        }
    }

    public function line(
        ?float $x1               = null,
        ?float $y1               = null,
        ?float $x2               = null,
        ?float $y2               = null,
        ReporterLineStyle $style = ReporterLineStyle::NORMAL_LINE,
    ): void {
        $pd = $this->getPageDim();

        $x1 = $x1 === null ? $pd['lm'] : $x1;

        $y1 = $y1 === null ? $pd['tm'] : $y1;

        $x2 = $x2 === null ? $pd['wk'] - $pd['rm'] : $x2;

        $y2 = $y2 === null ? $pd['hk'] - $pd['bm'] : $y2;

        $this->pdf->setLineStyle($style->getValue());

        $this->pdf->Line($x1, $y1, $x2, $y2);

        $this->pdf->setLineStyle(ReporterLineStyle::NORMAL_LINE->getValue());
    }

    public function multiLines(
        ?float $x1               = null,
        ?float $y1               = null,
        ?float $x2               = null,
        ?float $y2               = null,
        ReporterLineStyle $style = ReporterLineStyle::NORMAL_LINE,
        int $count               = 1,
        float $space             = 10,
    ): void {
        $pd = $this->getPageDim();

        $x1 = $x1 === null ? $pd['lm'] : $x1;

        $y1 = $y1 === null ? $pd['tm'] : $y1;

        $x2 = $x2 === null ? $pd['wk'] - $pd['rm'] : $x2;

        $y2 = $y2 === null ? $y1 : $y2;

        $this->pdf->setLineStyle($style->getValue());

        for ($y = $y1; $y < ($y1 + ($count * 10)); $y += $space) {
            $this->pdf->Line($x1, $y, $x2, $y + ($y2 - $y1));
        }

        $this->pdf->setLineStyle(ReporterLineStyle::NORMAL_LINE->getValue());
    }

    public function html(string $html): void
    {
        $this->pdf->writeHTML($html, true, false, false, false, '');
    }

    public function fill(ReporterFillColor $color = ReporterFillColor::WHITE): void
    {
        $this->pdf->setFillColor(...explode(',', $color->value));
    }

    public function getPageDim(): array
    {
        return $this->pdf->getPageDimensions();
    }

    public function getReportTitleText(): ?string
    {
        return null;
    }

    public function getReportTitleFontType(): string
    {
        return 'xtitre';
    }

    public function getReportTitleFontSize(): int|string
    {
        return 14;
    }

    public function getReportSubTitleText(): ?string
    {
        return null;
    }

    public function getReportSubTitleFontType(): string
    {
        return 'xnazanin';
    }

    public function getReportSubTitleFontSize(): int|string
    {
        return 10;
    }

    public function getPdfViewerTitle(): ?string
    {
        return $this->getReportTitleText();
    }

    public function getFooterText(): ?string
    {
        return null;
    }

    public function getOrientation(): string
    {
        return 'P';
    }

    public function getFormat(): string
    {
        return 'A4';
    }

    public function getTM(): int
    {
        return 5;
    }

    public function getBM(): int
    {
        return 5;
    }

    public function getSM(): int
    {
        return 10;
    }

    public function getEM(): int
    {
        return 10;
    }

    public function getHH(): int
    {
        return ($this->getReportTitleText() ? 10 : 0) + ($this->getReportSubTitleText() ? 6 : 0);
    }

    public function getFH(): int
    {
        return 5;
    }

    public function getHeaderHtml(): string
    {
        return '';
    }

    public function getFooterHtml(): string
    {
        return '';
    }

    public function getNoHeader(): bool
    {
        return false;
    }

    public function getNoFooter(): bool
    {
        return false;
    }

    public function getFooterFontSize(): int
    {
        return 8;
    }

    public function getFooterFontType(): ?string
    {
        return null;
    }

    public function showPageNumber(): bool
    {
        return true;
    }

    public function showDatetime(): bool
    {
        return true;
    }

    public function addPage(): void
    {
        $this->pdf->addPage();
    }

    public function isRtl(): bool
    {
        return app()->getLocale() === 'fa';
    }
}
