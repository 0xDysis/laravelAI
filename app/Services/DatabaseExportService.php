<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Session;
use App\Services\PHPScriptRunnerService;

class ExportService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function createNewAssistantWithCsv()
    {
        $csvData = $this->convertOrdersToCsv();
        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFilePath, $csvData);
        $assistantId = $this->phpScriptRunnerService->runScript('createAssistant', [$tempFilePath]);

        Session::put('assistantId', $assistantId);
        unlink($tempFilePath);

        return redirect('/');
    }

    private function convertOrdersToCsv()
    {
        $orders = Order::all();
        $csvData = "order_id,customer_name,order_total\n";
        foreach ($orders as $order) {
            $csvData .= "{$order->order_id},{$order->customer_name},{$order->order_total}\n";
        }

        return $csvData;
    }
}
