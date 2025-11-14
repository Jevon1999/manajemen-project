# Admin Report System Documentation

## Overview
Comprehensive reporting system for admin with per-project, monthly, and yearly reports with CSV export functionality.

## Features Implemented

### 1. Admin Sidebar Menu Enhancement
**File**: `resources/views/components/sidebar/admin.blade.php`

Added expandable "Reports" menu with submenu items:
- 📊 General Report - Default report with custom filters
- 📁 Per Project - Detailed breakdown of each project
- 📅 Monthly Report - Last 12 months performance data
- 📆 Yearly Report - Last 5 years trends and statistics

**Implementation**:
- Uses Alpine.js `x-data` for dropdown state management
- Auto-expands when on report routes (`Request::routeIs('admin.reports.*')`)
- Smooth transitions with Tailwind CSS
- Active state highlighting for current report type

### 2. Enhanced Report Controller
**File**: `app/Http/Controllers/ReportController.php`

#### New Methods Added:

##### `getMonthlyData()`
- Returns last 12 months of data
- Aggregates: total tasks, completed tasks, completion rate, work hours
- Data format:
  ```php
  [
      'month' => 'Jan 2025',
      'month_number' => '2025-01',
      'total_tasks' => 50,
      'completed_tasks' => 40,
      'completion_rate' => 80.0,
      'work_hours' => 320.5
  ]
  ```

##### `getYearlyData()`
- Returns last 5 years of data
- Includes project and task statistics
- Data format:
  ```php
  [
      'year' => 2024,
      'total_projects' => 10,
      'completed_projects' => 8,
      'total_tasks' => 500,
      'completed_tasks' => 450,
      'completion_rate' => 90.0,
      'work_hours' => 4000.0
  ]
  ```

##### `getProjectData()`
- Returns statistics for all projects
- Includes: tasks, completion rate, team members, overdue tasks
- Data format:
  ```php
  [
      'project_id' => 1,
      'project_name' => 'Project Alpha',
      'leader_name' => 'John Doe',
      'status' => 'active',
      'total_tasks' => 50,
      'completed_tasks' => 40,
      'in_progress_tasks' => 8,
      'overdue_tasks' => 2,
      'completion_rate' => 80.0,
      'work_hours' => 320.5,
      'team_members' => 5,
      'deadline' => '31 Dec 2025',
      'completed_at' => 'Ongoing'
  ]
  ```

##### `generateMonthly(Request $request)`
- Generates CSV report for specific month
- **Route**: `POST /admin/reports/monthly`
- **Parameters**: `month` (format: Y-m, e.g., "2025-01")
- **Returns**: CSV download
- **Filename**: `monthly_report_YYYY-MM.csv`

##### `generateYearly(Request $request)`
- Generates CSV report for specific year
- **Route**: `POST /admin/reports/yearly`
- **Parameters**: `year` (integer, e.g., 2024)
- **Returns**: CSV download
- **Filename**: `yearly_report_YYYY.csv`

##### `generateProject(Request $request)`
- Generates CSV report for specific project
- **Route**: `POST /admin/reports/project`
- **Parameters**: 
  - `project_id` (required)
  - `date_from` (optional, default: 1 year ago)
  - `date_to` (optional, default: today)
- **Returns**: CSV download
- **Filename**: `project_report_ProjectName_YYYY-MM-DD.csv`

### 3. Enhanced Report Views
**File**: `resources/views/admin/reports/index.blade.php`

#### Dynamic Content Based on Report Type

**General Report** (`type=null`):
- Shows default filter form
- Date range, project, user, status filters
- Recent reports list

**Monthly Report** (`type=monthly`):
- Table with last 12 months data
- Columns: Month, Total Tasks, Completed, Completion Rate, Work Hours
- Download CSV button for each month
- Color-coded completion rates:
  - Green (≥75%)
  - Yellow (≥50%)
  - Red (<50%)

**Yearly Report** (`type=yearly`):
- Table with last 5 years data
- Columns: Year, Projects, Completed Projects, Tasks, Completion Rate, Work Hours
- Download CSV button for each year
- Shows both project and task statistics

**Per-Project Report** (`type=project`):
- Table with all projects
- Columns: Project, Leader, Status, Tasks, Completed, Overdue, Rate, Hours, Team
- Download CSV button for each project
- Highlights overdue tasks in red badge

### 4. New Routes
**File**: `routes/web.php`

```php
// Inside role.admin middleware group
Route::get('/admin/reports', [ReportController::class, 'index'])
    ->name('admin.reports.index');
Route::post('/admin/reports/generate', [ReportController::class, 'generate'])
    ->name('admin.reports.generate');
Route::post('/admin/reports/monthly', [ReportController::class, 'generateMonthly'])
    ->name('admin.reports.monthly');
Route::post('/admin/reports/yearly', [ReportController::class, 'generateYearly'])
    ->name('admin.reports.yearly');
Route::post('/admin/reports/project', [ReportController::class, 'generateProject'])
    ->name('admin.reports.project');
```

## Usage Guide

### Accessing Reports

