#!/bin/sh

export LD_LIBRARY_PATH=`pwd`/builds/latest/usr/local/lib
gcc -I`pwd`/builds/latest/usr/local/include/openjpeg-2.1/ lib/log.c lib/opj_res.c opj-md.c -o opj-md -fPIC -lm -lpng -lopenjp2 -lcurl -ljpeg  -std=c99 -Wall -Wextra -Wmissing-prototypes -Wstrict-prototypes -Wold-style-definition -D_XOPEN_SOURCE=700
