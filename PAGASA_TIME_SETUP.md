# PAGASA Time Service Implementation

## Overview
The PAGASA Time Service synchronizes your application with the official Philippine time from the Philippine Atmospheric, Geophysical and Astronomical Services Administration (PAGASA).

## What was implemented

### 1. Service Class: `PagasaTimeService`
Located at `app/Services/PagasaTimeService.php`

**Methods:**
- `getCurrentTime()` - Fetches current time from PAGASA, falls back to system time
- `getAdjustedTime()` - Returns system time adjusted by PAGASA offset
- `getTimeOffset()` - Gets the time difference (in seconds) between PAGASA and system
- `clearCache()` - Clears the cached time offset

### 2. Helper Functions
Located at `app/Helpers/PagasaTimeHelper.php`

Auto-loaded globally (no need to import):
- `pagasa_time()` - Get PAGASA time
- `pagasa_adjusted_time()` - Get adjusted system time
- `pagasa_time_offset()` - Get offset in seconds

## Usage Examples

### In Controllers
```php
<?php

namespace App\Http\Controllers;

use App\Services\PagasaTimeService;

class MyController extends Controller
{
    public function example()
    {
        // Direct service usage
        $currentTime = PagasaTimeService::getCurrentTime();
        echo $currentTime->format('Y-m-d H:i:s');
        
        // Or use helper functions
        $time = pagasa_time();
        echo $time->format('Y-m-d H:i:s');
    }
}
```

### In Models
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\PagasaTimeService;

class User extends Model
{
    public function verifyToken()
    {
        // Use PAGASA time for token verification
        if ($this->token_expires_at < pagasa_time()) {
            return false; // Token expired
        }
        return true;
    }
}
```

### In Views/Blade
```blade
<p>Current Official Time: {{ pagasa_time()->format('Y-m-d H:i:s') }}</p>
<p>System Time (Adjusted): {{ pagasa_adjusted_time()->format('Y-m-d H:i:s') }}</p>
```

### In Commands
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PagasaTimeService;

class MyCommand extends Command
{
    public function handle()
    {
        $time = PagasaTimeService::getCurrentTime();
        $this->info("Current PAGASA time: {$time}");
    }
}
```

### For Email Verification Tokens
```php
<?php

namespace App\Models;

class User extends Model
{
    public function generateVerificationToken()
    {
        // Set expiration to 1 hour from PAGASA time
        $this->email_verification_token = Str::random(60);
        $this->email_token_expires_at = pagasa_time()->addHour();
        $this->save();
    }
    
    public function isVerificationTokenValid()
    {
        return $this->email_token_expires_at > pagasa_time();
    }
}
```

## How It Works

1. **Fetches PAGASA HTML** - Hits `https://oras.pagasa.dost.gov.ph/`
2. **Parses Time Data** - Extracts time and date from JavaScript in the page
3. **Caches Result** - Caches for 1 hour to avoid excessive requests
4. **Fallback** - Uses system time if PAGASA is unavailable
5. **Timezone** - Sets Asia/Manila timezone automatically

## Configuration

### Cache Duration
Edit `app/Services/PagasaTimeService.php` and change `CACHE_DURATION`:
```php
private const CACHE_DURATION = 3600; // Change this (in seconds)
```

### Clear Cache Manually
```php
PagasaTimeService::clearCache();
```

## Testing

Run the test file:
```bash
php test_pagasa_integration.php
```

## Key Features

✓ Automatic fallback to system time if PAGASA unavailable
✓ 5-second timeout + 2 retry attempts
✓ Caching to minimize external requests
✓ Asia/Manila timezone pre-configured
✓ Helper functions for easy access
✓ Comprehensive error logging

## Performance Impact

- **First call**: ~1-5 seconds (network request)
- **Subsequent calls (within cache period)**: Instant
- **Fallback**: Immediate (no network latency)

## Perfect For

- Email verification token expiration
- Email sending timestamp synchronization
- Time-sensitive operations
- Report generation with accurate timestamps
- Session timeout verification
