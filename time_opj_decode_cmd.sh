#!/bin/sh


# example invocations:
#                          build-ID (see builds dir after pull_and_build.sh) sample file (see samples dir)  -r -t
# ./time_opj_decode_cmd.sh 20150628-c6c49865fefd9c19471b0e2392a9422a3e758597 sample1.jp2                     0  1
# ./time_opj_decode_cmd.sh 20150628-c6c49865fefd9c19471b0e2392a9422a3e758597 sample1.jp2                     4

build=$1
sample=$2
reduction=$3
tile_idx=$4

mkdir -p `pwd`/out
export LD_LIBRARY_PATH=`pwd`/builds/$build/usr/local/lib
ts=$(date +%s%N)
if [ -z "$4" ] 
then `pwd`/builds/$build/usr/local/bin/opj_decompress -i `pwd`/samples/$sample -o `pwd`/out/test.raw -r $reduction 2>&1 1> /dev/null
else `pwd`/builds/$build/usr/local/bin/opj_decompress -i `pwd`/samples/$sample -o `pwd`/out/test.raw -r $reduction -t $tile_idx 2>&1 1> /dev/null 
fi
tt=$((($(date +%s%N) - $ts)/1000000))
echo "Timed: $tt MS"
