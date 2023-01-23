#! /usr/bin/python3.9

import os 

here = os.path.dirname(__file__)
os.chdir(here)


import logging
import sys
logging.basicConfig(stream=sys.stderr)
sys.path.insert(0, '/srv/www/html/metlog/')
from metlog_app import app as application
application.secret_key = '4v76249v76924'
