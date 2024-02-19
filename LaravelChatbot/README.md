# Laravel Chatbot Package

This package provides a comprehensive integration for creating and managing chatbots using the Laravel framework. The OpenAIController included within the package handles various aspects of chatbot functionality, including message processing, runs management, file downloads, database exports, thread operations, and assistant management.

## Features

- Message submission and retrieval
- Run initiation and cancellation with status checks
- File downloads related to chatbot messages
- Exporting data for assistant creation
- Thread and assistant lifecycle management

## Installation

To install the Laravel Chatbot package, run the following command in your project:

```bash
composer require laravelai/laravelchatbot
Publish the package's assets, migrations, and configuration files using:

bash
Copy code
php artisan vendor:publish --provider="LaravelAI\LaravelChatbot\LaravelChatbotServiceProvider"
Run the migrations to set up the database tables:

bash
Copy code
php artisan migrate
Usage
Message Handling
Submit a message to an assistant's thread and retrieve messages from it:

php
Copy code
$messageService->submitMessage($threadId, $assistantId, $userMessage);
$messageService->getMessages($threadId);
Run Management
Start, cancel, and check the status of a run:

php
Copy code
$runService->startRun($threadId, $assistantId);
$runService->cancelRun($threadId, $runId);
$runService->checkRunStatus($threadId, $runId);
File Downloads
Download a file associated with a message:

php
Copy code
$fileDownloadService->downloadMessageFile($fileId);
Database Exports
Create a new assistant with multiple CSV data:

php
Copy code
$databaseExportService->createNewAssistantWithMultipleCsv();
Thread and Assistant Management
Create and delete threads, manage assistant data:

php
Copy code
$threadService->createNewThread();
$threadService->deleteThread($threadId);
// ... and other assistant management functions
Contributing
Contributions are welcome. Please open an issue or submit a pull request with your changes.

License
This Laravel Chatbot package is open-sourced software licensed under the MIT license.

