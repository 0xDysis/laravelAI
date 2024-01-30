<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth; // To get the current user
use App\Services\PHPScriptRunnerService;

class DatabaseExportService
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
        // Use the public_path helper to reference files in the public directory
        $sourceFilePath = public_path($csvFile);
        if (!copy($sourceFilePath, $tempFilePath)) {
            // Handle the error appropriately
            return "Error copying file: " . $sourceFilePath;
        }
        $tempFilePaths[] = $tempFilePath;
    }

    // Run the script with the array of temporary file paths
    $assistantId = $this->phpScriptRunnerService->runScript('createAssistant2', $tempFilePaths);

    // Store the new assistant ID in the user's record
    $this->storeAssistantIdInUser($assistantId);

    // Clean up: delete the temporary files
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
        $user = Auth::user(); // Get the authenticated user

        // Check if the user already has assistant IDs and add the new one
        $assistantIds = $user->assistant_ids ?? [];
        $assistantIds[] = $assistantId;

        $user->assistant_ids = $assistantIds;
        $user->save();
    }
}


