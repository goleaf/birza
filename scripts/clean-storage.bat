@echo off
REM Clean storage directories (Windows)

REM Remove log files
if exist storage\logs\*.log del /f /q storage\logs\*.log

REM Clean cache data directory (excluding .gitignore)
for /r storage\framework\cache\data\ %%f in (*) do (
    if /i not "%%~nxf"==".gitignore" del /f /q "%%f"
)

REM Clean debugbar directory (excluding .gitignore)
for /r storage\debugbar\ %%f in (*) do (
    if /i not "%%~nxf"==".gitignore" del /f /q "%%f"
)

REM Clean views directory (excluding .gitignore)
for /r storage\framework\views\ %%f in (*) do (
    if /i not "%%~nxf"==".gitignore" del /f /q "%%f"
)

REM Clean sessions directory (excluding .gitignore)
for /r storage\framework\sessions\ %%f in (*) do (
    if /i not "%%~nxf"==".gitignore" del /f /q "%%f"
)

