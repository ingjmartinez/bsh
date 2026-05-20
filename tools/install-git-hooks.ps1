Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
Set-Location $repoRoot

git config core.hooksPath .githooks

Write-Output "Git hooks configured: core.hooksPath=.githooks"
Write-Output "Pre-commit guard enabled (encoding check)."
