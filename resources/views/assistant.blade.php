<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/assistant.js') }}" type="module"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 font-sans leading-normal tracking-normal"> <!-- Set body to a dark background -->
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar for Threads -->
        <div class="md:w-1/6 lg:w-1/8 bg-black p-5 shadow overflow-y-auto"> <!-- Set sidebar to black -->
            <!-- Action Buttons -->
            <div class="mb-5 space-y-2">
                <button id="createThreadButton" class="w-full px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 focus:outline-none">Start new conversation</button>
                <button id="createAssistantButton" class="w-full px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 focus:outline-none">synchronize database</button>
                <button id="cancelRunButton" class="w-full px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 focus:outline-none">Cancel Request</button>
            </div>
            <h2 class="text-xl font-semibold text-white mb-4">Conversations</h2>
            <div id="threads" class="space-y-2">
                <!-- Threads will be inserted here by JavaScript -->
            </div>
        </div>

           <!-- Main Content Area -->
           <div class="flex-1 flex flex-col bg-gray-800"> <!-- Set main content area to a dark gray -->
            <div id="messages" class="flex-1 overflow-y-auto p-5 bg-gray-800 text-gray-200"> <!-- Set messages area to the same dark gray and text to off-white -->
                <!-- Messages will be displayed here -->
            </div>
            
            

         <!-- Message Form -->
<!-- Message Form -->
<div class="p-5 bg-gray-800 border-t border-gray-800"> <!-- Set message form to a slightly lighter gray -->
    <form id="messageForm" class="flex items-center space-x-3">
        @csrf
        <input type="text" id="message" name="message" placeholder="Type your message..." class="flex-1 p-3 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:ring focus:ring-gray-500 focus:border-transparent">
        <button type="submit" class="p-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
        </button>
    </form>
</div>



        </div>
    </div>
</body>
</html>

