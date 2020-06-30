param (
    [Parameter(Mandatory=$true)][string] $expectedHeadSHA1,
    [Parameter(Mandatory=$true)][string] $expectedTag
)

$ErrorActionPreference = 'Stop'

Write-Host -NoNewline 'Retrieving event type... '
$eventType = "$(.\bin\ci-info.bat event)"
if ($eventType -eq '') {
    throw
}
Write-Host "done ($eventType)."
Write-Host -NoNewline 'Checking event type... '
if (-not($eventType -ceq 'tag')) {
    throw "failed (expected 'tag', got '$eventType')"
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

Write-Host -NoNewline 'Retrieving tag name... '
$tag="$(.\bin\ci-info.bat tag:name)"
if ($tag -eq '') {
    throw
}
Write-Host "done ($tag)."
Write-Host -NoNewline 'Checking tag name... '
if ($tag -ne $expectedTag) {
    throw "failed (expected '$expectedTag', got '$tag')"
}
Write-Host 'passed.'
