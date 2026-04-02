@echo off
TITLE MOLKA MODEN - Development Server
echo.
echo  ---------------------------------------------------------
echo    MOLKA MODEN : Lancement du serveur de developpement
echo  ---------------------------------------------------------
echo.

:: Se deplacer dans le repertoire du script
cd /d %~dp0

:: Verifier si composer est installe
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERREUR] Composer n'est pas installe ou pas dans le PATH.
    echo Veuillez installer Composer pour continuer.
    pause
    exit /b
)

:: Verifier si npm est installe
where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERREUR] NPM / Node.js n'est pas installe.
    echo Veuillez installer Node.js pour continuer.
    pause
    exit /b
)

echo [+] Demarrage de Laravel et Vite...
echo.

:: Lancer uniquement Artisan Serve et NPM Run Dev
npx concurrently -c "#93c5fd,#c4b5fd" "php artisan serve" "npm run dev" --names=server,vite --kill-others

if %errorlevel% neq 0 (
    echo.
    echo [ERREUR] Un des serveurs s'est arrete.
    pause
)
