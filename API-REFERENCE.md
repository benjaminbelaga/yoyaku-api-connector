# YOYAKU API Connector - API Reference

Complete reference documentation for YOYAKU API endpoints.

**Current Version**: 1.4.2
**Base URL**: `https://www.yoyaku.io/wp-json/yoyaku/v1/`
**Authentication**: None (public read-only endpoints)
**Response Format**: JSON
**CORS**: Enabled (for Google Apps Script access)

---

## Table of Contents

- [Overview](#overview)
- [Endpoints](#endpoints)
  - [Get Product by SKU](#get-product-by-sku)
  - [Get Products Batch](#get-products-batch)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Performance](#performance)
- [Examples](#examples)
- [Changelog](#changelog)

---

## Overview

YOYAKU API Connector provides ultra-fast REST endpoints for accessing product stock data directly from the database. It bypasses WordPress/WooCommerce hooks for maximum performance.

### Key Features

- ⚡ **Ultra-Fast**: Single optimized query per product (89% faster than WooCommerce API)
- 🔓 **No Authentication**: Public read-only endpoints (no OAuth required)
- 📦 **Batch Support**: Process up to 50 SKUs in one request
- 🎵 **Taxonomy Support**: Includes custom taxonomies (distributor_music)
- 🌍 **CORS Enabled**: Direct access from Google Apps Script
- 💾 **Cache Headers**: Proper cache-control for Cloudflare CDN

### Use Cases

- Google Sheets stock management dashboard
- Inventory synchronization systems
- External monitoring tools
- Automated reporting systems

---

## Endpoints

### Get Product by SKU

Retrieve complete stock data for a single product by its SKU.

#### Request

```http
GET /yoyaku/v1/product-stock-data/{SKU}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `SKU` | string | ✅ | Product SKU (case-insensitive, automatically converted to uppercase) |

**Example:**
```bash
curl https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/YOYAKU012
```

#### Response (Success - 200)

```json
{
  "sku": "YOYAKU012",
  "product_id": 626423,
  "title": "Élan",
  "found": true,
  "is_online": true,
  "post_status": "publish",
  "image_url": "https://yoyaku.io/wp-content/uploads/2025/10/YOYAKU012_1_600.jpg",
  "stock_quantity": -67,
  "stock_status": "onbackorder",
  "depot_vente": "no",
  "initial_quantity": "0",
  "shelf_quantity": "24",
  "total_preorders": "28",
  "distributor_music": "yydistribution"
}
```

#### Response (Not Found - 404)

```json
{
  "code": "not_found",
  "message": "Product not found",
  "data": {
    "status": 404
  }
}
```

---

### Get Products Batch

Process multiple products in a single request (up to 50 SKUs).

#### Request

```http
POST /yoyaku/v1/product-stock-data/batch
Content-Type: application/json
```

**Body:**

```json
{
  "skus": ["YOYAKU012", "DWLD007", "SKU003"]
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `skus` | array | ✅ | Array of SKU strings (1-50 items) |

**Example:**
```bash
curl -X POST https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/batch \
  -H "Content-Type: application/json" \
  -d '{"skus": ["YOYAKU012", "DWLD007"]}'
```

#### Response (Success - 200)

```json
[
  {
    "sku": "YOYAKU012",
    "product_id": 626423,
    "title": "Élan",
    "found": true,
    "is_online": true,
    "post_status": "publish",
    "image_url": "https://yoyaku.io/wp-content/uploads/2025/10/YOYAKU012_1_600.jpg",
    "stock_quantity": -67,
    "stock_status": "onbackorder",
    "depot_vente": "no",
    "initial_quantity": "0",
    "shelf_quantity": "24",
    "total_preorders": "28",
    "distributor_music": "yydistribution"
  },
  {
    "sku": "DWLD007",
    "product_id": 789456,
    "title": "Product Title",
    "found": true,
    "is_online": true,
    "post_status": "publish",
    "image_url": "https://yoyaku.io/wp-content/uploads/...",
    "stock_quantity": 12,
    "stock_status": "instock",
    "depot_vente": "yes",
    "initial_quantity": "10",
    "shelf_quantity": "5",
    "total_preorders": "0",
    "distributor_music": "clone"
  },
  {
    "sku": "INVALID_SKU",
    "error": "Product not found",
    "found": false
  }
]
```

---

## Response Format

### Product Object

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `sku` | string | Product SKU (uppercase) | `"YOYAKU012"` |
| `product_id` | integer | WordPress product ID | `626423` |
| `title` | string | Product title | `"Élan"` |
| `found` | boolean | Product found in database | `true` |
| `is_online` | boolean | Product is published | `true` |
| `post_status` | string | WordPress post status | `"publish"`, `"draft"`, `"pending"` |
| `image_url` | string | Product thumbnail URL | `"https://..."` |
| `stock_quantity` | integer | Current stock quantity (can be negative) | `-67`, `12` |
| `stock_status` | string | WooCommerce stock status | `"instock"`, `"outofstock"`, `"onbackorder"` |
| `depot_vente` | string | Depot vente status | `"yes"`, `"no"` |
| `initial_quantity` | string | Initial quantity origin | `"0"`, `"10"` |
| `shelf_quantity` | string | **Quantity in units** on shelf (_total_shelves) | `"24"` |
| `total_preorders` | string | Total preorders count | `"28"` |
| `distributor_music` | string | Distributor taxonomy term name | `"yydistribution"`, `"clone"`, `""` |

### Field Details

#### `shelf_quantity` ⚠️ Important
- **Source**: `_total_shelves` custom field
- **Type**: Quantity in units (not EUR amount)
- **Purpose**: Physical count of items on shelf
- **Note**: Do NOT confuse with `_yyd_total_shelf` (which is EUR amount)

#### `distributor_music` 🆕 v1.4.2
- **Source**: `distributormusic` taxonomy
- **Returns**: First term name as string
- **Example Values**: `"yydistribution"`, `"clone"`, `""`
- **Empty String**: If no taxonomy term assigned

#### `stock_quantity`
- **Can be negative**: Backorders scenario
- **Protection**: Frontend should use `Math.max(0, stock_quantity)` if negatives not allowed

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| `200` | OK | Request successful |
| `404` | Not Found | Product SKU does not exist |
| `400` | Bad Request | Invalid parameters (e.g., empty SKU array) |
| `500` | Server Error | Database or internal error |

### Error Response Format

```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 404
  }
}
```

### Common Errors

**Product Not Found (Single Endpoint):**
```json
{
  "code": "not_found",
  "message": "Product not found",
  "data": {
    "status": 404
  }
}
```

**Product Not Found (Batch Endpoint):**
```json
{
  "sku": "INVALID_SKU",
  "error": "Product not found",
  "found": false
}
```

**Invalid Batch Size:**
```json
{
  "code": "rest_invalid_param",
  "message": "Invalid parameter(s): skus",
  "data": {
    "status": 400
  }
}
```

---

## Performance

### Query Optimization

**v1.3.0+ Single Mega-Query Architecture:**
- **Before**: 9 queries per product (SKU lookup, post data, stock, image, 4× custom fields)
- **After**: 1 optimized query per product (89% reduction!)
- **Impact**: 100 products = 900 queries → 100 queries

**Response Time:**
- **Single Product**: ~10-20ms
- **Batch (10 products)**: ~100-200ms
- **vs WooCommerce API**: 10-20x faster

### Rate Limiting

**No Hard Limits** (currently), but recommended:
- **Single Requests**: Unlimited
- **Batch Requests**: Max 50 SKUs per request
- **Best Practice**: Add 100-200ms delay between batch requests in loops

### Cache Headers

```http
Access-Control-Allow-Origin: *
Cache-Control: no-cache, must-revalidate, max-age=0
```

**Cloudflare Cache:** Edge cache enabled (purge required after plugin updates)

---

## Examples

### Google Apps Script Example

```javascript
/**
 * Fetch product data from YOYAKU API
 */
function fetchProductData(sku) {
  const API_BASE = 'https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data';
  const url = `${API_BASE}/${encodeURIComponent(sku)}`;

  try {
    const response = UrlFetchApp.fetch(url, {
      method: 'get',
      headers: {
        'Content-Type': 'application/json'
      },
      muteHttpExceptions: true
    });

    const product = JSON.parse(response.getContentText());

    if (!product.found) {
      Logger.log(`Product ${sku} not found`);
      return null;
    }

    return {
      sku: product.sku,
      title: product.title,
      stockQuantity: Math.max(0, product.stock_quantity), // Negative protection
      stockStatus: product.stock_status,
      distributorMusic: product.distributor_music,
      imageUrl: product.image_url,
      shelfQuantity: parseFloat(product.shelf_quantity) || 0,
      totalPreorders: parseFloat(product.total_preorders) || 0
    };

  } catch (error) {
    Logger.log('Error fetching product: ' + error.message);
    return null;
  }
}

/**
 * Fetch multiple products in batch
 */
function fetchProductsBatch(skus) {
  const API_BASE = 'https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data';
  const url = `${API_BASE}/batch`;

  try {
    const response = UrlFetchApp.fetch(url, {
      method: 'post',
      headers: {
        'Content-Type': 'application/json'
      },
      payload: JSON.stringify({ skus: skus }),
      muteHttpExceptions: true
    });

    return JSON.parse(response.getContentText());

  } catch (error) {
    Logger.log('Error fetching batch: ' + error.message);
    return [];
  }
}

// Usage
const product = fetchProductData('YOYAKU012');
Logger.log(product.distributorMusic); // "yydistribution"

const batch = fetchProductsBatch(['YOYAKU012', 'DWLD007']);
Logger.log(batch.length); // 2
```

### cURL Examples

**Single Product:**
```bash
# Basic request
curl https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/YOYAKU012

# With jq formatting
curl -s https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/YOYAKU012 | jq '.'

# Extract specific field
curl -s https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/YOYAKU012 | jq '.distributor_music'
# Output: "yydistribution"
```

**Batch Request:**
```bash
curl -X POST https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/batch \
  -H "Content-Type: application/json" \
  -d '{"skus": ["YOYAKU012", "DWLD007", "SKU003"]}' | jq '.'
```

### JavaScript/Fetch Example

```javascript
// Single product
async function getProduct(sku) {
  const response = await fetch(
    `https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/${sku}`
  );
  return await response.json();
}

// Batch
async function getProductsBatch(skus) {
  const response = await fetch(
    'https://www.yoyaku.io/wp-json/yoyaku/v1/product-stock-data/batch',
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ skus })
    }
  );
  return await response.json();
}

// Usage
const product = await getProduct('YOYAKU012');
console.log(product.distributor_music); // "yydistribution"

const batch = await getProductsBatch(['YOYAKU012', 'DWLD007']);
console.log(batch[0].distributor_music); // "yydistribution"
console.log(batch[1].distributor_music); // "clone"
```

---

## Changelog

### v1.4.2 - 2025-10-23 🎵
- ✅ Added `distributor_music` field to API response
- ✅ Returns first taxonomy term name from `distributormusic` taxonomy
- ✅ Enables Google Sheets to auto-fetch distributor information
- Example: `"distributor_music": "yydistribution"`

### v1.4.1 - 2025-10-23 🔧
- ✅ **CRITICAL FIX**: Changed `shelf_quantity` source from `_yyd_total_shelf` (EUR amount) to `_total_shelves` (quantity in units)
- ✅ Semantic correction: `_total_shelves` represents physical quantity on shelf
- ✅ Database verification: 503 rows on YOYAKU.IO with `_total_shelves` data

### v1.4.0 - 2025-10-23
- ✅ Standardized shelf field name to `_yyd_total_shelf` (ecosystem-wide naming)
- Note: Superseded by v1.4.1 which uses correct `_total_shelves` field

### v1.3.0 - 2025-10-19 ⚡
- ✅ **MAJOR OPTIMIZATION**: Single mega-query architecture
- ✅ **89% query reduction**: 9 queries → 1 query per product
- ✅ New `get_complete_product_data_by_sku()` method in base class
- ✅ Response time: ~10-20ms per product (down from ~50-100ms)

### v1.2.0 - 2025-10-19 📊
- ✅ **Publication status tracking**: New `is_online` boolean field
- ✅ **Post status field**: Returns WordPress `post_status`
- ✅ Support for non-published products (draft, pending, etc.)

### v1.1.0 - 2025-10-19
- ⚠️ **SUPERSEDED**: Migrated to incorrect field name
- Upgrade to v1.4.1+ recommended

### v1.0.0 - 2025-10-18 🚀
- ✅ Initial release
- ✅ Single product endpoint
- ✅ Batch endpoint (up to 50 SKUs)
- ✅ Direct database queries for maximum performance
- ✅ No authentication required
- ✅ CORS headers for Google Apps Script access

---

## Support

**Documentation**: `/Users/yoyaku/repos/yoyaku-api-connector/README.md`
**Changelog**: `/Users/yoyaku/repos/yoyaku-api-connector/CHANGELOG.md`
**GitHub**: `https://github.com/benjaminbelaga/yoyaku-api-connector`
**Author**: Benjamin Belaga (ben@yoyaku.fr)
**Company**: YOYAKU SARL

---

**Last Updated**: 2025-10-23
**API Version**: 1.4.2
