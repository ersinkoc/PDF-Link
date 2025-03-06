# PDF Link - Setup Creator Script
# This script creates a setup directory with all necessary files for installation

# Configuration
$sourceDir = "."
$setupDir = ".\setup"
$vendorZipFile = "$setupDir\vendor.zip"

# Create setup directory if it doesn't exist
Write-Host "Creating setup directory..." -ForegroundColor Cyan
if (!(Test-Path $setupDir)) {
    New-Item -ItemType Directory -Path $setupDir | Out-Null
}

# Create empty directories
Write-Host "Creating empty directories..." -ForegroundColor Cyan
$emptyDirs = @(
    "$setupDir\database",
    "$setupDir\uploads",
    "$setupDir\backups",
    "$setupDir\cache",
    "$setupDir\logs"
)

foreach ($dir in $emptyDirs) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
        Write-Host "  Created: $dir" -ForegroundColor Green
    }
}

# Copy PHP files
Write-Host "Copying PHP files..." -ForegroundColor Cyan
$phpFiles = @(
    "index.php",
    "view.php",
    "download.php",
    "home.php",
    "error.php",
    "qr.php",
    "mobile_view.php",
    "404.php",
    ".htaccess.example"
)

foreach ($file in $phpFiles) {
    if (Test-Path "$sourceDir\$file") {
        Copy-Item "$sourceDir\$file" -Destination "$setupDir\" -Force
        Write-Host "  Copied: $file" -ForegroundColor Green
    } else {
        Write-Host "  Warning: $file not found" -ForegroundColor Yellow
    }
}

# Copy directories
Write-Host "Copying directories..." -ForegroundColor Cyan
$directories = @(
    "admin",
    "includes",
    "assets",
    "src"
)

foreach ($dir in $directories) {
    if (Test-Path "$sourceDir\$dir") {
        Copy-Item "$sourceDir\$dir" -Destination "$setupDir\" -Recurse -Force
        Write-Host "  Copied: $dir" -ForegroundColor Green
    } else {
        Write-Host "  Warning: $dir not found" -ForegroundColor Yellow
    }
}

# Copy only essential configuration files
Write-Host "Copying essential configuration files..." -ForegroundColor Cyan
$configFiles = @(
    ".env.example",
    "README.md"
)

foreach ($file in $configFiles) {
    if (Test-Path "$sourceDir\$file") {
        Copy-Item "$sourceDir\$file" -Destination "$setupDir\" -Force
        Write-Host "  Copied: $file" -ForegroundColor Green
    } else {
        Write-Host "  Warning: $file not found" -ForegroundColor Yellow
    }
}

# Copy install.php from setup_creator if it exists
if (Test-Path ".\setup_creator\install.php") {
    Copy-Item ".\setup_creator\install.php" -Destination "$setupDir\" -Force
    Write-Host "  Copied: install.php from setup_creator" -ForegroundColor Green
} else {
    # Create install.php file
    Write-Host "Creating install.php file..." -ForegroundColor Cyan
    Copy-Item ".\setup_creator\install.php" -Destination "$setupDir\" -Force
    Write-Host "  Created: install.php" -ForegroundColor Green
}

# Create README file for setup
Write-Host "Creating setup README file..." -ForegroundColor Cyan
$readmeContent = @"
# PDF Link - Installation Package

This directory contains all the necessary files to install PDF Link on your server.

## Installation Instructions

1. Upload the entire contents of this directory to your web server
2. Make sure the following directories are writable by the web server:
   - database
   - uploads
   - backups
   - cache
   - logs
3. Navigate to install.php in your web browser to start the installation process
4. Follow the on-screen instructions to complete the installation

## Requirements

- PHP 8.0+
- SQLite 3
- PHP Extensions:
  - PDO SQLite
  - GD/Imagick (for QR codes)
  - ZIP
  - JSON
  - FileInfo

## After Installation

1. Delete the setup directory for security reasons
2. Log in to the admin panel using the credentials you created during installation
3. Configure system settings in the admin panel

## Support

For support or questions, please refer to the main README.md file.
"@

Set-Content -Path "$setupDir\SETUP_README.md" -Value $readmeContent
Write-Host "  Created: SETUP_README.md" -ForegroundColor Green

# Create ZIP file of vendor directory
Write-Host "Creating vendor.zip file (this may take a while)..." -ForegroundColor Cyan
if (Test-Path "$sourceDir\vendor") {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory("$sourceDir\vendor", $vendorZipFile)
    Write-Host "  Created: vendor.zip" -ForegroundColor Green
} else {
    Write-Host "  Error: vendor directory not found" -ForegroundColor Red
}

# Final message
Write-Host "`nSetup directory created successfully!" -ForegroundColor Green
Write-Host "Location: $setupDir" -ForegroundColor Green
Write-Host "`nTo install PDF Link:" -ForegroundColor Cyan
Write-Host "1. Upload the entire 'setup' directory to your web server" -ForegroundColor Cyan
Write-Host "2. Navigate to install.php in your web browser" -ForegroundColor Cyan
Write-Host "3. Follow the on-screen instructions to complete the installation" -ForegroundColor Cyan
