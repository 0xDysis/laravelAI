import openai
import sys
import json
import time

# Set the OpenAI API key
openai.api_key = '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'

def create_assistant():
    # Upload the files and create an assistant one assistant can have multiple threads from different users
    file1 = openai.files.create(
      file=open("/Users/dysisx/Documents/assistant/van-onsdataset copy.txt", "rb"),
      purpose='assistants'
    )

    file2 = openai.files.create(
      file=open("/Users/dysisx/Documents/assistant/van-ons2 copy.txt", "rb"),
      purpose='assistants'
    )

    assistant = openai.beta.assistants.create(
        name="Retrieval Assistant",
        instructions="You are an assistant that uses retrieval to answer questions.",
        tools=[{"type": "retrieval"}],
        model="gpt-3.5-turbo-1106",
        file_ids=[file1.id, file2.id]
    )
    print(assistant.id)

def create_thread():
    # Create a new thread
    thread = openai.beta.threads.create()
    print(thread.id)

def add_message(thread_id, role, content):
    # Add a message to a thread
    message = openai.beta.threads.messages.create(
        thread_id=thread_id,
        role=role,
        content=content
    )
    print(message.id)

def get_messages(thread_id):
    # Retrieve the messages from a thread
    response = openai.beta.threads.messages.list(thread_id=thread_id)
    messages = response.data
    messages_data = []
    for message in messages:
        message_dict = {
            'id': message.id,
            'role': message.role,
            'content': message.content.text if hasattr(message.content, 'text') else {'value': str(message.content)},
            'created_at': message.created_at,
        }
        messages_data.append(message_dict)
    messages_json = json.dumps(messages_data)
    print(messages_json)

def run_assistant(thread_id, assistant_id):
    # Run the assistant in a thread and wait for it to finish processing
    run = openai.beta.threads.runs.create(
        thread_id=thread_id,
        assistant_id=assistant_id
    )
    while run.status == 'in-progress':
        time.sleep(1)
        run = openai.beta.threads.runs.retrieve(run.id)
    print(run.id)

# This block is the entry point of the Python script when it is executed as the main program.
# It checks if the script was called with additional command line arguments.
# The first argument is expected to be the name of the function to call,
# and the remaining arguments are passed to that function.
if __name__ == "__main__":
    # Check if any command line arguments were provided
    if len(sys.argv) > 1:
        # The first argument after the script name is the function name
        function_name = sys.argv[1]
        # The rest of the arguments are collected as a list
        args = sys.argv[2:]
        # Check if the function name provided exists in the global scope of this script
        if function_name in globals():
            # If the function exists, call it with the provided arguments
            globals()[function_name](*args)
        else:
            # If the function does not exist, print an error message
            print(f"No function named {function_name} found.")
    else:
        # If no function is specified, print an error message
        print("No function specified to call.")

