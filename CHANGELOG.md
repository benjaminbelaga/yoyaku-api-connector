# Changelog

All notable changes to YOYAKU API Connector will be documented in this file.

## [1.4.0] - 2025-10-23

### Changed
- ✅ **BREAKING CHANGE**: Standardized shelf field name from `_yyd_shelf_count` to `_yyd_total_shelf`
- ✅ Aligns with ecosystem-wide custom field naming convention across YSC, YIO, and theme documentation
- ✅ Semantic correction: `_yyd_total_shelf` accurately represents EUR total value (not a count)

### Migration Notes
- API response structure unchanged (still returns `shelf_quantity` key)
- Backend now reads from `_yyd_total_shelf` custom field
- Database migration recommended: rename `_yyd_shelf_count` → `_yyd_total_shelf` if data exists
- No changes required in Google Apps Script consumers (API contract maintained)

### Technical Details
- Updated `class-base-endpoint.php:34` - Changed meta key in default query array
- Updated `class-product-stock-endpoint.php:141` - Changed custom field reference
- Maintains backward compatibility at API response level

### Naming Consistency
- ✅ `_total_preorders` (total preorders count)
- ✅ `_yyd_total_shelf` (total shelf value EUR) - NOW CONSISTENT
- ✅ `_units_on_shelf` (physical units count)

## [1.3.0] - 2025-10-19

### Performance 🚀
- ✅ **MAJOR OPTIMIZATION**: Single mega-query architecture
- ✅ **89% query reduction**: 9 queries → 1 query per product
- ✅ New `get_complete_product_data_by_sku()` method in base class
- ✅ Single JOIN query retrieves: post data + all meta fields at once

### Technical Details
- **Before**: SKU lookup → basic data → stock (2 queries) → image → 4× custom fields = 9 queries
- **After**: Single optimized query with CASE statements and GROUP BY = 1 query
- **Impact**: 100 products = 900 queries → 100 queries (89% reduction)
- **Response time**: ~10-20ms per product (down from ~50-100ms)

### Benefits
- Faster API responses for Google Sheets integration
- Reduced database load on production servers
- Better scalability for batch operations
- Lower memory footprint

## [1.2.0] - 2025-10-19

### Added
- ✅ **Publication status tracking**: New `is_online` boolean field in API response
- ✅ **Post status field**: Returns WordPress `post_status` (publish, draft, pending, etc.)
- ✅ **Support for non-published products**: API now returns data for all products regardless of publication status

### Changed
- ✅ Removed automatic filtering of non-published products
- ✅ API consumers can now determine publication status client-side

### Use Cases
- Google Sheets can display "online" vs "not online" with conditional formatting
- Inventory management tools can track unpublished products
- Better visibility into product lifecycle status

## [1.1.0] - 2025-10-19 (SUPERSEDED BY v1.4.0)

### Changed
- ⚠️ **SUPERSEDED**: Migrated shelf quantity field from `yid_total_shelf` to `_yyd_shelf_count`
- ⚠️ **NOTE**: This version used incorrect field name. Please upgrade to v1.4.0 which uses `_yyd_total_shelf`
- ✅ Updated Product Stock Data endpoint to use new unified field name
- ✅ Aligns with YYD B2B inventory field standardization

### Migration Notes
- API response structure unchanged (still returns `shelf_quantity` key)
- Backend reads from `_yyd_shelf_count` custom field (incorrect - fixed in v1.4.0)
- No changes required in Google Apps Script consumers

## [1.0.0] - 2025-10-18

### Added
- ✅ Initial release
- ✅ Abstract base class `YOYAKU_Base_Endpoint` with reusable methods
- ✅ Product Stock Data endpoint for `wp-import-dashboard` integration
- ✅ Single product endpoint: `GET /yoyaku/v1/product-stock-data/{SKU}`
- ✅ Batch endpoint: `POST /yoyaku/v1/product-stock-data/batch`
- ✅ Direct database queries for maximum performance
- ✅ No authentication required (public read-only endpoints)
- ✅ CORS headers for Google Apps Script access
- ✅ Returns: images, stock, custom fields (_depot_vente, _initial_quantity, _yyd_total_shelf, _total_preorders)

### Technical Details
- **Performance**: < 100ms response time (vs 1-3s for WooCommerce API)
- **Reliability**: Bypasses all WordPress/WooCommerce hooks and filters
- **Batch Support**: Up to 50 SKUs in one request
- **Architecture**: DRY design with abstract base class

### Integration
- Replaces WooCommerce REST API in `wp-import-dashboard` Google Apps Script
- 20-30x faster than standard WooCommerce API
- Simpler authentication (none required)
- Cleaner data format

---

**Author**: Benjamin Belaga
**Company**: YOYAKU SARL
**Repository**: https://github.com/benjaminbelaga/yoyaku-api-connector
