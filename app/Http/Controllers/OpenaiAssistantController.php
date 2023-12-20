<?php

require_once __DIR__ . '/../../../vendor/autoload.php';





$apiKey = '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';
$client = OpenAI::client($apiKey);

function createAssistant($client) {
   
    $file1 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen("/Users/dysisx/Documents/assistant/van-onsdataset copy.txt", "rb"),
    ]);

    $file2 = $client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen("/Users/dysisx/Documents/assistant/van-ons2 copy.txt", "rb"),
    ]);

    
    $assistant = $client->assistants()->create([
        'name' => "Retrieval Assistant",
        'instructions' => "You are an assistant that uses retrieval to answer questions.",
        'tools' => [['type' => 'retrieval']],
        'model' => 'gpt-3.5-turbo-1106',
        'file_ids' => [$file1->id, $file2->id]
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


function getMessages($client, $threadId) {
    $response = $client->threads()->messages()->list($threadId);
    $messages = $response->data;
    $messagesData = [];
    foreach ($messages as $message) {
        $content = $message->content;
        if (is_array($content)) {
            $content = json_encode($content);
        }
        $messageDict = [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $content,
        ];
        array_push($messagesData, $messageDict);
    }
    echo json_encode($messagesData); // Echo the JSON string directly
}




function runAssistant($client, $threadId, $assistantId) {
    $run = $client->threads()->runs()->create($threadId, [
        'assistant_id' => $assistantId
    ]);
    while ($run->status == 'in-progress') {
        sleep(1);
        $run = $client->threads()->runs()->retrieve($threadId, $run->id);
    }
    echo $run->id;
}

// Entry point of the PHP script
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

?>
