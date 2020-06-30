param (
    [Parameter(Mandatory=$true)][string] $expectedMergeSHA1,
    [Parameter(Mandatory=$true)][string] $expectedBaseBranch,
    [Parameter(Mandatory=$true)][AllowEmptyString()][string] $expectedBaseSHA1,
    [Parameter(Mandatory=$true)][string] $expectedHeadSHA1,
    [Parameter(Mandatory=$true)][AllowEmptyString()][string] $expectedRange
)

$ErrorActionPreference = 'Stop'

Write-Host -NoNewline 'Retrieving event type... '
$eventType = "$(.\bin\ci-info.bat event)"
if ($eventType -eq '') {
    throw
}
Write-Host "done ($eventType)."
Write-Host -NoNewline 'Checking event type... '
if (-not($eventType -ceq 'pr')) {
    throw "failed (expected 'pr', got '$eventType')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving wrong merge SHA-1... '
$wrongMergeSHA1 = "$(.\bin\ci-info.bat pr:wrongsha1)"
Write-Host "done ($wrongMergeSHA1)."
if ($wrongMergeSHA1 -ne '') {
    Write-Host -NoNewline 'Checking wrong merge SHA-1... '
    if ($wrongMergeSHA1 -ne $expectedMergeSHA1) {
        throw "failed (expected '$expectedMergeSHA1', got '$wrongMergeSHA1')"
    }
    Write-Host 'passed.'
}

Write-Host -NoNewline 'Retrieving correct merge SHA-1... '
$mergeSHA1="$(.\bin\ci-info.bat sha1)"
if ($mergeSHA1 -eq '') {
    throw
}
Write-Host "done ($mergeSHA1)."
Write-Host -NoNewline 'Checking correct merge SHA-1... '
if ($wrongMergeSHA1 -ne '') {
    if (-not($mergeSHA1 -match '^[a-f0-9]{40}$')) {
        throw 'failed (wrong syntax)'
    }
} else {
    if ($mergeSHA1 -ne $expectedMergeSHA1) {
        throw "failed (expected '$expectedMergeSHA1', got '$mergeSHA1')"
    }
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving base branch name... '
$baseBranch="$(.\bin\ci-info.bat pr:base:branch)"
if ($baseBranch -eq '') {
    throw
}
Write-Host "done ($baseBranch)."
Write-Host -NoNewline 'Checking base branch name... '
if ($baseBranch -ne $expectedBaseBranch) {
    throw "failed (expected '$expectedBaseBranch', got '$baseBranch')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving base branch SHA-1... '
$baseSHA1="$(.\bin\ci-info.bat pr:base:sha1)"
if ($baseSHA1 -eq '') {
    throw
}
Write-Host "done ($baseSHA1)."
Write-Host -NoNewline 'Checking base branch SHA-1... '
if ($expectedBaseSHA1 -ne '') {
    if ($baseSHA1 -ne $expectedBaseSHA1) {
        throw "failed (expected '$expectedBaseSHA1', got '$baseSHA1')"
    }
} else {
    if (-not($baseSHA1 -match '^[a-f0-9]{40}$')) {
        throw 'failed (wrong syntax)'
    }
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving PR branch SHA-1... '
$headSHA1="$(.\bin\ci-info.bat pr:head:sha1)"
if ($headSHA1 -eq '') {
    throw
}
Write-Host "done ($headSHA1)."
Write-Host -NoNewline 'Checking PR branch SHA-1... '
if ($headSHA1 -ne $expectedHeadSHA1) {
    throw "failed (expected '$expectedHeadSHA1', got '$headSHA1')"
}
Write-Host 'passed.'

Write-Host -NoNewline 'Retrieving PR range... '
$range="$(.\bin\ci-info.bat pr:range)"
if ($range -eq '') {
    throw
}
Write-Host "done ($range)."
Write-Host -NoNewline 'Checking PR range... '
if ($expectedRange -ne '') {
    if ($range -ne $expectedRange) {
        throw "failed (expected '$expectedRange', got '$range')"
    }
} else {
    if (-not($range -match '^[a-f0-9]{40}\.\.\.[a-f0-9]{40}$')) {
        throw 'failed (wrong syntax)'
    }
}
Write-Host 'passed.'
