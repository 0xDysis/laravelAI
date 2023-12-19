import openai
import sys
import json
import time

# Set the API key directly
openai.api_key = 'x'

def create_assistant():
    assistant = openai.beta.assistants.create(
        name="Retrieval Assistant",
        instructions="You are an assistant that uses retrieval to answer questions.",
        tools=[{"type": "retrieval"}],
        model="gpt-3.5-turbo-1106"
    )
    print(assistant.id)  # Only print the assistant ID

def create_thread():
    thread = openai.beta.threads.create()
    print(thread.id)  # Only print the thread ID

def add_message(thread_id, role, content):
    message = openai.beta.threads.messages.create(
        thread_id=thread_id,
        role=role,
        content=content
    )
    print(message.id)  # Print the message ID to standard output

def get_messages(thread_id):
    response = openai.beta.threads.messages.list(thread_id=thread_id)
    messages = response.data
    messages_data = []
    for message in messages:
        # Convert each message to a dictionary
        message_dict = {
            'id': message.id,
            'role': message.role,
            'content': message.content.text if hasattr(message.content, 'text') else {'value': str(message.content)},
            'created_at': message.created_at,
            # Add any other properties you're interested in
        }
        messages_data.append(message_dict)
    messages_json = json.dumps(messages_data)  # Convert messages to JSON
    print(messages_json)  # Print the messages to standard output

def run_assistant(thread_id, assistant_id):
    run = openai.beta.threads.runs.create(
        thread_id=thread_id,
        assistant_id=assistant_id
    )
    # Wait for the run to finish processing
    while run.status == 'in-progress':
        time.sleep(1)  # Wait for a short amount of time
        run = openai.beta.threads.runs.retrieve(run.id)  # Refresh the run status
    print(run.id)  # Print the run ID to standard output

# Call the function specified by the first command line argument with the remaining arguments
if __name__ == "__main__":
    if len(sys.argv) > 1:
        function_name = sys.argv[1]
        args = sys.argv[2:]
        if function_name in globals():
            globals()[function_name](*args)
        else:
            print(f"No function named {function_name} found.")
    else:
        print("No function specified to call.")