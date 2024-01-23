<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include assistant.js -->
    <script src="{{ asset('js/assistant.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include Tailwind CSS -->
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar for Threads -->
        <div class="w-1/4 bg-white p-5 shadow">
            <h2 class="text-xl font-semibold mb-4">Threads</h2>
            <!-- Assuming threads are loaded into a $threads variable -->
            <div id="threads" class="overflow-y-auto space-y-2">
                <!-- Threads will be inserted here by JavaScript -->
            </div>

            <!-- Action Buttons -->
            <div class="mt-5 space-y-2">
                <button id="createThreadButton" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none">Create New Thread</button>
                <button id="createAssistantButton" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">Create New Assistant</button>
                <button id="cancelRunButton" onclick="cancelAssistantRun()">Cancel Run</button>
               



                <button id="deleteAssistantButton" class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none">Delete Assistant</button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Messages Display Area -->
            <div id="messages" class="flex-1 overflow-y-auto p-5">
                <!-- Messages will be displayed here -->
            </div>

            <!-- Message Form -->
            <div class="p-5 bg-white border-t">
                <form id="messageForm" class="flex items-center space-x-3">
                    @csrf
                    <input type="text" id="message" name="message" placeholder="Type your message..." class="flex-1 p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">Send</button>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/assistant.js') }}"></script>
</body>
</html>
