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

# make sql queries and return single weather data
def wxquery(query, pwsid):
    sql = query
    value = (pwsid, )
    db.execute(sql, value)
    rows = db.fetchone()
    
    if not rows:
        return False
    
    return rows[0]

# get the username
def user():
    # Get user id...
    userid = session["user_id"]

    # Get user id and set for session
    sql = "SELECT user FROM users WHERE id = %s"
    value = (userid, )
    db.execute(sql, value)
    rows = db.fetchone()

    return rows[0]

# Home page
@app.route("/", methods=["GET", "POST"])
@login_required
def index():
    # Show current weather conditions

    curpws = "none"

    if request.method == "POST":
        curpws = request.form.get("pwsid")
        session["curpws"] = curpws

    # Get user id...
    userid = session["user_id"]

    # Get user id and set for session
    sql = "SELECT user FROM users WHERE id = %s"
    value = (userid, )
    db.execute(sql, value)
    rows = db.fetchone()

    user = rows[0]

    # Get all the user's weather stations
    sql = "SELECT id, pwsid, name, description FROM pws WHERE userid = %s"
    value = (userid, )
    db.execute(sql, value)
    rows = db.fetchall()

    # If there are no weather stations
    if not rows:
        # A value of 0 will prompt the user to setup a new pws
        rows = 0
    else:
        # Create a list of pws info
        pws = []

        for i in range(len(rows)):
            id = rows[i][0]
            pwsid = rows[i][1]
            description = rows[i][2]

            # place into dict
            pwsinfo = {'id': id, 'pwsid': pwsid, 'description': description}
            pws.append(pwsinfo)

    return render_template("index.html", user=user, rows=rows, pws=pws, curpws=curpws)

@app.route("/current", methods=["GET", "POST"])
@login_required
def current():
    """ show the current weather conditions """

    if request.method == "GET":
        pwsid = session["curpws"]
        # allow user to select pws
        # return info_msg("no weather station selected")

    else:
        # Get weather station id
        pwsid = request.form.get("pwsid")

    # Get the weather from this pws
    sql = "SELECT pwsid, pwskey, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms,"\
          "windgustms, winddir, rainmm, dailyrainmm FROM snapshot WHERE pwsid = %s ORDER BY timestamp DESC LIMIT 1";
    value = (pwsid, )
    db.execute(sql, value)
    rows = db.fetchall()

    if not rows:
        return error_msg("no weather data found: is weather station online?")

    # put this into a dict:
    currentwx = {'pwsid': rows[0][0], 'pwskey': rows[0][1], 'timestamp': rows[0][2], 'barohpa': rows[0][3],\
             'tempc': rows[0][4], 'intempc': rows[0][5], 'dewptc': rows[0][6], 'humidity': rows[0][7],\
             'inhumidity': rows[0][8], 'windspeedms': rows[0][9], 'windgustms': rows[0][10],\
              'winddir': rows[0][11], 'rainmm': rows[0][12], 'dailyrainmm': rows[0][13]}
    return render_template("current.html", pwsid=pwsid, currentwx=currentwx, user=user())

