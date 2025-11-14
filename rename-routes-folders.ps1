# Script to rename all 'routes' folders to 'Routes' for PSR-4 compliance

$routesFolders = @(
    "src/Modules/Admin/routes",
    "src/Modules/Auth/routes",
    "src/Modules/DashboardExample/routes",
    "src/Modules/Enhanced/routes",
    "src/Modules/Mail/routes",
    "src/Modules/Moda/routes",
    "src/Modules/Moda/Modules/Suba/routes",
    "src/Modules/Newmod/routes",
    "src/Modules/React/routes",
    "src/Modules/Reactb/routes",
    "src/Modules/Reactcrud/routes",
    "src/Modules/Reacthmr/routes",
    "src/Modules/Reactnb/routes",
    "src/Modules/Test/routes",
    "src/Modules/Testitems/routes",
    "src/Modules/User/routes",
    "src/Modules/Userorm/routes"
)

Set-Location "d:\GitHub\upMVC"

foreach ($folder in $routesFolders) {
    Write-Host "Renaming: $folder" -ForegroundColor Cyan
    
    # First rename to temp
    $tempFolder = $folder + "_TEMP"
    git mv $folder $tempFolder
    
    if ($LASTEXITCODE -eq 0) {
        # Then rename to Routes with capital R
        $finalFolder = $folder -replace "routes$", "Routes"
        git mv $tempFolder $finalFolder
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✓ Success: $finalFolder" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Failed second rename: $tempFolder -> $finalFolder" -ForegroundColor Red
        }
    } else {
        Write-Host "  ✗ Failed first rename: $folder" -ForegroundColor Red
    }
}

Write-Host "`nAll routes folders renamed!" -ForegroundColor Green
Write-Host "Run git status to see changes" -ForegroundColor Yellow
