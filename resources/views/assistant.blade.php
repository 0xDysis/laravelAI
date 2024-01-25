<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/assistant.js') }}" type="module"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 font-sans leading-normal tracking-normal">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar for Threads -->
        <div class="md:w-1/6 lg:w-1/8 bg-white p-5 shadow overflow-y-auto">
            <!-- Action Buttons -->
            <div class="mb-5 space-y-2">
                <button id="createThreadButton" class="w-full px-2 py-2 bg-white text-black rounded hover:bg-light-blue hover:text-dark-blue focus:outline-none flex items-center justify-start transition duration-300 ease-in-out overflow-hidden whitespace-nowrap text-sm">
                    <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <!-- Start new conversation icon SVG placeholder -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16M4 12h16M4 20h16"/>
                    </svg>
                    Nieuw gesprek
                </button>
                <button id="createAssistantButton" class="w-full px-2 py-2 bg-white text-black rounded hover:bg-light-blue hover:text-dark-blue focus:outline-none flex items-center justify-start transition duration-300 ease-in-out overflow-hidden whitespace-nowrap text-sm">
                    <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <!-- Synchronize database icon SVG -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M15 15l5 5M4 4l5 5M9 15l-5 5M19 9l-5-5"/>
                    </svg>
                    Synchroniseer database
                </button>
            </div>
        
            
            
            
            
            <h2 class="text-xl font-semibold text-black mb-4">Conversations</h2> <!-- Text color changed to black for visibility -->
            <div id="threads" class="space-y-2">
                <!-- Threads will be inserted here by JavaScript -->
            </div>
        </div>
    

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col bg-gray-100"> <!-- Main content area set to off-white (e.g., bg-gray-100) -->
            <div id="messages" class="flex-1 overflow-y-auto p-5 bg-gray-100 text-gray-800"> <!-- Messages area set to the same off-white with darker text -->
                <!-- Messages will be displayed here -->
            </div>

            <!-- Message Form -->
            <div class="p-5 bg-gray-100 border-t border-gray-100"> <!-- Message form set to off-white -->
                <form id="messageForm" class="flex items-center space-x-3">
                    @csrf
                    <input type="text" id="message" name="message" placeholder="Type your message..." class="flex-1 p-3 bg-white-100 border border-gray-200 rounded-xl focus:outline-none focus:ring focus:ring-gray-300 focus:border-transparent">
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
