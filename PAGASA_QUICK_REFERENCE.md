# Quick Reference: PAGASA Time for Uploads

## What Happens When User Uploads Files?

### Before
```
User uploads file → System records current system time → Timestamp may vary
```

### After
```
User uploads file → System fetches PAGASA official time → Records official timestamp
                 → Falls back to system time if offline
```

## Affected Upload Points

| Upload Type | Database Field | Location |
|---|---|---|
| MOV File | `tbfur_mov_uploads.updated_at` | Fund Utilization Report section |
| Written Notice | `tbfur_written_notice.updated_at` | Distribution Recipients section |
| FDP Document | `tbfur_fdp.updated_at` | Financial Disbursement Plan section |
| DILG Approval | `tbfur_mov_uploads.approved_at` | Approval button |
| DILG Approval | `tbfur_written_notice.approved_at` | Approval button |
| DILG Approval | `tbfur_fdp.approved_at` | Approval button |

## Usage in Code

### In Controllers
```php
$uploadedTime = pagasa_time(); // Get PAGASA time
```

### In Models
```php
$this->updated_at = pagasa_time();
$this->save();
```

### In Views
```blade
{{ pagasa_time()->format('M d, Y H:i:s') }}
```

## Configuration Files

- **Service**: `app/Services/PagasaTimeService.php`
- **Helpers**: `app/Helpers/PagasaTimeHelper.php`
- **Controller**: `app/Http/Controllers/FundUtilizationReportController.php`

## Cache Settings

The PAGASA time is cached for **1 hour** to avoid excessive external requests.

To clear cache manually:
```php
PagasaTimeService::clearCache();
```

## Testing

Test if PAGASA time service is working:
```bash
php test_pagasa_integration.php
```

## No User Action Required

✓ No form changes
✓ No UI modifications
✓ No user training needed
✓ Completely transparent to end users

The system automatically uses PAGASA time for all file uploads and approvals.
