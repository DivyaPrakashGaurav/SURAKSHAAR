# API Integration Guide

## Base URLs
- **Localhost:** `http://localhost/SIH26041admin`
- **Production:** `https://your-production-url.com` (Update this with your deployed domain)

## 1. Worker Login
**Endpoint:** `POST /api/v1/auth/login`

**Request:**
```json
{
  "worker_id": "W-101",
  "name": "Amit Kumar",
  "language": "English",
  "role": "Miner",
  "site": "Site Alpha"
}
```

**Expected Response (Success):**
```json
{
  "success": true,
  "session_token": "a1b2c3d4...",
  "worker": {
    "id": 1,
    "worker_id": "W-101",
    "name": "Amit Kumar"
  }
}
```

## Authorization Header
All subsequent requests (except login) require the Authorization header using the `session_token` returned from the login API.

**Header:**
```
Authorization: Bearer <session_token>
```

## 2. Heartbeat (Online Status)
**Endpoint:** `POST /api/v1/heartbeat`

**Request:**
```json
{}
```

**Expected Response:**
```json
{
  "success": true,
  "last_seen_at": "2023-10-25T10:00:00+00:00"
}
```

## 3. Training Result
**Endpoint:** `POST /api/v1/training/result`

**Request:**
```json
{
  "module_code": "fire_safety",
  "score": 90,
  "attempts": 1,
  "completed": true,
  "critical_fail": false,
  "ar_used": true,
  "scenario_id": "fire_01",
  "started_at": "2023-10-25T10:00:00+00:00",
  "completed_at": "2023-10-25T10:30:00+00:00"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Training result saved"
}
```

## 4. Assessment Result
**Endpoint:** `POST /api/v1/assessment/result`

**Request:**
```json
{
  "module_code": "fire_safety",
  "score": 85,
  "passed": true,
  "date": "2023-10-25T10:45:00+00:00"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Assessment result saved"
}
```

## 5. Certificate
**Endpoint:** `POST /api/v1/certificate/result`

**Request:**
```json
{
  "certificate_id": "CERT-12345",
  "module_code": "fire_safety",
  "score": 85,
  "issue_date": "2023-10-25T10:45:00+00:00",
  "status": "valid",
  "verification_token": "verif-xyz123"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Certificate saved"
}
```

## 6. Retention Result
**Endpoint:** `POST /api/v1/retention/result`

**Request:**
```json
{
  "module_code": "fire_safety",
  "score": 80,
  "retention_date": "2023-10-25T11:00:00+00:00"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Retention result saved"
}
```

## 7. Sync (Offline Support)
**Endpoint:** `POST /api/v1/sync`

**Request:**
```json
{
  "sync_data": [
    {
      "type": "training_result",
      "payload": {
        "module_code": "fire_safety",
        "score": 95,
        "completed": true
      }
    }
  ]
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Sync completed",
  "results": {
    "successful": 1,
    "failed": 0,
    "errors": []
  }
}
```

## 8. Logout
**Endpoint:** `POST /api/v1/auth/logout`

**Request:**
```json
{}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

## Common Errors
- `400 Bad Request`: Missing required fields like `worker_id` or `module_code`.
- `401 Unauthorized`: Missing or invalid `Authorization: Bearer <token>` header.
- `403 Forbidden`: Worker account is inactive.
- `405 Method Not Allowed`: Using GET instead of POST.
- `500 Internal Server Error`: Database insertion failed (e.g., trying to insert when `assessments` table hasn't been created yet).
