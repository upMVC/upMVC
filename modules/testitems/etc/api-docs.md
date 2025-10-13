# TestItems Enhanced API Documentation

## Overview
This is an enhanced API module for TestItems with auto-discovery capabilities.

**Features:**
- ✅ Auto-discovered by InitModsImproved.php
- ✅ Environment-aware configuration
- ✅ Caching support
- ✅ Enhanced error handling
- ✅ Debug mode support

## Base URL
```
{BASE_URL}/testitemss/api
```

## Auto-Discovery
This module is automatically discovered by `InitModsImproved.php`. No manual registration required!

**Discovery path:** `modules/TestItems/routes/Routes.php`

## Enhanced Features

### Environment Awareness
The API respects environment settings:
- `APP_ENV=development` - Enhanced debugging and error messages
- `APP_ENV=production` - Optimized performance and minimal output
- `ROUTE_USE_CACHE=true` - Enables response caching
- `ROUTE_DEBUG_OUTPUT=true` - Shows debug information

### Caching Support
Responses are automatically cached when `ROUTE_USE_CACHE=true`:
- Cache TTL: 3600 seconds (1 hour)
- Cache invalidation: Automatic on data changes
- Development mode: Caching disabled for fresh data

### Error Handling
Enhanced error responses with environment-appropriate detail levels:

**Development:**
```json
{
    "error": "Detailed error message",
    "debug": {
        "file": "/path/to/file.php",
        "line": 42,
        "trace": ["..."]
    },
    "environment": "development"
}
```

**Production:**
```json
{
    "error": "An error occurred",
    "environment": "production"
}
```

## Endpoints

### GET /testitemss/api
Get module information and status.

**Response:**
```json
{
    "success": true,
    "module": "TestItems",
    "version": "2.0-enhanced",
    "features": {
        "auto_discovery": true,
        "caching": true,
        "middleware_ready": true,
        "submodule_support": true
    },
    "request": {
        "route": "/testitemss/api",
        "method": "GET",
        "timestamp": "2023-01-01T12:00:00+00:00"
    }
}
```

### GET /testitemss/api/debug
Get debug information (development only).

**Response:**
```json
{
    "debug": true,
    "environment": "development",
    "module_path": "modules/TestItems/",
    "auto_discovery": {
        "enabled": true,
        "discovered_by": "InitModsImproved.php",
        "route_file": "routes/Routes.php"
    },
    "cache_status": {
        "enabled": true,
        "driver": "file",
        "ttl": 3600
    }
}
```

## Integration Examples

### JavaScript/AJAX
```javascript
// Using the enhanced module JavaScript
const api = new EnhancedTestItemsModule();
api.callApi('test').then(response => {
    console.log('API Response:', response);
});
```

### PHP Integration
```php
// The module is auto-discovered, just use the routes
$response = file_get_contents(BASE_URL . '/testitemss/api');
$data = json_decode($response, true);
```

### cURL Example
```bash
curl -X GET "http://localhost/testitemss/api" \
     -H "Accept: application/json"
```

## Configuration

### Environment Variables
```properties
# Enable/disable caching for this module
ROUTE_USE_CACHE=true

# Enable debug output
ROUTE_DEBUG_OUTPUT=false

# Module-specific configuration
{strtoupper(TestItems)}_API_ENABLED=true
{strtoupper(TestItems)}_CACHE_TTL=3600
```

### Module Config
See `etc/config.php` for module-specific configuration options.

## Troubleshooting

### API Not Found
1. Check that `ROUTE_SUBMODULE_DISCOVERY=true` if this is a submodule
2. Verify `routes/Routes.php` exists and is properly formatted
3. Clear cache: set `ROUTE_USE_CACHE=false` temporarily

### Debug Information
Enable debug mode to see detailed information:
```properties
ROUTE_DEBUG_OUTPUT=true
APP_ENV=development
```

### Performance Optimization
For production environments:
```properties
ROUTE_USE_CACHE=true
APP_ENV=production
ROUTE_DEBUG_OUTPUT=false
```

## Support
This enhanced module is designed to work seamlessly with upMVC v2.0 and InitModsImproved.php auto-discovery.