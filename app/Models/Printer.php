<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class Printer extends Model
{
    public const STATION_COLD_BAR = 'cold_bar';

    public const STATION_HOT_BAR = 'hot_bar';

    public const STATION_CASHIER = 'cashier';

    public const CONNECTION_USB = 'usb';

    public const CONNECTION_NETWORK = 'network';

    protected $fillable = [
        'name',
        'station',
        'connection_type',
        'usb_path',
        'network_ip',
        'network_port',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'network_port' => 'integer',
    ];

    public static function getStationOptions(): array
    {
        return [
            self::STATION_COLD_BAR => 'Barra Fría',
            self::STATION_HOT_BAR => 'Barra Caliente',
            self::STATION_CASHIER => 'Caja',
        ];
    }

    public static function getConnectionOptions(): array
    {
        return [
            self::CONNECTION_USB => 'USB',
            self::CONNECTION_NETWORK => 'Red/Ethernet',
        ];
    }

    public function getStationLabelAttribute(): string
    {
        return self::getStationOptions()[$this->station] ?? $this->station;
    }

    public function getConnector()
    {
        if ($this->connection_type === self::CONNECTION_USB) {
            return new FilePrintConnector($this->usb_path);
        }

        return new NetworkPrintConnector($this->network_ip, $this->network_port);
    }

    public static function findByStation(string $station): ?self
    {
        return self::where('station', $station)
            ->where('is_active', true)
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
