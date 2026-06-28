@echo off
cd /d C:\wamp64\www\palliative\cron
C:\wamp64\bin\php\php8.2.26\php.exe send_reminders.php >> ..\logs\reminders.log 2>&1 