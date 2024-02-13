<?php

namespace LaravelAI\LaravelChatbot\Services;


use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DatabaseExportService
{
    protected $openAIService;

    public function __construct(MyOpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function createNewAssistantWithCsv()
    {
        $csvData = $this->convertOrdersToCsv();
        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFilePath, $csvData);
        $assistantId = $this->openAIService->createAssistant($tempFilePath);

        $this->storeAssistantIdInUser($assistantId);
        unlink($tempFilePath);

        return redirect('/');
    }

    public function createNewAssistantWithMultipleCsv()
    {
        $csvFiles = [
            'Nights.csv', 
            'Age_categories.csv', 
            'Reservations (1).csv', 
            'Parameters.csv'
        ];

        $tempFilePaths = [];
        foreach ($csvFiles as $csvFile) {
            $tempFilePath = tempnam(sys_get_temp_dir(), 'csv');
            $sourceFilePath = public_path($csvFile);
            if (!copy($sourceFilePath, $tempFilePath)) {
                return "Error copying file: " . $sourceFilePath;
            }
            $tempFilePaths[] = $tempFilePath;
        }

        $assistantId = $this->openAIService->createAssistant($tempFilePaths);

        $this->storeAssistantIdInUser($assistantId);

        foreach ($tempFilePaths as $tempFilePath) {
            unlink($tempFilePath);
        }

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

    private function storeAssistantIdInUser($assistantId)
    {
        $user = Auth::user();
        $assistantIds = $user->assistant_ids ?? [];
        $assistantIds[] = $assistantId;

        $user->assistant_ids = $assistantIds;
        $user->save();
    }
}
