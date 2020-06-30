param (
    [Parameter(Mandatory=$true)][string] $expectedHeadSHA1,
    [Parameter(Mandatory=$true)][string] $expectedBranch
)

$ErrorActionPreference = 'Stop'

Write-Host -NoNewline 'Retrieving event type... '
$eventType = "$(.\bin\ci-info.bat event)"
if ($eventType -eq '') {
    throw
}
Write-Host "done ($eventType)."
Write-Host -NoNewline 'Checking event type... '
if (-not($eventType -ceq 'manual')) {
    throw "failed (expected 'manual', got '$eventType')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving head commit SHA-1... '
$headSHA1="$(.\bin\ci-info.bat sha1)"
if ($headSHA1 -eq '') {
    throw
}
Write-Host "done ($headSHA1)."
Write-Host -NoNewline 'Checking head commit SHA-1... '
if ($headSHA1 -ne $expectedHeadSHA1) {
    throw "failed (expected '$expectedHeadSHA1', got '$headSHA1')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving branch name... '
$branch="$(.\bin\ci-info.bat manual:branch)"
if ($branch -eq '') {
    throw
}
Write-Host "done ($branch)."
Write-Host -NoNewline 'Checking branch name... '
if ($branch -ne $expectedBranch) {
    throw "failed (expected '$expectedBranch', got '$branch')"
}
Write-Host 'passed.'
