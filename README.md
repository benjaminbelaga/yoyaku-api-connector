# YOYAKU API Connector

**Centralized REST API endpoints for Google Apps Scripts integration**

Ultra-fast custom WordPress plugin that bypasses WooCommerce API and uses direct database queries for maximum performance.

## 🚀 Features

- ✅ **No Authentication Required** - Public endpoints for trusted Google Apps Scripts
- ✅ **Ultra Fast** - Direct database queries, < 100ms response time
- ✅ **Batch Support** - Process up to 50 SKUs in one request
- ✅ **DRY Architecture** - Single source of truth for all endpoints
- ✅ **Extensible** - Easy to add new endpoints

## 📦 Endpoints

### Product Stock Data

**For:** `wp-import-dashboard` Google Apps Script

**Single Product:**
```
GET /wp-json/yoyaku/v1/product-stock-data/{SKU}
```

**Batch (recommended):**
```
POST /wp-json/yoyaku/v1/product-stock-data/batch
Content-Type: application/json

{
  "skus": ["GRN003", "D2E002", "OYSTER72"]
}
```

**Response Format:**
```json
{
  "sku": "GRN003",
  "product_id": 12345,
  "title": "Product Title",
  "found": true,
  "image_url": "https://yoyaku.io/wp-content/uploads/...",
  "stock_quantity": 10,
  "stock_status": "instock",
  "depot_vente": "no",
  "initial_quantity": "20",
  "shelf_quantity": "",
  "total_preorders": "5"
}
```

## 🏗️ Architecture

```
yoyaku-api-connector/
├── yoyaku-api-connector.php              # Main plugin file
├── includes/
│   ├── class-base-endpoint.php           # Abstract base class (DRY)
│   ├── class-product-stock-endpoint.php  # Stock data endpoint
│   └── class-xxx-endpoint.php            # Future endpoints
└── docs/
    └── API-DOCUMENTATION.md
```

## 📥 Installation

### Method 1: Git Clone (Recommended)
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/benjaminbelaga/yoyaku-api-connector.git
```

### Method 2: Download ZIP
1. Download from GitHub
2. Extract to `wp-content/plugins/yoyaku-api-connector/`
3. Activate in WordPress admin

### Method 3: SSH Deploy
```bash
# On YOYAKU.IO server
cd /home/master/applications/jfnkmjmfer/public_html/wp-content/plugins/
git clone https://github.com/benjaminbelaga/yoyaku-api-connector.git
wp plugin activate yoyaku-api-connector
```

## 🔧 Usage in Google Apps Script

### Before (WooCommerce API - Slow)
```javascript
const API_BASE = 'https://www.yoyaku.io/wp-json/wc/v3';
const API_KEY = 'ck_...';
const API_SECRET = 'cs_...';
// Authentication required
// Slow response
// Complex meta_data extraction
```

### After (Custom Endpoint - Fast)
```javascript
const API_BASE = 'https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data';
// No authentication needed
// Ultra fast < 100ms
// Clean JSON response
```

## 🎯 Benefits

| Aspect | WooCommerce API | YOYAKU Custom API | Improvement |
|--------|----------------|-------------------|-------------|
| **Speed** | 1-3 seconds | < 100ms | **20-30x faster** |
| **Authentication** | Required | None | Simpler |
| **Data Format** | Complex nested | Clean flat | Easier to use |
| **Batch Support** | Limited | 50 SKUs | Better |
| **Reliability** | Hooks/filters | Direct SQL | More reliable |

## 📚 Documentation

- [API Documentation](docs/API-DOCUMENTATION.md)
- [Examples](docs/EXAMPLES.md)
- [Changelog](CHANGELOG.md)

## 🔐 Security

- **Public Endpoints**: Only read-only operations
- **No Write Access**: Cannot modify data
- **CORS Enabled**: For Google Apps Script access
- **Rate Limiting**: Consider Cloudflare if needed

## 🛠️ Development

### Adding a New Endpoint

1. Create new class extending `YOYAKU_Base_Endpoint`
2. Implement `register_routes()` method
3. Add initialization in main plugin file

Example:
```php
class YOYAKU_New_Endpoint extends YOYAKU_Base_Endpoint {
    public function register_routes() {
        register_rest_route('yoyaku/v1', '/new-endpoint/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_data'),
            'permission_callback' => '__return_true'
        ));
    }
}
```

## 📊 Performance

- **Direct Database Queries**: No WordPress/WooCommerce overhead
- **No Hooks/Filters**: Clean data extraction
- **Optimized SQL**: Single query per product
- **Batch Processing**: Minimal HTTP overhead

## 🚀 Deployment

### YOYAKU.IO Production
```bash
ssh yoyaku-cloudways
cd /home/master/applications/jfnkmjmfer/public_html/wp-content/plugins/
git clone https://github.com/benjaminbelaga/yoyaku-api-connector.git
wp plugin activate yoyaku-api-connector
```

### YYD.FR Production
```bash
ssh yoyaku-cloudways
cd /home/master/applications/akrjekfvzk/public_html/wp-content/plugins/
git clone https://github.com/benjaminbelaga/yoyaku-api-connector.git
wp plugin activate yoyaku-api-connector
```

## 🧪 Testing

Test endpoint:
```bash
curl https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/GRN003
```

## 📝 License

GPL-2.0+

## 👤 Author

**Benjamin Belaga**
- Email: ben@yoyaku.io
- Company: YOYAKU SARL
- Website: https://yoyaku.io

## 🎯 Version

**1.0.0** - Initial release with Product Stock Data endpoint

---

**Built with ❤️ for YOYAKU operations**
