"""
Outputs longitude and latitude for a danish zipcode separated by a single space.
Uses the multimap api.
"""

import urllib2
import sys
from textwrap import dedent as d
from simplexml import parsestring

# Globals.
URL = ('http://developer.multimap.com/API/geocode/1.2/OA10110913592184095'
      '?countryCode=DK&postalCode=%d')

# Parse options.
try:
    zipcode = int(sys.argv[1])
    if zipcode < 1000 or zipcode > 9999:
        raise ValueError
except IndexError:
    sys.exit(d("""\
        Outputs longitude and latitude for a danish zipcode.
        Usage: multimap-danish-zipcode-search.py [ZIPCODE]"""))
except ValueError:
    sys.exit("[ZIPCODE] must be in the range 1000-9999")
    
# Get response from multimap api.
response = urllib2.urlopen(URL % zipcode).read()\
    .replace('"http://clients.multimap.com/API"', '""').strip()

# Parse xml and write longitude and latitude.
xml = parsestring(response)
try:
    print '%s %s' % (xml.Location.Point.Lat, xml.Location.Point.Lon)
except KeyError:
    pass # Do nothing on error.
