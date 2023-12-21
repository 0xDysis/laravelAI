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
        'instructions' => "VanOnsAssist is a knowledgeable, friendly, and professional AI assistant for the web development company van-ons, specifically designed to help you find the right information about anything concerning the van-ons operations",
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
        $contentJson = json_decode($content, true);
        $messageText = $contentJson[0]['text']['value'];
        $messageDict = [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $messageText,
        ];
        array_push($messagesData, $messageDict);
    }
    echo json_encode($messagesData); 
}





function runAssistant($client, $threadId, $assistantId) {
    $run = $client->threads()->runs()->create($threadId, [
        'assistant_id' => $assistantId
    ]);

    
    do {
        sleep(1);  
        $run = $client->threads()->runs()->retrieve($threadId, $run->id);
    } while ($run->status == 'in_progress');

    echo $run->id;
    return true;
}
function deleteThread($client, $threadId) {
    $response = $client->threads()->delete($threadId);
    echo $response->id;
}

function deleteAssistant($client, $assistantId) {
    $response = $client->assistants()->delete($assistantId);
    echo $response->id;
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
