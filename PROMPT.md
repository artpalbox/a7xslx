# A7XSLX Excel File Manager - Development Prompt Documentation

## Project Overview
WordPress plugin wrapper for a standalone PHP micro application to handle Excel and CSV files (.xlsx, .xlsm, .xls, .csv) using PhpSpreadsheet and Box/Spout libraries.

## Key Requirements
- **File Management**: Upload, list, edit (rename), delete, view (top 50 rows), download Excel/CSV files
- **Database**: Table `wp_a7xslx_files` with columns: id (auto_inc), filename, filepath, uploaded_at
- **Admin Integration**: Menu page under admin, toolbar button linking to app
- **API Endpoints**:
  - `?action=api&endpoint=info&file_id=X` - Returns sheet info as JSON
  - `?action=api&endpoint=data&file_id=X&sheet=NAME&limit=N&offset=M&columns=A,B,C` - Returns data as JSON
  - `?action=api&endpoint=allfiles` - Returns all files as JSON
- **UI Features**:
  - File list with Tabulator.js table, AJAX loaded
  - File preview with sheet navigation, Tabulator.js table, AJAX loaded
  - Loading indicators (progress bars) during AJAX requests
  - Sheet switching without page reload

## Technical Implementation
- **Architecture**: PSR-4 autoloader, namespaced classes in `src/` directory
- **Libraries**:
  - PhpOffice/PhpSpreadsheet (^5.3) for Excel info and .xlsm support
  - Box/Spout (^4.0) for fast CSV/Excel data reading
- **Frontend**: Tabulator.js for data tables, jQuery for AJAX
- **Security**: WordPress permissions, input sanitization, file type validation
- **Performance**: Efficient reading with limits, AJAX data loading, no full file processing on page load

## Development Notes
- Fixed path issues with `__DIR__ . '/../../../wp-load.php'`
- Used null coalescing operator `??` for parameters
- Separated concerns with individual class methods
- Optimized file reading with read filters and range queries
- Fallback to PhpOffice for unsupported file types (.xlsm)
- Dynamic sheet navigation built from AJAX responses

## File Structure
```
a7xslx.php (main plugin)
app/
├── composer.json
├── vendor/ (dependencies)
├── index.php (file list)
├── upload.php (upload form)
├── edit.php (edit file)
├── view.php (preview file)
├── download.php (download file)
└── api.php (API endpoints)
src/ (classes)
├── App.php (facade/router)
├── API.php (API handlers)
├── FileList.php
├── FileUpload.php
├── FileEdit.php
├── FileView.php
├── FileDownload.php
└── LimitRowsReadFilter.php
```

## Important Fixes Applied
- Corrected require paths for WordPress integration
- Resolved Spout compatibility issues with custom column helpers
- Implemented efficient data loading with library fallbacks
- Added loading indicators and dynamic UI updates
- Ensured PSR-4 compliance and clean code structure

This documentation captures the evolution of requirements and technical decisions made during development.