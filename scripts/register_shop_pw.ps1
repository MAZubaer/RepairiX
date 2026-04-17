param(
  [string]$Base = 'http://127.0.0.1:8000'
)

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
Write-Output "GET $Base/register/shop"
try {
  $r = Invoke-WebRequest -Uri "$Base/register/shop" -WebSession $session -UseBasicParsing -ErrorAction Stop -TimeoutSec 30
  $r.Content | Out-File .\scripts\last_get.html -Encoding utf8
  Write-Output 'GET_OK'
} catch {
  Write-Output 'GET_ERR:'
  Write-Output $_.Exception.Message
}

# extract and decode XSRF cookie
$xsrf_enc = ($session.Cookies.GetCookies($Base) | Where-Object { $_.Name -eq 'XSRF-TOKEN' }).Value
if (-not $xsrf_enc) { Write-Output 'XSRF cookie not found'; exit 1 }
$xsrf = [System.Uri]::UnescapeDataString($xsrf_enc)
Write-Output "DECODED_XSRF:$xsrf"

# prepare form
$form = @{ shop_name='PSPowerShellShop'; email='ps'+(Get-Random)+'@example.com'; phone='01811'+(Get-Random); shop_address='Addr'; password='password123'; password_confirmation='password123' }

Write-Output "POST $Base/register/shop"
try {
  $r2 = Invoke-WebRequest -Uri "$Base/register/shop" -Method Post -WebSession $session -Headers @{ 'X-XSRF-TOKEN' = $xsrf; 'X-Requested-With' = 'XMLHttpRequest'; 'X-Inertia' = 'true' } -Body $form -UseBasicParsing -ErrorAction Stop -TimeoutSec 30
  if ($r2 -ne $null) {
    $r2.Content | Out-File .\scripts\last_post.html -Encoding utf8
    if ($r2.StatusCode) { Write-Output "Status: $($r2.StatusCode)" } else { Write-Output "Response received (no StatusCode property)" }
  } else { Write-Output 'No response object returned from Invoke-WebRequest' }
} catch {
  if ($_.Exception.Response) {
    $resp = $_.Exception.Response
    $sr = New-Object System.IO.StreamReader($resp.GetResponseStream())
    $body = $sr.ReadToEnd()
    $body | Out-File .\scripts\last_post.html -Encoding utf8
    Write-Output 'POST_ERR:'
    Write-Output $_.Exception.Message
  } else {
    Write-Output 'POST_FAILED:'
    Write-Output $_.Exception.Message
  }
}

Write-Output 'DONE'
