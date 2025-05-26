@echo off

@REM 获取当前执行文件的目录
set "script_dir=%~dp0"
@REM cmd /c 'copy /b 当前目录的/micro.sfx + 第一个参数 第二个参数'
cmd /c "copy /b ""%script_dir%micro.sfx"" + ""%~1"" ""%~2"""