@echo off
:config()
:{
	set count_type=1
	:#  0：表示只统计当前目录
	:#  1：表示只统计当前目录，包括子目录

	set count_ext=.php
	:#  表示统计文件的后缀名称
:}

:main()
:{
	if %count_type%==0 (set count_info=当前目录)
	if %count_type%==1 (set count_info=当前目录，包括子目录)
	echo 代码文件数与行数统计(HonestQiao 2006-5-20 0:14)
	echo 统计方式：%count_info%
	echo 文件后缀：%count_ext%
	set /P PauseKey=开始统计（回车开始，Q退出）
	if "%PauseKey%" == "Q" goto :EOF
	if "%PauseKey%" == "q" goto :EOF
	echo.

	cd "%CD%"
	set counts=0
	set count=0
	set tmp_list=%RANDOM%.tmp
	copy /Y NUL %tmp_list% >nul 2>nul
	if %count_type%==0 (dir/b | findstr "%count_ext%\>" > %tmp_list%)
	if %count_type%==1 (dir/b/s | findstr "%count_ext%\>" > %tmp_list%)
	call :function_files_count %tmp_list%
	echo 文件总数：%counts%
	echo 行数总计：%count%
	del /Q %tmp_list%
	echo.
	set /P PauseKey=回车退出
	@echo on
	@goto :EOF
:}

:function_files_count
:{
        set counts_tmp=0
	for /F %%l in ('type %1') do (call :function_files_add "%%l")
	set /A counts=%counts%+%counts_tmp%
	goto :EOF
:}

:function_files_add
:{
	set /A counts_tmp=%counts_tmp%+1
	call :function_file_count %1
	goto :EOF
:}

:function_file_count
:{
	echo 第%counts_tmp%个文件：%1
        set count_tmp=0
	for /F %%l in ('type %1') do (call :function_file_add %1)
	set /A count=%count%+%count_tmp%
	echo 小计行数：%count_tmp%
	echo.
	goto :EOF	
:}

:function_file_add
:{
	set /A count_tmp=%count_tmp%+1
	goto :EOF
:}