from flask import Flask

# Configure application
app = Flask(__name__)

# Home page
@app.route("/")

# Hello world function for testing
def hello():
    print("Hello")
    return "Hello, world!"

# Run the application
if __name__ == "__main__":
    app.run()
