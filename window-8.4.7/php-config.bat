@echo off
setlocal enabledelayedexpansion
set spc=spc.exe
set sapi=--build-cli --build-micro
set ext=amqp,apcu,ast,bcmath,bz2,ctype,curl,dba,dom,exif,ffi,fileinfo,filter,iconv,igbinary,libxml,mbstring,msgpack,mysqli,mysqlnd,openssl,pdo,pdo_mysql,pdo_pgsql,pdo_sqlsrv,pgsql,phar,redis,session,shmop,simplexml,soap,sockets,sqlite3,sqlsrv,sysvshm,tokenizer,xlswriter,xml,xmlreader,xmlwriter,zip,zlib
set is_upx=true

echo =======================
echo scp = "%spc%"
echo sapi = "%sapi%"
echo ext = "%ext%"
echo is_upx = "%is_upx%"
echo =======================