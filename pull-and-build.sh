#!/bin/sh

rm -rf openjpeg
git clone 'https://github.com/uclouvain/openjpeg.git'

cd openjpeg
build=`git log -1 --format=%ct-%H`
cd ..
export DESTDIR=`pwd`/builds/$build
cd openjpeg
mkdir -p ../builds/$build
cmake .
make
make install
rm -rf ../builds/latest
cp -R ../builds/$build ../builds/latest

cd ..
./compile-opj-md.sh
echo 'BUILD DONE'