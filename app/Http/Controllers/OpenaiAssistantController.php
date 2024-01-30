<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$apiKey = env('OPENAI_API_KEY', 'your-default-api-key');
$client = OpenAI::client($apiKey);

function createAssistant($client, $filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("CSV file not found: " . $filePath);
    }

    $file = fopen($filePath, "rb");
    if (!$file) {
        throw new Exception("Failed to open file: " . $filePath);
    }

    try {
        $file1 = $client->files()->upload([
            'purpose' => 'assistants',
            'file' => $file,
        ]);
    } catch (Exception $e) {
        if (is_resource($file)) {
            fclose($file);
        }
        throw $e;
    }

    if (is_resource($file)) {
        fclose($file);
    }

    $assistant = $client->assistants()->create([
        'name' => "code interpreter",
        'instructions' => "YOU ALWAYS CONVERT REQUESTS FOR GRAPHS PLOTS OR ANYTHING OF THE LIKE TO A DOWNLOADABLE FILE IN THE PNG FORMAT VanOnsAssist is a knowledgeable, friendly, and professional AI assistant for the web development company van-ons, specifically designed to help you find the right information about anything concerning the van-ons operations",
        'tools' => [['type' => 'code_interpreter']],
        'model' => 'gpt-3.5-turbo-1106',
        'file_ids' => [$file1->id]
    ]);

    echo $assistant->id;
}
function createAssistant2($client) {
    
    $basePath = 'public/';

    
    $file1 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen($basePath . "Nights.csv", "rb"),
    ]);

    
    $file2 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen($basePath . "Age_categories.csv", "rb"),
    ]);

    $file3 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen($basePath . "Reservations (1).csv", "rb"),
    ]);

    
    $file4 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen($basePath . "Parameters.csv", "rb"),
    ]);

    
    $assistant = $client->assistants()->create([
        'name' => "Retrieval Assistant",
        'instructions' => "YOU ALWAYS CONVERT REQUESTS FOR GRAPHS PLOTS OR ANYTHING OF THE LIKE TO A DOWNLOADABLE FILE IN THE PNG FORMAT VanOnsAssist is a knowledgeable, friendly, and professional AI assistant for the web development company van-ons, specifically designed to help you find the right information about anything concerning the van-ons operations",
        'tools' => [['type' => 'code_interpreter']],
        'model' => 'gpt-3.5-turbo-1106',
        'file_ids' => [$file1->id, $file2->id, $file3->id, $file4->id]
    ]);

    echo $assistant->id;
}


function createThread($client) {
    $thread = $client->threads()->create([]);
    echo $thread->id;
}

function addMessage($client, $threadId, $role, $content) {
    $message = $client->threads()->messages()->create($threadId, [
        'role' => $role,
        'content' => $content
    ]);
    echo $message->id;
}

function createAndRunThreadWithMessage($client, $assistantId, $userMessage) {
    try {
        $response = $client->threads()->createAndRun(
            [
                'assistant_id' => $assistantId,
                'thread' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $userMessage,
                        ],
                    ],
                ],
            ]
        );

        // Returning the response properties
        return [
            'id' => $response->id,
            'object' => $response->object,
            // ... other properties as needed ...
        ];

    } catch (Exception $e) {
        echo 'Error creating and running thread: ' . $e->getMessage();
    }
}

function getMessages($client, $threadId) {
    $response = $client->threads()->messages()->list($threadId);
    $messages = $response->data;
    $messagesData = [];
    foreach ($messages as $message) {
        $content = $message->content;
        if (is_array($content)) {
            $content = json_encode($content);
        }
        $contentJson = json_decode($content, true);
        $messageText = $contentJson[0]['text']['value'];
        $messageDict = [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $messageText,
            'threadId' => $threadId,  // Add the threadId to the message
        ];
        array_push($messagesData, $messageDict);
    }
    echo json_encode($messagesData); 
}

function runAssistant($client, $threadId, $assistantId) {
    $run = $client->threads()->runs()->create($threadId, [
        'assistant_id' => $assistantId
    ]);

    echo $run->id;
}


function cancelRun($client, $threadId, $runId) {
    try {
        $response = $client->threads()->runs()->cancel(
            threadId: $threadId,
            runId: $runId,
        );

        // Output the details of the cancelled run
        echo json_encode($response->toArray());
    } catch (Exception $e) {
        // Handle any exceptions that may occur
        echo 'Error canceling run: ' . $e->getMessage();
    }
}

function checkRunStatus($client, $threadId, $runId) {
    $run = $client->threads()->runs()->retrieve($threadId, $runId);
    echo json_encode(['status' => $run->status, 'id' => $run->id]);
}

function deleteThread($client, $threadId) {
    $response = $client->threads()->delete($threadId);
    echo $response->id;
}

function deleteAssistant($client, $assistantId) {
    $response = $client->assistants()->delete($assistantId);
    echo $response->id;
}

function retrieveMessageFile($client, $fileId) {
    $fileContent = $client->files()->download($fileId);
    header('Content-Type: application/octet-stream');
    echo $fileContent;
}

function listMessageFiles($client, $threadId, $messageId) {
    $response = $client->threads()->messages()->files()->list(
        threadId: $threadId,
        messageId: $messageId,
        parameters: [
            'limit' => 10,
        ],
    );

    $fileIds = [];
    foreach ($response->data as $file) {
        array_push($fileIds, $file->id);
    }

    echo json_encode($fileIds);
}

if ($argc > 1) {
    $functionName = $argv[1];
    $args = array_slice($argv, 2);
    if (function_exists($functionName)) {
        call_user_func_array($functionName, array_merge([$client], $args));
    } else {
        echo "No function named {$functionName} found.";
    }
} else {
    echo "No function specified to call.";
}

