param (
    [Parameter(Mandatory=$true)][string] $expectedDriver
)

$ErrorActionPreference = 'Stop'

Write-Host -NoNewline 'Retrieving driver... '
$driver = "$(.\bin\ci-info.bat driver)"
if ($driver -eq '') {
    throw
}
Write-Host "done ($driver)."

Write-Host -NoNewline 'Checking driver... '
if (-not($driver -ceq $expectedDriver)) {
    throw "failed (expected '$expectedDriver', got '$driver')"
}
Write-Host 'passed.'
