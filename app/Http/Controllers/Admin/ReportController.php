<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ProjectsExport;
use App\Exports\TasksExport;
use App\Exports\UsersExport;
use App\Exports\ComprehensiveProjectReport;
use App\Exports\TaskDetailsReport;
use App\Exports\FullReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display reports page
     */
    public function index()
    {
        return view('admin.reports.export');
    }

    /**
     * Export projects report
     */
    public function exportProjects(Request $request)
    {
        $status = $request->query('status');
        $filename = 'projects_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ProjectsExport($status), $filename);
    }

    /**
     * Export tasks report
     */
    public function exportTasks(Request $request)
    {
        $projectId = $request->query('project_id');
        $status = $request->query('status');
        $filename = 'tasks_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new TasksExport($projectId, $status), $filename);
    }

    /**
     * Export users report
     */
    public function exportUsers(Request $request)
    {
        $role = $request->query('role');
        $filename = 'users_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new UsersExport($role), $filename);
    }

    /**
     * Export comprehensive report (all data in multiple sheets)
     */
    public function exportComprehensive(Request $request)
    {
        $projectId = $request->query('project_id');
        $filename = 'comprehensive_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new FullReportExport($projectId), $filename);
    }

    /**
     * Export comprehensive project report
     */
    public function exportComprehensiveProject(Request $request)
    {
        $status = $request->query('status');
        $format = $request->query('format', 'xlsx');
        $filename = 'project_comprehensive_' . date('Y-m-d_His');

        $export = new ComprehensiveProjectReport($status);

        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Export task details report
     */
    public function exportTaskDetails(Request $request)
    {
        $projectId = $request->query('project_id');
        $status = $request->query('status');
        $format = $request->query('format', 'xlsx');
        $filename = 'task_details_' . date('Y-m-d_His');

        $export = new TaskDetailsReport($projectId, $status);

        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }
}
