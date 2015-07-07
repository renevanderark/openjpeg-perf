FROM ubuntu:trusty

RUN apt-get update
RUN apt-get -y install php5 git-core gcc make cmake
RUN git clone https://github.com/renevanderark/openjpeg-perf
EXPOSE 5002
CMD ["php", "-S", "0.0.0.0:5002", "-d", "upload_max_filesize=150M", "-d", "post_max_size=150M", "web.php"]
