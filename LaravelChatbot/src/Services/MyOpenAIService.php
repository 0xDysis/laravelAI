<?php

namespace LaravelAI\LaravelChatbot\Services;


use OpenAI;


class MyOpenAIService
{
    private $client;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');
        $this->client = OpenAI::client($apiKey);
    }

  
    public function createAssistant()
{
    $file1 = $this->client->files()->upload([
        'purpose' => 'assistants',
        'file' => fopen(public_path("Nights.csv"), "rb"),
    ]);

   

        $assistant = $this->client->assistants()->create([
            'name' => "Retrieval Assistant",
            'instructions' => "you are an expert data analyst for the company hotel casa. they have given you 4 different csv files containing data about their company which you use to extrapolate the data they ask of you. you will only speak in DUTCH.",
            'tools' => [['type' => 'retrieval']],
            'model' => 'gpt-4-turbo-preview',
            'file_id' => [$file1->id]
        ]);

        return $assistant->id;
    }

    public function createThread()
    {
        $thread = $this->client->threads()->create([]);
        return $thread->id;
    }

    public function addMessage($threadId, $role, $content)
    {
        $message = $this->client->threads()->messages()->create($threadId, [
            'role' => $role,
            'content' => $content
        ]);
        return $message->id;
    }

    public function getMessages($threadId)
    {
        $response = $this->client->threads()->messages()->list($threadId);
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
                'threadId' => $threadId,
            ];
            array_push($messagesData, $messageDict);
        }
        return json_encode($messagesData); 
    }

    public function runAssistant($threadId, $assistantId)
    {
        $run = $this->client->threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId
        ]);

        return $run->id;
    }

    public function cancelRun($threadId, $runId)
    {
        try {
            $response = $this->client->threads()->runs()->cancel(
                threadId: $threadId,
                runId: $runId,
            );

            // Output the details of the cancelled run
            echo json_encode($response->toArray());
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return 'Error canceling run: ' . $e->getMessage();
        }
    }

    public function checkRunStatus($threadId, $runId)
    {
        $run = $this->client->threads()->runs()->retrieve($threadId, $runId);
        return json_encode(['status' => $run->status, 'id' => $run->id]);
    }

    public function deleteThread($threadId)
    {
        $response = $this->client->threads()->delete($threadId);
        return $response->id;
    }

    public function deleteAssistant($assistantId)
    {
        $response = $this->client->assistants()->delete($assistantId);
        return $response->id;
    }

    public function retrieveMessageFile($fileId)
    {
        $fileContent = $this->client->files()->download($fileId);
        header('Content-Type: application/octet-stream');
        return $fileContent;
    }

    public function listMessageFiles($threadId, $messageId)
    {
        $response = $this->client->threads()->messages()->files()->list(
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

        return json_encode($fileIds);
    }
}

