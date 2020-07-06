param (
    [Parameter(Mandatory = $true)][string] $expectedHeadSHA1,
    [Parameter(Mandatory = $true)][string] $expectedBranch,
    [Parameter(Mandatory = $true)][AllowEmptyString()][string] $expectedBaseSHA1,
    [Parameter(Mandatory = $true)][AllowEmptyString()][string] $expectedBaseRangeFailure
)

$ErrorActionPreference = 'Stop'

Write-Host -NoNewline 'Retrieving event type... '
$eventType = "$(.\bin\ci-info.bat event)"
if ($eventType -eq '') {
    throw
}
Write-Host "done ($eventType)."
Write-Host -NoNewline 'Checking event type... '
if (-not($eventType -ceq 'push')) {
    throw "failed (expected 'push', got '$eventType')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving head commit SHA-1... '
$headSHA1 = "$(.\bin\ci-info.bat sha1)"
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
$branch = "$(.\bin\ci-info.bat push:branch)"
if ($branch -eq '') {
    throw
}
Write-Host "done ($branch)."
Write-Host -NoNewline 'Checking branch name... '
if ($branch -ne $expectedBranch) {
    throw "failed (expected '$expectedBranch', got '$branch')"
}
Write-Host 'passed.'

if ($expectedBaseRangeFailure -eq '') {
    Write-Host -NoNewline 'Retrieving previous commit SHA-1... '
    $baseSha1 = "$(.\bin\ci-info.bat push:prev:sha1)"
    if ($baseSha1 -eq '') {
        throw
    }
    Write-Host "done ($baseSha1)."
    Write-Host -NoNewline 'Checking previous commit SHA-1... '
    if ($baseSha1 -ne $expectedBaseSHA1) {
        throw "failed (expected '$expectedBaseSHA1', got '$baseSha1')"
    }
    Write-Host 'passed.'

    Write-Host -NoNewline 'Retrieving commit range... '
    $range = "$(.\bin\ci-info.bat push:range)"
    if ($range -eq '') {
        throw
    }
    Write-Host "done ($range)."
    Write-Host -NoNewline 'Checking commit range... '
    if ($range -ne "$expectedBaseSHA1...$expectedHeadSHA1") {
        throw "failed (expected '$expectedBaseSHA1...$expectedHeadSHA1', got '$range')"
    }
    Write-Host 'passed.'
}
else {
    Write-Host -NoNewline 'Retrieving previous commit SHA-1... '
    $exception = $null
    try {
        $failure = "$(.\bin\ci-info push:prev:sha1 2>&1)"
    }
    catch {
        $exception = $_
    }
    if ($null -eq $exception) {
        throw "expected exception, received '$failure'"
    }
    $failure = $exception.Exception.Message
    Write-Host 'correctly received failure message.'
    Write-Host -NoNewline 'Checking previous commit failure... '
    if (-not($failure -like "*$expectedBaseRangeFailure*")) {
        throw "failed ('$failure' does not contain '$expectedBaseRangeFailure')"
    }
    Write-Host 'passed.'

    Write-Host -NoNewline 'Retrieving commit range... '
    $exception = $null
    try {
        $failure = "$(.\bin\ci-info push:range 2>&1)"
    }
    catch {
        $exception = $_
    }
    if ($null -eq $exception) {
        throw "expected exception, received '$failure'"
    }
    $failure = $exception.Exception.Message
    Write-Host 'correctly received failure message.'
    Write-Host -NoNewline 'Checking commit range failure... '
    if (-not($failure -like "*$expectedBaseRangeFailure*")) {
        throw "failed ('$failure' does not contain '$expectedBaseRangeFailure')"
    }
    Write-Host 'passed.'
}
