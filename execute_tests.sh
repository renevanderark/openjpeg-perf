#!/bin/sh

result_dir=out/test/raw
html_dir=out/test/html
html_file=$1-`date +%s`.html
result_file=`date +%s`.log

mkdir -p $result_dir
mkdir -p $html_dir
touch $result_dir/$result_file
php generate-test-suite.php $1 $result_dir/$result_file  | sh
echo "Generating HTML report"
php generate-html-report.php < $result_dir/$result_file > $html_dir/$html_file
echo "DONE"