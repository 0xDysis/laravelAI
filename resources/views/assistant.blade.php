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
        <div class="md:w-1/6 md:w-1/8 bg-white p-5 shadow overflow-y-auto border-r border-gray-200 flex flex-col justify-between">
            <div class="flex-grow">
                <div class="mb-10 flex-shrink-0 flex justify-start">
                    <img src="{{ asset('build/assets/zwart-transparant1580994585logo 1.png') }}" alt="Company Logo" class="block my-0 p-0 w-14">
                </div>
                <div class="mt-4">
                    <!-- Action Buttons -->
                    <div class="mb-9 space-y-2">
                    <button id="createThreadButton" class="w-full px-2 py-2 bg-white text-black rounded hover:bg-light-blue hover:text-dark-blue focus:outline-none flex items-center justify-start transition duration-300 ease-in-out overflow-hidden whitespace-nowrap text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                          </svg>
                          
                        Nieuw gesprek
                    </button>
                    <button id="createAssistantButton" class="w-full px-2 py-2 bg-white text-black rounded hover:bg-light-blue hover:text-dark-blue focus:outline-none flex items-center justify-start transition duration-300 ease-in-out overflow-hidden whitespace-nowrap text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                          </svg>
                          
                        Synchroniseer database
                    </button>
                </div>
                <h2 class="text-sm text-gray-400 mb-4">GESPREKKEN</h2>
                <div id="threads" class="space-y-2">
                    <!-- Threads will be inserted here by JavaScript -->
                </div>
            </div>
        </div>
        <!-- Sticky div at the bottom -->
        <div class="flex-shrink-0 w-full text-gray-400 bg-white p-2 flex items-center justify-center text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="black" class="w-4 h-4 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-2.25-1.313M21 7.5v2.25m0-2.25-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3 2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75 2.25-1.313M12 21.75V19.5m0 2.25-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
              </svg>
              
                
                
            Powered by Van Ons AI
        </div>
    </div>
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col bg-gray-100">
        <div id="messages" class="flex-1 overflow-y-auto p-5 bg-gray-100 text-gray-800">
            <!-- Assistant Message -->
            <div class="mb-4 flex items-end justify-start">
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg max-w-xs lg:max-w-md">
                    <p>Hello! How can I assist you today?</p>
                </div>
            </div>
            <!-- User Message -->
            <div class="mb-4 flex items-end justify-end">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg max-w-xs lg:max-w-md">
                    <p>Hey, I need help with my account.</p>
                </div>
            </div>
            <!-- More messages... -->
        </div>
        
        
        <!-- Message Form -->
        <div class="p-5 bg-gray-100 border-t border-gray-100 flex-shrink-0">
            <form id="messageForm" class="flex items-center space-x-3">
                @csrf
                <input type="text" id="message" name="message" placeholder="Type your message..." class="flex-1 p-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring focus:ring-gray-300 focus:border-transparent">
                <button type="submit" class="p-2 bg-blue-700 text-white rounded hover:bg-blue-800 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                      </svg>
                      
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>