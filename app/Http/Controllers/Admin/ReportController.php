<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ProjectsExport;
use App\Exports\TasksExport;
use App\Exports\UsersExport;
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
    public function exportComprehensive()
    {
        $filename = 'comprehensive_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            public function sheets(): array
            {
                return [
                    new ProjectsExport(),
                    new TasksExport(),
                    new UsersExport(),
                ];
            }
        }, $filename);
    }
}
