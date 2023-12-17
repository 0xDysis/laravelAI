# app/PythonScripts/openai_assistant.py

import openai
import sys

openai.api_key = 'your-openai-api-key'

def create_assistant():
    assistant = openai.Assistant.create(
        name="Retrieval Assistant",
        instructions="You are an assistant that uses retrieval to answer questions.",
        tools=[{"type": "retrieval"}],
        model="gpt-3.5-turbo"
    )
    return assistant.id

def create_thread():
    thread = openai.Thread.create()
    return thread.id

def add_message(thread_id, role, content):
    message = openai.Message.create(
        thread_id=thread_id,
        role=role,
        content=content
    )
    return message.id

def run_assistant(thread_id, assistant_id):
    run = openai.Run.create(
        thread_id=thread_id,
        assistant_id=assistant_id
    )
    return run.id

def get_messages(thread_id):
    messages = openai.Message.list(thread_id=thread_id)
    return messages

# Call the function specified by the first command line argument with the remaining arguments
globals()[sys.argv[1]](*sys.argv[2:])