@app.route("/temperature", methods=["GET", "POST"])
@login_required
def temperature():
    """ show the temperature info """

    if request.method == "GET":
        pwsid = session["curpws"]
        # allow user to select pws
        #return info_msg("no weather station selected")

    else:
        # Get weather station id
        pwsid = request.form.get("pwsid")

    # Get the weather from this pws
    sql = "SELECT pwsid, pwskey, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms,"\
          "windgustms, winddir, rainmm, dailyrainmm FROM snapshot WHERE pwsid = %s ORDER BY timestamp DESC LIMIT 1";
    value = (pwsid, )
    db.execute(sql, value)
    rows = db.fetchall()

    if not rows:
        return error_msg("weather data not found: is weather station properly configured and online?")

    # put this into a dict:
    currentwx = {'pwsid': rows[0][0], 'pwskey': rows[0][1], 'timestamp': rows[0][2], 'barohpa': rows[0][3],\
             'tempc': rows[0][4], 'intempc': rows[0][5], 'dewptc': rows[0][6], 'humidity': rows[0][7],\
             'inhumidity': rows[0][8], 'windspeedms': rows[0][9], 'windgustms': rows[0][10],\
              'winddir': rows[0][11], 'rainmm': rows[0][12], 'dailyrainmm': rows[0][13]}

    # Query daily, weekly and yearly records and put into dict
    wxrecords = {
    'todaytemphi': wxquery("SELECT tempchi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND tempchi IS NOT NULL ORDER BY tempchi DESC Limit 1", pwsid),
    'todaytemplo': wxquery("SELECT tempclo FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND tempclo IS NOT NULL ORDER BY tempclo ASC Limit 1", pwsid),
    'weektemphi': wxquery("SELECT tempchi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND tempchi IS NOT NULL ORDER BY tempchi DESC Limit 1", pwsid),
    'weektemplo': wxquery("SELECT tempclo FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND tempclo IS NOT NULL ORDER BY tempclo ASC Limit 1", pwsid),
    'yeartemphi': wxquery("SELECT tempchi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND tempchi IS NOT NULL ORDER BY tempchi DESC Limit 1", pwsid),
    'yeartemplo': wxquery("SELECT tempclo FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND tempclo IS NOT NULL ORDER BY tempclo ASC Limit 1", pwsid),
    'todayintemphi': wxquery("SELECT intempchi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND intempchi IS NOT NULL ORDER BY intempchi DESC Limit 1", pwsid),
    'todayintemplo': wxquery("SELECT intempclo FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND intempclo IS NOT NULL ORDER BY intempclo ASC Limit 1", pwsid),
    'weekintemphi': wxquery("SELECT intempchi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND intempchi IS NOT NULL ORDER BY intempchi DESC Limit 1", pwsid),
    'weekintemplo': wxquery("SELECT intempclo FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND intempclo IS NOT NULL ORDER BY intempclo ASC Limit 1", pwsid),
    'yearintemphi': wxquery("SELECT intempchi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND intempchi IS NOT NULL ORDER BY intempchi DESC Limit 1", pwsid),
    'yearintemplo': wxquery("SELECT intempclo FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND intempclo IS NOT NULL ORDER BY intempclo ASC Limit 1", pwsid)
    }

    if wxrecords.get("todaytemphi") == False:
        return info_msg("today's weather data not found, is weather station online?")

    # Send data to web page
    return render_template("temperature.html", pwsid=pwsid, currentwx=currentwx, wxrecords=wxrecords, user=user())


@app.route("/wind", methods=["GET", "POST"])
@login_required
def wind():
    """ show the wind info """

    if request.method == "GET":
        pwsid = session["curpws"]
        # allow user to select pws
        # return info_msg("no weather station selected")

    else:
        # Get weather station id
        pwsid = request.form.get("pwsid")

    # Get the weather from this pws
    sql = "SELECT pwsid, pwskey, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms,"\
          "windgustms, winddir, rainmm, dailyrainmm FROM snapshot WHERE pwsid = %s ORDER BY timestamp DESC LIMIT 1";
    value = (pwsid, )
    db.execute(sql, value)
    rows = db.fetchall()

    if not rows:
        return error_msg("today's weather data found: is weather station online?")

    # put this into a dict:
    currentwx = {'pwsid': rows[0][0], 'pwskey': rows[0][1], 'timestamp': rows[0][2], 'barohpa': rows[0][3],\
             'tempc': rows[0][4], 'intempc': rows[0][5], 'dewptc': rows[0][6], 'humidity': rows[0][7],\
             'inhumidity': rows[0][8], 'windspeedms': rows[0][9], 'windgustms': rows[0][10],\
              'winddir': rows[0][11], 'rainmm': rows[0][12], 'dailyrainmm': rows[0][13]}


    # Query daily, weekly and yearly records and put into dict
    wxrecords = {
    'todaywindspeedmshi': wxquery("SELECT windspeedmshi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND windspeedmshi IS NOT NULL ORDER BY windspeedmshi DESC Limit 1", pwsid),
    'weekwindspeedmshi': wxquery("SELECT windspeedmshi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND windspeedmshi IS NOT NULL ORDER BY windspeedmshi DESC Limit 1", pwsid),
    'yearwindspeedmshi': wxquery("SELECT windspeedmshi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND windspeedmshi IS NOT NULL ORDER BY windspeedmshi DESC Limit 1", pwsid),
    'todaywindgustmshi': wxquery("SELECT windgustmshi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND windgustmshi IS NOT NULL ORDER BY windgustmshi DESC Limit 1", pwsid),
    'weekwindgustmshi': wxquery("SELECT windgustmshi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND windgustmshi IS NOT NULL ORDER BY windgustmshi DESC Limit 1", pwsid),
    'yearwindgustmshi': wxquery("SELECT windgustmshi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND windgustmshi IS NOT NULL ORDER BY windgustmshi DESC Limit 1", pwsid),
    }

    # Send data to web page
    return render_template("wind.html", pwsid=pwsid, currentwx=currentwx, wxrecords=wxrecords, user=user())
        
