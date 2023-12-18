import openai
import sys

# Set the API key directly
openai.api_key = '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'



def create_assistant():
    assistant = openai.beta.assistants.create(
        name="Retrieval Assistant",
        instructions="You are an assistant that uses retrieval to answer questions.",
        tools=[{"type": "retrieval"}],
        model="gpt-3.5-turbo-1106"
    )
    return assistant.id

def create_thread():
    thread = openai.beta.threads.create()
    return thread.id

def add_message(thread_id, role, content):
    message = openai.beta.threads.messages.create(
        thread_id=thread_id,
        role=role,
        content=content
    )
    return message.id

def run_assistant(thread_id, assistant_id):
    run = openai.beta.threads.runs.create(
        thread_id=thread_id,
        assistant_id=assistant_id
    )
    return run.id

def get_messages(thread_id):
    messages = openai.beta.threads.runs.retrieve(thread_id=thread_id)
    return messages

# Call the function specified by the first command line argument with the remaining arguments
if __name__ == "__main__":
    globals()[sys.argv[1]](*sys.argv[2:])