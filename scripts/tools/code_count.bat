@echo off
:config()
:{
	set count_type=1
	:#  0����ʾֻͳ�Ƶ�ǰĿ¼
	:#  1����ʾֻͳ�Ƶ�ǰĿ¼��������Ŀ¼

	set count_ext=.php
	:#  ��ʾͳ���ļ��ĺ�׺����
:}

:main()
:{
	if %count_type%==0 (set count_info=��ǰĿ¼)
	if %count_type%==1 (set count_info=��ǰĿ¼��������Ŀ¼)
	echo �����ļ���������ͳ��(HonestQiao 2006-5-20 0:14)
	echo ͳ�Ʒ�ʽ��%count_info%
	echo �ļ���׺��%count_ext%
	set /P PauseKey=��ʼͳ�ƣ��س���ʼ��Q�˳���
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
	echo �ļ�������%counts%
	echo �����ܼƣ�%count%
	del /Q %tmp_list%
	echo.
	set /P PauseKey=�س��˳�
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
	echo ��%counts_tmp%���ļ���%1
        set count_tmp=0
	for /F %%l in ('type %1') do (call :function_file_add %1)
	set /A count=%count%+%count_tmp%
	echo С��������%count_tmp%
	echo.
	goto :EOF	
:}

:function_file_add
:{
	set /A count_tmp=%count_tmp%+1
	goto :EOF
:}