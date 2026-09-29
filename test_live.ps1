$baseUrl = "https://surakshaar-admin.onrender.com/api/v1"

function Test-Endpoint {
    param (
        [string]$Name,
        [string]$Url,
        [string]$Method = "POST",
        [object]$Body = $null,
        [string]$Token = $null
    )
    
    $headers = @{"Content-Type" = "application/json"}
    if ($Token) {
        $headers["Authorization"] = "Bearer $Token"
    }
    
    Write-Host "Testing $Name ($Method $Url)..."
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $headers
        }
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json -Depth 10 -Compress)
        }
        
        $response = Invoke-RestMethod @params
        Write-Host "Success: $(($response | ConvertTo-Json -Depth 5 -Compress))"
        return $response
    } catch {
        Write-Host "Failed: $_"
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $reader.BaseStream.Position = 0
            $errBody = $reader.ReadToEnd()
            Write-Host "Response Body: $errBody"
        }
        return $null
    }
}

# 1. Test Login
$loginBody = @{
    worker_id = "TEST-W-999"
    name = "API Test Worker"
    language = "English"
    role = "Miner"
    site = "Test Site"
}
$loginRes = Test-Endpoint -Name "Login" -Url "$baseUrl/auth/login" -Body $loginBody

$token = $null
if ($loginRes -and $loginRes.success) {
    $token = $loginRes.session_token
    Write-Host "Extracted Token: (hidden for security)"
} else {
    Write-Host "Aborting further tests due to login failure."
    exit
}

# 2. Test Heartbeat
$heartbeatRes = Test-Endpoint -Name "Heartbeat" -Url "$baseUrl/heartbeat" -Token $token -Body @{}

# 3. Test Training
$trainingBody = @{
    module_code = "fire_safety"
    score = 95
    attempts = 1
    completed = $true
    critical_fail = $false
    ar_used = $true
    scenario_id = "fire_test"
}
Test-Endpoint -Name "Training" -Url "$baseUrl/training/result" -Token $token -Body $trainingBody

# 4. Test Assessment
$assessmentBody = @{
    module_code = "fire_safety"
    score = 88
    passed = $true
}
Test-Endpoint -Name "Assessment" -Url "$baseUrl/assessment/result" -Token $token -Body $assessmentBody

# 5. Test Certificate
$certBody = @{
    certificate_id = "TEST-CERT-999"
    module_code = "fire_safety"
    score = 95
    status = "valid"
}
Test-Endpoint -Name "Certificate" -Url "$baseUrl/certificate/result" -Token $token -Body $certBody

# 6. Test Retention
$retentionBody = @{
    module_code = "fire_safety"
    score = 90
    retention_date = (Get-Date).ToString("yyyy-MM-ddTHH:mm:ssZ")
}
Test-Endpoint -Name "Retention" -Url "$baseUrl/retention/result" -Token $token -Body $retentionBody

# 7. Test Sync
$syncBody = @{
    sync_data = @(
        @{
            type = "training_result"
            payload = @{
                module_code = "fire_safety"
                score = 100
                completed = $true
            }
        },
        @{
            type = "assessment_result"
            payload = @{
                module_code = "fire_safety"
                score = 80
            }
        }
    )
}
Test-Endpoint -Name "Sync" -Url "$baseUrl/sync" -Token $token -Body $syncBody

# 8. Test Logout
Test-Endpoint -Name "Logout" -Url "$baseUrl/auth/logout" -Token $token -Body @{}
