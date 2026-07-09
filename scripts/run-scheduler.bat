@echo off
cd /d D:\something\devrates
C:\xampp\php\php.exe artisan schedule:run >> storage\logs\scheduler.log 2>&1
