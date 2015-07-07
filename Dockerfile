FROM ubuntu:trusty

RUN apt-get update
RUN apt-get -y install php5 git-core gcc make cmake
RUN git clone https://github.com/renevanderark/openjpeg-perf
EXPOSE 5002
CMD ["php", "-S", "0.0.0.0:5002", "web.php"]
