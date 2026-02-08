<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Printer;
use App\Models\RestaurantConfig;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer as EscposPrinter;

class PrinterService
{
    public function printOrder(Order $order): array
    {
        $results = [];
        $order->load('items.dish.category');

        $itemsByStation = $this->groupItemsByStation($order);

        foreach ($itemsByStation as $station => $items) {
            if ($station === 'none' || empty($items)) {
                continue;
            }

            try {
                $success = $this->printToStation($order, $station, $items);
                $results[$station] = $success;
            } catch (\Exception $e) {
                Log::error("Error printing to {$station}: ".$e->getMessage());
                $results[$station] = false;
            }
        }

        return $results;
    }

    public function printToStation(Order $order, string $station, array $items = []): bool
    {
        $printer = Printer::findByStation($station);

        if (! $printer) {
            Log::warning("No active printer found for station: {$station}");

            return false;
        }

        try {
            $connector = $printer->getConnector();
            $escpos = new EscposPrinter($connector);

            $this->printKitchenTicket($escpos, $order, $items, $printer->station_label);

            $escpos->cut();
            $escpos->close();

            return true;
        } catch (\Exception $e) {
            Log::error("Printer error [{$printer->name}]: ".$e->getMessage());

            return false;
        }
    }

    public function printReceipt(Order $order): bool
    {
        $printer = Printer::findByStation(Printer::STATION_CASHIER);

        if (! $printer) {
            Log::warning('No active cashier printer found');

            return false;
        }

        try {
            $connector = $printer->getConnector();
            $escpos = new EscposPrinter($connector);

            $this->printCashierTicket($escpos, $order);

            $escpos->cut();
            $escpos->close();

            return true;
        } catch (\Exception $e) {
            Log::error('Cashier printer error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Reprint a ticket to the cashier printer.
     *
     * @param  string  $ticketType  'cold_bar', 'hot_bar', or 'receipt'
     */
    public function reprintToCashier(Order $order, string $ticketType): bool
    {
        $cashierPrinter = Printer::findByStation(Printer::STATION_CASHIER);

        if (! $cashierPrinter) {
            Log::warning('No active cashier printer found for reprint');

            return false;
        }

        $order->load('items.dish.category', 'payments');

        try {
            $connector = $cashierPrinter->getConnector();
            $escpos = new EscposPrinter($connector);

            if ($ticketType === 'receipt') {
                $this->printCashierTicket($escpos, $order);
            } else {
                $stationLabel = match ($ticketType) {
                    Printer::STATION_COLD_BAR => 'Barra Fría',
                    Printer::STATION_HOT_BAR => 'Barra Caliente',
                    default => $ticketType,
                };

                $items = $order->items->filter(function ($item) use ($ticketType) {
                    return ($item->dish->category->print_station ?? 'none') === $ticketType;
                })->values()->all();

                if (empty($items)) {
                    $escpos->close();

                    return false;
                }

                $this->printKitchenTicket($escpos, $order, $items, $stationLabel.' (REIMPRESIÓN)');
            }

            $escpos->cut();
            $escpos->close();

            return true;
        } catch (\Exception $e) {
            Log::error("Reprint error [{$ticketType}]: ".$e->getMessage());

            return false;
        }
    }

    public function testPrinter(Printer $printer): bool
    {
        try {
            $connector = $printer->getConnector();
            $escpos = new EscposPrinter($connector);

            $escpos->initialize();
            $escpos->setJustification(EscposPrinter::JUSTIFY_CENTER);
            $escpos->setEmphasis(true);
            $escpos->text("================================\n");
            $escpos->text("     TEST DE IMPRESORA\n");
            $escpos->text("================================\n");
            $escpos->setEmphasis(false);
            $escpos->feed(1);
            $escpos->text('Impresora: '.$printer->name."\n");
            $escpos->text('Estación: '.$printer->station_label."\n");
            $escpos->text('Conexión: '.($printer->connection_type === 'usb' ? 'USB' : 'Red')."\n");
            $escpos->feed(1);
            $escpos->text('Fecha: '.now()->format('d/m/Y H:i:s')."\n");
            $escpos->feed(1);
            $escpos->text("================================\n");
            $escpos->text("      IMPRESORA OK!\n");
            $escpos->text("================================\n");

            $escpos->cut();
            $escpos->close();

            return true;
        } catch (\Exception $e) {
            Log::error("Test printer error [{$printer->name}]: ".$e->getMessage());

            return false;
        }
    }

    private function groupItemsByStation(Order $order): array
    {
        $grouped = [];

        foreach ($order->items as $item) {
            $station = $item->dish->category->print_station ?? 'none';

            if (! isset($grouped[$station])) {
                $grouped[$station] = [];
            }

            $grouped[$station][] = $item;
        }

        return $grouped;
    }

    private function printKitchenTicket(EscposPrinter $printer, Order $order, array $items, string $stationLabel): void
    {
        $config = RestaurantConfig::config();
        $nombreEmpresa = $config->nombre_comercial ?: 'QuickRestaurant';

        $printer->initialize();
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);

        $printer->setEmphasis(true);
        $printer->text("================================\n");
        $printer->text("     {$nombreEmpresa}\n");
        $printer->text("================================\n");
        $printer->setEmphasis(false);

        $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
        $printer->feed(1);

        $printer->text('Orden: '.$order->order_number."\n");
        $printer->text('Mesa: '.($order->table_number ?: 'N/A')."\n");
        $printer->text('Cliente: '.($order->customer_name ?: 'N/A')."\n");
        $printer->text('Hora: '.$order->created_at->format('H:i')."\n");

        $printer->text("--------------------------------\n");
        $printer->setEmphasis(true);
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        $printer->text(mb_strtoupper($stationLabel)."\n");
        $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
        $printer->setEmphasis(false);
        $printer->text("--------------------------------\n");

        foreach ($items as $item) {
            $qty = $item->quantity;
            $name = $item->dish->name ?? 'Item';

            $printer->setEmphasis(true);
            $printer->text("{$qty}x {$name}\n");
            $printer->setEmphasis(false);

            if (! empty($item->special_instructions)) {
                $printer->text('   '.$item->special_instructions."\n");
            }
        }

        if (! empty($order->customer_notes)) {
            $printer->feed(1);
            $printer->text("--------------------------------\n");
            $printer->text('Notas: '.$order->customer_notes."\n");
        }

        $printer->feed(1);
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        $printer->text("================================\n");
        $printer->feed(2);
    }

    private function printCashierTicket(EscposPrinter $printer, Order $order): void
    {
        $order->load('items.dish', 'payments');

        $config = RestaurantConfig::config();
        $nombreEmpresa = $config->nombre_comercial ?: 'QuickRestaurant';
        $ivaPorcentaje = $config->iva_porcentaje ?: 16;

        $printer->initialize();
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);

        $printer->setEmphasis(true);
        $printer->text("================================\n");
        $printer->text("     {$nombreEmpresa}\n");
        $printer->text("================================\n");
        $printer->setEmphasis(false);

        if ($config->direccion) {
            $printer->text($config->direccion."\n");
        }
        if ($config->telefono) {
            $printer->text("Tel: {$config->telefono}\n");
        }

        $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
        $printer->feed(1);

        $printer->text('Orden: '.$order->order_number."\n");
        $printer->text('Mesa: '.($order->table_number ?: 'N/A')."\n");
        $printer->text('Cliente: '.($order->customer_name ?: 'N/A')."\n");
        $printer->text('Fecha: '.$order->created_at->format('d/m/Y H:i')."\n");

        $printer->text("--------------------------------\n");

        foreach ($order->items as $item) {
            $qty = $item->quantity;
            $name = mb_substr($item->dish->name ?? 'Item', 0, 20);
            $total = number_format($item->total_price, 2);

            $line = sprintf("%-3s %-20s %8s\n", $qty.'x', $name, '$'.$total);
            $printer->text($line);
        }

        $printer->text("--------------------------------\n");

        $subtotal = $order->total / (1 + ($ivaPorcentaje / 100));
        $iva = $order->total - $subtotal;

        $printer->text(sprintf("%-24s %8s\n", 'Subtotal:', '$'.number_format($subtotal, 2)));
        $printer->text(sprintf("%-24s %8s\n", "IVA ({$ivaPorcentaje}%):", '$'.number_format($iva, 2)));

        $printer->setEmphasis(true);
        $printer->text(sprintf("%-24s %8s\n", 'TOTAL:', '$'.number_format($order->total, 2)));
        $printer->setEmphasis(false);

        if ($order->payments->isNotEmpty()) {
            $printer->feed(1);
            $printer->text("--- PAGOS ---\n");

            foreach ($order->payments as $payment) {
                $method = ucfirst($payment->method);
                $amount = number_format($payment->amount, 2);
                $printer->text(sprintf("%-16s %16s\n", $method, '$'.$amount));

                if ($payment->tip > 0) {
                    $printer->text(sprintf("%-16s %16s\n", '  Propina:', '$'.number_format($payment->tip, 2)));
                }
            }

            $paid = $order->amountPaid();
            $remaining = $order->amountRemaining();

            $printer->text("--------------------------------\n");
            $printer->text(sprintf("%-24s %8s\n", 'Total Pagado:', '$'.number_format($paid, 2)));

            if ($remaining > 0) {
                $printer->text(sprintf("%-24s %8s\n", 'Pendiente:', '$'.number_format($remaining, 2)));
            } else {
                $printer->setEmphasis(true);
                $printer->text("         ** PAGADO **\n");
                $printer->setEmphasis(false);
            }
        }

        $printer->feed(1);
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        $printer->text("================================\n");
        $printer->text("    Gracias por su visita!\n");
        $printer->text("================================\n");
        $printer->feed(3);
    }
}
