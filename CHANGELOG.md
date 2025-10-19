# Changelog

All notable changes to YOYAKU API Connector will be documented in this file.

## [1.1.0] - 2025-10-19

### Changed
- ✅ **BREAKING CHANGE**: Migrated shelf quantity field from `yid_total_shelf` to `_yyd_shelf_count`
- ✅ Updated Product Stock Data endpoint to use new unified field name
- ✅ Aligns with YYD B2B inventory field standardization

### Migration Notes
- API response structure unchanged (still returns `shelf_quantity` key)
- Backend now reads from `_yyd_shelf_count` custom field
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
- ✅ Returns: images, stock, custom fields (_depot_vente, _initial_quantity, _yyd_shelf_count, _total_preorders)

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
