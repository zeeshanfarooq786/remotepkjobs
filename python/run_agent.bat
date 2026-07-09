@echo off
REM Usage: run_agent.bat job_scraper
setlocal

if "%~1"=="" (
    echo Usage: run_agent.bat job_scraper
    exit /b 1
)

set "AGENT=%~1"

if /I not "%AGENT%"=="job_scraper" if /I not "%AGENT%"=="rate_updater" if /I not "%AGENT%"=="github_updater" if /I not "%AGENT%"=="update_exchange_rates" if /I not "%AGENT%"=="blog_generator" (
    echo Unknown agent: %AGENT%
    echo Allowed: job_scraper, rate_updater, github_updater, update_exchange_rates, blog_generator
    exit /b 1
)

cd /d D:\something\devrates\python

if exist venv\Scripts\activate.bat (
    call venv\Scripts\activate.bat
)

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd"') do set LOGDATE=%%i

C:\Users\Zeeshan_Farooq\AppData\Local\Programs\Python\Python312\python.exe agents\%AGENT%.py >> logs\cron_%LOGDATE%.log 2>&1

endlocal
