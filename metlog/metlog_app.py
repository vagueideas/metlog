from flask import Flask, render_template, request
from helpers import login_required

# Configure application
app = Flask(__name__)

# Home page
@app.route("/")
@login_required
def index():
    # Show current weather conditions

    return render_template("index.html")

    
@app.route("/login")
def login():

    return render_template("login.html")

@app.route("/register", methods=["GET", "POST"])
def register():
    """ register user """

    if request.method == "POST":
        return render_template("register.html")

    else:
        return render_template("register.html")
        