@app.route("/rain", methods=["GET", "POST"])
@login_required
def rain():
    """ show the rain info """

    if request.method == "GET":
        pwsid = session["curpws"]
        # allow user to select pws
        #return info_msg("no weather station selected")

    else:
        # Get weather station id
        pwsid = request.form.get("pwsid")

    # Get the weather from this pws
    sql = "SELECT pwsid, pwskey, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms,"\
          "windgustms, winddir, rainmm, dailyrainmm FROM snapshot WHERE pwsid = %s ORDER BY timestamp DESC LIMIT 1";
    value = (pwsid, )
    db.execute(sql, value)
    rows = db.fetchall()

    if not rows:
        return error_msg("today's weather data found: is weather station online?")

    # put this into a dict:
    currentwx = {'pwsid': rows[0][0], 'pwskey': rows[0][1], 'timestamp': rows[0][2], 'barohpa': rows[0][3],\
             'tempc': rows[0][4], 'intempc': rows[0][5], 'dewptc': rows[0][6], 'humidity': rows[0][7],\
             'inhumidity': rows[0][8], 'windspeedms': rows[0][9], 'windgustms': rows[0][10],\
              'winddir': rows[0][11], 'rainmm': rows[0][12], 'dailyrainmm': rows[0][13]}


    # Query daily, weekly and yearly records and put into dict
    wxrecords = {
    'todayrainmmhi': wxquery("SELECT rainmmhi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND rainmmhi IS NOT NULL ORDER BY rainmmhi DESC Limit 1", pwsid),
    'weekrainmmhi': wxquery("SELECT rainmmhi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND rainmmhi IS NOT NULL ORDER BY rainmmhi DESC Limit 1", pwsid),
    'yearrainmmhi': wxquery("SELECT rainmmhi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND rainmmhi IS NOT NULL ORDER BY rainmmhi DESC Limit 1", pwsid),
    'todaydailyrainmmhi': wxquery("SELECT dailyrainmmhi FROM wxrecords WHERE pwsid = %s AND date(timestamp)=curdate() AND dailyrainmmhi IS NOT NULL ORDER BY dailyrainmmhi DESC Limit 1", pwsid),
    'weekdailyrainmmhi': wxquery("SELECT dailyrainmmhi FROM wxrecords WHERE pwsid = %s AND yearweek(timestamp)=YEARWEEK(NOW()) AND dailyrainmmhi IS NOT NULL ORDER BY dailyrainmmhi DESC Limit 1", pwsid),
    'yeardailyrainmmhi': wxquery("SELECT dailyrainmmhi FROM wxrecords WHERE pwsid = %s AND year(timestamp)=YEAR(NOW()) AND dailyrainmmhi IS NOT NULL ORDER BY dailyrainmmhi DESC Limit 1", pwsid),
    }

    # Send data to web page
    return render_template("rain.html", pwsid=pwsid, currentwx=currentwx, wxrecords=wxrecords, user=user())


@app.route("/addpws", methods=["GET", "POST"])
@login_required
def addpws():
    """ add pws page """

    if request.method == "POST":

        # Get form data
        pwsid = request.form.get("pwsid")
        name = request.form.get("name")
        description = request.form.get("description")
        lat = request.form.get("lat")
        lng = request.form.get("lng")
        height = request.form.get("height")
        wid = request.form.get("wid")
        wkey = request.form.get("wkey")
        wuid = request.form.get("wuid")
        wupass = request.form.get("wupass")

        # Set user id as pwd owner
        userid = session["user_id"]

        # Insert all values into db
        sql = "INSERT INTO pws (userid, pwsid, name, description, lat, lng, height, wid, wkey, wuid, wupass)"\
               "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
        values =  (userid, pwsid, name, description, lat, lng, height, wid, wkey, wuid, wupass) 
        db.execute(sql, values)
        mydb.commit()

        return info_msg("weather station added!")

    else:
        return render_template("add.html", user=user())
    
