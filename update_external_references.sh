#!/bin/bash

mkdir -p ../external 2> /dev/null

cd ../external

git clone git://github.com/neitanod/forceutf8.git forceutf8
cd forceutf8
git pull
cd ..

git clone git://github.com/simplepie/simplepie.git simplepie
cd simplepie
git pull
cd ..

git clone git://github.com/rapid2k1/mysqldump-php.git mysqldump-php
cd mysqldump-php
git pull
cd ..
