#!/bin/bash

cd `dirname "$0"`
cd ..

phpdoc -c doc/config.ini

if [ "$1" = "upload" ]; then
    rsync -avz doc/html/ peschar@earth.374c.com:public_html/framework/api/
fi

