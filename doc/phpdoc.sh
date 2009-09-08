#!/bin/bash

set -e
set -u

cd `dirname "$0"`
cd ..

phpdoc -c doc/config.ini
rsync -avz doc/html/ peschar@earth.374c.com:public_html/framework/api/

