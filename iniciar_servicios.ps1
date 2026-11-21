<#
.SYNOPSIS
    Prepara las bases de datos y levanta los 3 microservicios de El Cuervo con el servidor embebido de PHP.

.DESCRIPTION
    ms-reservas :8102 | ms-productos :8103 | ms-pedidos :8104
    Primero ejecuta scripts/preparar_bd.php de cada servicio (crea la BD y aplica el SQL del ejercicio si faltan
    las tablas) y luego abre una ventana por servicio (ciérrela para detenerlo). Requiere "composer install" y un
    .env (copiado de .env.example) en cada carpeta ms-*. Si un puerto ya está en uso, no lanza ese servicio.

.EXAMPLE
    .\iniciar_servicios.ps1
#>
param(
    [string]$Php = 'php',
    [string]$Direccion = '127.0.0.1'
)

$servicios = [ordered]@{
    'ms-reservas'  = 8102
    'ms-productos' = 8103
    'ms-pedidos'   = 8104
}

foreach ($servicio in $servicios.GetEnumerator()) {
    $carpeta = Join-Path $PSScriptRoot $servicio.Key
    $puerto = $servicio.Value

    if (-not (Test-Path (Join-Path $carpeta 'vendor/autoload.php'))) {
        Write-Warning "$($servicio.Key): falta vendor/. Ejecute 'composer install' en $carpeta"
        continue
    }

    if (-not (Test-Path (Join-Path $carpeta '.env'))) {
        Copy-Item (Join-Path $carpeta '.env.example') (Join-Path $carpeta '.env')
        Write-Host "$($servicio.Key): se creó .env a partir de .env.example"
    }

    if (Get-NetTCPConnection -State Listen -LocalPort $puerto -ErrorAction SilentlyContinue) {
        Write-Warning "$($servicio.Key): el puerto $puerto ya está en uso; no se lanza."
        continue
    }

    Push-Location $carpeta
    & $Php 'scripts/preparar_bd.php'
    Pop-Location

    Start-Process -FilePath $Php -WorkingDirectory $carpeta `
        -ArgumentList '-S', "$Direccion`:$puerto", '-t', 'public'
    Write-Host "$($servicio.Key) -> http://$Direccion`:$puerto"
}
