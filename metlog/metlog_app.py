import mysql.connector
from flask import Flask, redirect, render_template, request, session
from flask_session import Session
from helpers import login_required, error_msg, info_msg
from werkzeug.security import check_password_hash, generate_password_hash


# Configure application
app = Flask(__name__)

# Configure  mysql database
mydb = mysql.connector.connect(
  host="localhost",
  user="metlog",
  password="metlog",
  database="metlog"
)

# Get db ready
db = mydb.cursor()

# Home page
@app.route("/")
@login_required
def index():
    # Show current weather conditions

    # Get user id and set for session
    sql = "SELECT user FROM users WHERE id = %s"
    value = (session["user_id"], )
    db.execute(sql, value)
    rows = db.fetchone()

    user = rows[0]

    return render_template("index.html", user=user)

    
@app.route("/login", methods=["GET", "POST"])
def login():
    """ log user in """

    # Clear session id
    session.clear()

    # If user entered login form
    if request.method == "POST":
        
        # Get form data
        user = request.form.get("username")
        password = request.form.get("password")

        # Ensure username was submitted
        if not user:
            return error_msg("must provide username", 403)

        # Ensure password was submitted
        if not password:
            return error_msg("must provide password", 403)

        # Get user details from db and set session
        sql = "SELECT id, hash FROM users WHERE user = %s"
        value = (user, )
        db.execute(sql, value)
        rows = db.fetchall()

        # Ensure username exists and password is correct
        if len(rows) != 1 or not check_password_hash(rows[0][1], password):
            return error_msg("invalid username and/or password", 403)
        
        # If password matches user, set session
        session["user_id"] = rows[0][0]

        return redirect("/")


    return render_template("login.html")

@app.route("/register", methods=["GET", "POST"])
def register():
    """ register user """

    if request.method == "POST":
    
        # Get the user form data
        user = request.form.get("username");
        password = request.form.get("password");
        confirm = request.form.get("confirm");
        first = request.form.get("first");
        last = request.form.get("last");
        email = request.form.get("email");

        # Form validation
        if not user:
            return error_msg("username required", 400)

        if not password:
            return error_msg("password required", 400)

        if not confirm:
            return error_msg("password confirmation required", 400)

        if password != confirm:
            return error_msg("password and confirmation must match", 400)


        # Check if user exists in the db
        sql = "SELECT * FROM users WHERE user = %s"
        value = (user, )
        db.execute(sql, value)
        rows = db.fetchall()

        if len(rows) == 0:
            # All is bon, so create user in the db

            # Generate password hash
            hash = generate_password_hash(password)

            # Insert all values into db
            sql = "INSERT INTO users (user, hash, first, last, email) VALUES (%s, %s, %s, %s, %s)"
            values = (user, hash, first, last, email)
            db.execute(sql, values)
            mydb.commit()

            # Get user id and set for session
            sql = "SELECT id FROM users WHERE user = %s"
            value = (user, )
            db.execute(sql, value)
            rows = db.fetchone()
           
            session["user_id"] = rows[0]


            return info_msg(rows)

        else:
            # User already exists error message
            return error_msg("user exists")

    else:
        return render_template("register.html")

@app.route("/logout")
def logout():
    """ log user out """

    # Clear the user session
    session.clear()

    # Redirect to home screen
    return redirect("/")
