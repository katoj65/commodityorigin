<?php

namespace App\Helpers;

use App\Models\Lot;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeHelper
{
    /**
     * Generate a QR code as an SVG markup string for the given payload.
     * The SVG carries no XML prolog or fixed dimensions, so it can be
     * embedded inline in a page and scaled with CSS.
     */
    public static function generate(string $data, int $scale = 6): string
    {
        $options = new QROptions([
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'svgAddXmlHeader' => false,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Generate the QR code for a lot. It encodes the lot's traceability
     * URL, so scanning the code opens the lot's traceability timeline and
     * the lot becomes traceable end-to-end from a printed label.
     */
    public static function forLot(Lot $lot): string
    {
        return self::generate(self::lotUrl($lot));
    }

    /**
     * Generate the lot's QR code as a standalone, downloadable SVG document
     * (with the XML prolog a self-contained .svg file needs). Used by the
     * "Download QR" action on the traceability page.
     */
    public static function forLotFile(Lot $lot): string
    {
        $options = new QROptions([
            'eccLevel' => EccLevel::M,
            'scale' => 6,
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'svgAddXmlHeader' => true,
        ]);

        return (new QRCode($options))->render(self::lotUrl($lot));
    }

    /**
     * The URL encoded into a lot's QR code.
     */
    public static function lotUrl(Lot $lot): string
    {
        return route('lot.traceability', $lot);
    }
}
