#!/bin/bash

git pull 

find ./ -type d -exec chmod 775 {} \;
find ./ -type f -exec chmod 644 {} \;

chmod 700 update.sh
