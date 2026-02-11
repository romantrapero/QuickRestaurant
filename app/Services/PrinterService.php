<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Printer;
use App\Models\RestaurantConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\Printer as EscposPrinter;

class PrinterService
{
    public function printOrder(Order $order, bool $onlyDelta = true): array
    {
        if ($onlyDelta) {
            return $this->printOrderDelta($order);
        }

        // Impresión completa (mantener para primera impresión)
        return $this->printOrderComplete($order);
    }

    /**
     * Imprime solo los cambios (delta) de la orden
     */
    public function printOrderDelta(Order $order): array
    {
        $results = [];

        // 1. Obtener items que necesitan impresión
        $newItems = $order->items()
            ->whereNull('printed_at')
            ->with('dish.category')
            ->get();

        $modifiedItems = $order->items()
            ->whereNotNull('printed_at')
            ->where('updated_at', '>', DB::raw('printed_at'))
            ->where('status', '!=', OrderItem::STATUS_CANCELLED)
            ->with('dish.category')
            ->get();

        $cancelledItems = $order->items()
            ->where('status', OrderItem::STATUS_CANCELLED)
            ->whereNotNull('printed_at')
            ->where(function ($q) {
                $q->where('updated_at', '>', DB::raw('printed_at'));
            })
            ->with('dish.category')
            ->get();

        // Si no hay cambios, no imprimir nada
        if ($newItems->isEmpty() && $modifiedItems->isEmpty() && $cancelledItems->isEmpty()) {
            return ['message' => 'No hay cambios para imprimir'];
        }

        // 2. Agrupar por estación de impresión
        $itemsByStation = $this->groupItemsByStationDelta($newItems, $modifiedItems, $cancelledItems);

        // 3. Imprimir a cada estación
        foreach ($itemsByStation as $station => $stationItems) {
            if ($station === 'none' || empty($stationItems)) {
                continue;
            }

            try {
                $result = $this->printDeltaToStation($order, $station, $stationItems);
                $results[$station] = $result;
            } catch (\Exception $e) {
                Log::error("Error printing delta to {$station}: ".$e->getMessage());
                $results[$station] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // 4. Marcar items como impresos
        foreach ($newItems->merge($modifiedItems)->merge($cancelledItems) as $item) {
            $item->markAsPrinted();
        }

        return $results;
    }

    /**
     * Imprime la orden completa (para primera impresión)
     */
    private function printOrderComplete(Order $order): array
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

        // Marcar todos los items como impresos
        foreach ($order->items as $item) {
            $item->markAsPrinted();
        }

        return $results;
    }

    public function printToStation(Order $order, string $station, array $items = []): bool
    {
        // MODO DESARROLLO: Simular impresión
        if (config('printing.simulate', false)) {
            return $this->simulatePrint($order, $station, $items, 'kitchen');
        }

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
        // MODO DESARROLLO: Simular impresión
        if (config('printing.simulate', false)) {
            return $this->simulatePrint($order, 'cashier', $order->items->all(), 'receipt');
        }

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

    /**
     * Agrupa items por estación, marcando tipo de cambio
     */
    private function groupItemsByStationDelta($newItems, $modifiedItems, $cancelledItems): array
    {
        $grouped = [];

        // Nuevos items
        foreach ($newItems as $item) {
            $station = $item->dish->category->print_station ?? 'hot_bar';
            $grouped[$station]['new'][] = $item;
        }

        // Items modificados
        foreach ($modifiedItems as $item) {
            $station = $item->dish->category->print_station ?? 'hot_bar';
            $grouped[$station]['modified'][] = $item;
        }

        // Items cancelados
        foreach ($cancelledItems as $item) {
            $station = $item->dish->category->print_station ?? 'hot_bar';
            $grouped[$station]['cancelled'][] = $item;
        }

        return $grouped;
    }

    /**
     * Imprime delta a una estación específica
     */
    private function printDeltaToStation(Order $order, string $station, array $items): array
    {
        // MODO DESARROLLO: Simular impresión
        if (config('printing.simulate', false)) {
            $this->simulatePrint($order, $station, $items, 'delta');

            return [
                'station' => $station,
                'success' => true,
                'simulated' => true,
            ];
        }

        $printer = Printer::findByStation($station);

        if (! $printer) {
            return [
                'station' => $station,
                'success' => false,
                'error' => 'No printer found for station',
            ];
        }

        try {
            $connector = $printer->getConnector();
            $escpos = new EscposPrinter($connector);

            $config = RestaurantConfig::config();
            $nombreEmpresa = $config->nombre_comercial ?: 'QuickRestaurant';

            // Header
            $escpos->initialize();
            $escpos->setJustification(EscposPrinter::JUSTIFY_CENTER);
            $escpos->setEmphasis(true);
            $escpos->setTextSize(2, 2);
            $escpos->text("*** MODIFICACION ***\n");
            $escpos->setTextSize(1, 1);
            $escpos->setEmphasis(false);
            $escpos->feed(1);

            $escpos->setJustification(EscposPrinter::JUSTIFY_LEFT);
            $escpos->text('Orden: '.$order->order_number."\n");
            $escpos->text('Mesa: '.($order->table_number ?? 'N/A')."\n");
            $escpos->text('Hora: '.now()->format('H:i:s')."\n");
            $escpos->text(str_repeat('-', 32)."\n\n");

            // Items nuevos
            if (! empty($items['new'])) {
                $escpos->setEmphasis(true);
                $escpos->text(">>> AGREGAR <<<\n");
                $escpos->setEmphasis(false);

                foreach ($items['new'] as $item) {
                    $this->printItem($escpos, $item);
                }
                $escpos->text("\n");
            }

            // Items modificados
            if (! empty($items['modified'])) {
                $escpos->setEmphasis(true);
                $escpos->text(">>> MODIFICAR <<<\n");
                $escpos->setEmphasis(false);

                foreach ($items['modified'] as $item) {
                    $this->printItem($escpos, $item, 'MODIFICADO');
                }
                $escpos->text("\n");
            }

            // Items cancelados
            if (! empty($items['cancelled'])) {
                $escpos->setEmphasis(true);
                $escpos->text(">>> CANCELAR <<<\n");
                $escpos->setEmphasis(false);

                foreach ($items['cancelled'] as $item) {
                    $this->printItem($escpos, $item, 'CANCELADO');
                }
                $escpos->text("\n");
            }

            $escpos->text(str_repeat('-', 32)."\n");
            $escpos->feed(2);
            $escpos->cut();
            $escpos->close();

            return [
                'station' => $station,
                'success' => true,
            ];

        } catch (\Exception $e) {
            Log::error("Delta print error [{$station}]: ".$e->getMessage());

            return [
                'station' => $station,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Método helper para imprimir un item individual
     */
    private function printItem(EscposPrinter $printer, OrderItem $item, ?string $badge = null): void
    {
        $line = sprintf(
            '%dx %s',
            $item->quantity,
            $item->dish->name
        );

        if ($badge) {
            $printer->setEmphasis(true);
            $printer->text("[{$badge}] ");
            $printer->setEmphasis(false);
        }

        $printer->text($line."\n");

        // Notas especiales
        if (! empty($item->special_instructions)) {
            $printer->text('   >> '.$item->special_instructions."\n");
        }

        $printer->text("\n");
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

    /**
     * Simula la impresión en ambiente de desarrollo
     */
    private function simulatePrint(Order $order, string $station, $items, string $type = 'kitchen'): bool
    {
        $itemsCount = is_array($items) ? count($items) : (method_exists($items, 'count') ? $items->count() : 0);

        Log::info("📄 [SIMULATED PRINT] {$type} ticket", [
            'order' => $order->order_number,
            'station' => $station,
            'items_count' => $itemsCount,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);

        // Guardar contenido simulado en storage para debugging
        if (config('printing.save_simulated', false)) {
            $content = $this->generateSimulatedTicketContent($order, $station, $items, $type);
            $filename = "simulated-prints/{$order->order_number}-{$station}-".now()->format('YmdHis').'.txt';

            Storage::disk('local')->put($filename, $content);
            Log::info("📝 Ticket simulado guardado: storage/app/{$filename}");
        }

        return true;
    }

    /**
     * Genera el contenido de un ticket simulado
     */
    private function generateSimulatedTicketContent($order, $station, $items, $type): string
    {
        $content = "=================================\n";
        $content .= '  TICKET SIMULADO - '.strtoupper($station)."\n";
        $content .= "  Tipo: {$type}\n";
        $content .= "=================================\n";
        $content .= "Orden: {$order->order_number}\n";
        $content .= 'Mesa: '.($order->table_number ?? 'N/A')."\n";
        $content .= 'Cliente: '.($order->customer_name ?? 'N/A')."\n";
        $content .= 'Hora: '.now()->format('H:i:s')."\n";
        $content .= "---------------------------------\n\n";

        // Convertir items a array si es necesario
        $itemsArray = is_array($items) ? $items : $items->all();

        // Si es un array estructurado por tipo (delta)
        if (isset($itemsArray['new']) || isset($itemsArray['modified']) || isset($itemsArray['cancelled'])) {
            if (! empty($itemsArray['new'])) {
                $content .= ">>> AGREGAR <<<\n";
                foreach ($itemsArray['new'] as $item) {
                    $content .= $this->formatSimulatedItem($item);
                }
                $content .= "\n";
            }

            if (! empty($itemsArray['modified'])) {
                $content .= ">>> MODIFICAR <<<\n";
                foreach ($itemsArray['modified'] as $item) {
                    $content .= '[MODIFICADO] '.$this->formatSimulatedItem($item);
                }
                $content .= "\n";
            }

            if (! empty($itemsArray['cancelled'])) {
                $content .= ">>> CANCELAR <<<\n";
                foreach ($itemsArray['cancelled'] as $item) {
                    $content .= '[CANCELADO] '.$this->formatSimulatedItem($item);
                }
                $content .= "\n";
            }
        } else {
            // Items normales
            foreach ($itemsArray as $item) {
                $content .= $this->formatSimulatedItem($item);
            }
        }

        $content .= "---------------------------------\n";
        $content .= 'Total: $'.number_format($order->total, 2)."\n";
        $content .= "=================================\n";

        return $content;
    }

    /**
     * Formatea un item para el ticket simulado
     */
    private function formatSimulatedItem($item): string
    {
        $line = "{$item->quantity}x {$item->dish->name}\n";

        if (! empty($item->special_instructions)) {
            $line .= "   >> {$item->special_instructions}\n";
        }

        $line .= "\n";

        return $line;
    }
}
