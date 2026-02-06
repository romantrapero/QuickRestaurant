<?php

namespace App\Http\Controllers;

use App\Models\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function downloadQr(Table $table)
    {
        $url = $table->qr_url;

        // Generar QR Code
        $qr = QrCode::format('png')
            ->size(512)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        // Devolver como descarga
        return response($qr)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="mesa-'.$table->numero.'-qr.png"');
    }
}
