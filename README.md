# intg-php

#### 介绍

💕 Intg.php 一个 PHP 的 整 合 库

#### linux 拓展信息

php.ini`/.php/php.ini`

```bash
amqp,apcu,ast,bcmath,ctype,curl,dba,dom,event,exif,ffi,fileinfo,filter,gd,iconv,igbinary,imagick,libxml,mbstring,msgpack,mysqli,mysqlnd,openssl,pcntl,pdo,pdo_mysql,pdo_sqlite,pdo_sqlsrv,pgsql,phar,posix,readline,redis,session,shmop,simplexml,soap,sockets,sqlite3,sqlsrv,sysvmsg,sysvsem,sysvshm,tokenizer,xlswriter,xml,xmlreader,xmlwriter,xsl,zip,zlib
```

#### windows 拓展信息

```bash
amqp,apcu,ast,bcmath,bz2,ctype,curl,dba,dom,exif,ffi,fileinfo,filter,iconv,igbinary,libxml,mbstring,msgpack,mysqli,mysqlnd,openssl,pdo,pdo_mysql,pdo_pgsql,pdo_sqlsrv,pgsql,phar,redis,session,shmop,simplexml,soap,sockets,sqlite3,sqlsrv,sysvshm,tokenizer,xlswriter,xml,xmlreader,xmlwriter,zip,zlib
```

#### 编译二进制文件

`window` 环境下编译二进制文件

- `参数1`：`php`或者`phar` 文件路径

- `参数2`：`exe` 二进制文件路径

```bash
./XXX/php-build.bat xxx.php xxx.exe
```

`linux` 环境下编译二进制文件

- `参数1`：`php`或者`phar` 文件路径

- `参数2`：`bin` 二进制文件路径

```bash
./XXX/php-build xxx.php xxx.bin
```