@app.route("/settings", methods=["GET", "POST"])
@login_required
def settings():
    """ settings page """

    if request.method == "POST":
        curpws = session["curpws"]

        # Get form data
        pwsid = request.form.get("pwsid")
        name = request.form.get("name")
        description = request.form.get("description")
        lat = request.form.get("lat")
        lng = request.form.get("lng")
        height = request.form.get("height")
        wid = request.form.get("wid")
        wkey = request.form.get("wkey")
        wuid = request.form.get("wuid")
        wupass = request.form.get("wupass")

        # Set user id as pwd owner
        userid = session["user_id"]

        # Insert all values into db
        sql = "UPDATE pws SET userid = %s, pwsid = %s, name = %s, description = %s, lat = %s, lng = %s, height = %s, wid = %s, wkey = %s, wuid = %s, wupass = %s WHERE pwsid = %s"
        values = (userid, pwsid, name, description, lat, lng, height, wid, wkey, wuid, wupass, curpws) 
        db.execute(sql, values)
        mydb.commit()

        return info_msg("weather station updated!")

    else:
        # Get pwsid
        curpws = session["curpws"]

        # Get all the user's weather stations
        sql = "SELECT name, description, lat, lng, height, wid, wkey, wkey, wuid, wupass FROM pws WHERE pwsid = %s"
        value = (curpws, )
        db.execute(sql, value)
        rows = db.fetchall()

        name = rows[0][0]
        description = rows[0][1]
        lat = rows[0][2]
        lng = rows[0][3]
        height = rows[0][4]
        wid = rows[0][5]
        wkey = rows[0][6]
        wuid = rows[0][7]
        wupass = rows[0][8]

        return render_template("settings.html", pwsid=curpws, name=name, description=description, lat=lat, lng=lng, height=height, wid=wid, wkey=wkey, wuid=wuid, wupass=wupass, user=user())
    
@app.route("/password", methods=["POST"])
@login_required
def password():

        # get user name
        user_id = session["user_id"]

        # if user wants to change password
        password = request.form.get("password")
        newpass = request.form.get("newpass")
        confirm = request.form.get("confirm")

        # Form validation
        if not password:
            return error_msg("password required", 400)

        if not newpass:
            return error_msg("password confirmation required", 400)

        if newpass != confirm:
            return error_msg("new password and confirmation must match", 400)

        # Get user details from db and set session
        sql = "SELECT id, hash FROM users WHERE id = %s"
        value = (user_id, )
        db.execute(sql, value)
        rows = db.fetchall()

        # Ensure username exists and password is correct
        if len(rows) != 1 or not check_password_hash(rows[0][1], password):
            return error_msg("incorrect password", 403)
        
        # Generate password hash
        hash = generate_password_hash(newpass)

        # Insert all values into db
        sql = "UPDATE users SET hash = %s WHERE id = %s"
        values = (hash, user_id) 
        db.execute(sql, values)
        mydb.commit()

        return info_msg("password changed!")

@app.route("/user", methods=["GET", "POST"])
@login_required
def user_settings():
    """ user settings page """

    if request.method == "POST":

        # Get form data
        name = request.form.get("name")
        first = request.form.get("first")
        last = request.form.get("last")
        email = request.form.get("email")


        # Set user id 
        userid = session["user_id"]

        # Insert all values into db
        sql = "UPDATE users SET first = %s, last = %s, email = %s WHERE id = %s"
        values = (first, last, email, userid) 
        db.execute(sql, values)
        mydb.commit()

        return info_msg("user details updated!")

    else:

        # Get user id...
        userid = session["user_id"]
    
        # Get user details
        sql = "SELECT user, first, last, email FROM users WHERE id = %s"
        value = (userid, )
        db.execute(sql, value)
        rows = db.fetchall()

        name = rows[0][0]
        first = rows[0][1]
        last = rows[0][2]
        email = rows[0][3]

        return render_template("user.html", name=name, first=first, last=last, email=email, user=name)
    
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