1. **Via Sidebar**:
   - Click "Reports" in admin sidebar
   - Select report type from submenu

2. **Direct URLs**:
   - General: `/admin/reports`
   - Monthly: `/admin/reports?type=monthly`
   - Yearly: `/admin/reports?type=yearly`
   - Per Project: `/admin/reports?type=project`

### Generating Reports

#### Monthly Report:
1. Navigate to Monthly Report page
2. Find desired month in table
3. Click "CSV" button to download

#### Yearly Report:
1. Navigate to Yearly Report page
2. Find desired year in table
3. Click "CSV" button to download

#### Per-Project Report:
1. Navigate to Per Project page
2. Find desired project in table
3. Click "CSV" button to download specific project report

#### Custom Report:
1. Navigate to General Report page
2. Fill in filter form:
   - Date range (required)
   - Project (optional)
   - User (optional)
   - Status (optional)
3. Click "Generate & Download CSV"

## CSV Report Structure

All reports include these sections:

### 1. Project Summary
- Project information
- Total tasks count
- Work hours
- Completion status

### 2. Task Summary
- Individual task details
- Assignment information
- Status and priority
- Time tracking

### 3. Work Time Tracking
- Work session details
- Start/stop times
- Duration per session
- Daily breakdown

### 4. User Performance
- User statistics
- Completed tasks count
- Total work hours
- Average time per task

## Report Logging

All generated reports are logged to `report_logs` table:
- `user_id`: Admin who generated report
- `report_type`: Type of report (combined, monthly, yearly, project)
- `filters`: JSON of applied filters
- `file_path`: Storage path of report file
- `generated_at`: Timestamp

View recent reports in the "Recent Reports" section on the main report page.

## Data Aggregation

### Monthly Aggregation
- Groups by calendar month
- Uses `created_at` for tasks
- Uses `work_date` for work sessions
- Calculates completion rate: (completed / total) * 100

### Yearly Aggregation
- Groups by calendar year
- Includes project lifecycle data
- Sums all work hours for the year
- Tracks both project and task completion

### Per-Project Aggregation
- Filters by specific project ID
- Counts team members from `project_members` table
- Identifies overdue tasks: status ≠ done AND deadline < now
- Real-time calculation of statistics

## Color Coding

### Completion Rates
- **Green (bg-success)**: ≥75% - Excellent performance
- **Yellow (bg-warning)**: 50-74% - Good performance
- **Red (bg-danger)**: <50% - Needs attention

### Project Status
- **Green (bg-success)**: Active projects
- **Blue (bg-primary)**: Completed projects
- **Gray (bg-secondary)**: Archived/inactive

## Security

- All report endpoints require admin authentication
- Role middleware: `role.admin`
- CSRF protection on all POST requests
- Input validation on all parameters

## Dependencies

- Laravel 10+
- Carbon (date manipulation)
- Alpine.js (sidebar dropdown)
- Bootstrap 5 (styling)
- Font Awesome (icons)

## Database Tables Used

- `projects` - Project information
- `tasks` - Task details
- `work_sessions` - Time tracking
- `project_members` - Team membership
- `report_logs` - Report generation history
- `users` - User information

## Future Enhancements

Potential improvements:
- [ ] Chart.js integration for visual reports
- [ ] PDF export option
- [ ] Scheduled report generation
- [ ] Email report delivery
- [ ] Custom report templates
- [ ] Comparison between periods
- [ ] Export to Excel format
- [ ] Report filtering by task priority
- [ ] Team performance analytics

## Testing

To test the new features:

1. Login as admin
2. Click Reports in sidebar - verify submenu opens
3. Test each report type:
   - Monthly: Check last 12 months display
   - Yearly: Check last 5 years display
   - Per Project: Check all projects listed
4. Download CSV for each report type
5. Verify CSV content matches database data
6. Check Recent Reports section updates

## Troubleshooting

### "No data" showing in tables
- Check if data exists for the time period
- Verify work_sessions table has records
- Check project_members table is populated

### CSV download fails
- Verify storage/reports directory is writable
- Check PHP memory limit for large datasets
- Verify all required columns exist in database

### Sidebar menu not expanding
- Clear browser cache
- Verify Alpine.js is loaded
- Check browser console for JavaScript errors

## API Endpoints Summary

| Endpoint | Method | Purpose | Parameters |
|----------|--------|---------|------------|
| `/admin/reports` | GET | Display report page | `type` (optional) |
| `/admin/reports/generate` | POST | General report | date_from, date_to, project_id, user_id, status |
| `/admin/reports/monthly` | POST | Monthly report | month (Y-m format) |
| `/admin/reports/yearly` | POST | Yearly report | year (integer) |
| `/admin/reports/project` | POST | Project report | project_id, date_from, date_to |

## Notes

- All times are in system timezone
- Work hours rounded to 2 decimal places
- Completion rates rounded to 1 decimal place
- CSV uses UTF-8 encoding with BOM
- Reports sorted by most recent first

---

**Last Updated**: January 2025
**Version**: 1.0.0
**Author**: Project Management System Team
