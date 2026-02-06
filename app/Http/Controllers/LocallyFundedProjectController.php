<?php

namespace App\Http\Controllers;

use App\Models\FundUtilizationReport;
use App\Models\LocallyFundedProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocallyFundedProjectController extends Controller
{
    /**
     * Display a listing of locally funded projects
     */
    public function index()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $user = Auth::user();
        $agency = strtoupper(trim((string) $user->agency));
        $province = trim((string) $user->province);
        $office = trim((string) $user->office);
        $region = trim((string) $user->region);
        $provinceLower = strtolower($province);
        $officeLower = strtolower($office);
        $regionLower = strtolower($region);

        // Build query with role-based filtering
        $query = LocallyFundedProject::query();

        // Filter based on user's agency, province, and office
        if ($agency === 'LGU') {
            // LGU users can only see projects from their specific office
            if ($office !== '') {
                if ($province !== '') {
                    $query->whereRaw('LOWER(province) = ?', [$provinceLower])
                        ->whereRaw('LOWER(office) = ?', [$officeLower]);
                } else {
                    $query->whereRaw('LOWER(office) = ?', [$officeLower]);
                }
            } elseif ($province !== '') {
                // If no office is specified for LGU, show their province
                $query->whereRaw('LOWER(province) = ?', [$provinceLower]);
            }
        } elseif ($agency === 'DILG') {
            // DILG users filtering
            if ($provinceLower === 'regional office') {
                // Regional Office users can see all projects
            } elseif ($province !== '') {
                // DILG with specific province: show all projects in that province
                $query->whereRaw('LOWER(province) = ?', [$provinceLower]);
            } elseif ($region !== '') {
                // DILG with region set (no province): show all projects in that region
                $query->whereRaw('LOWER(region) = ?', [$regionLower]);
            }
            // If neither province nor region is set, show all projects (superadmin behavior)
        }

        $projects = $query->get();

        // Get current physical status for each project
        $projectIds = $projects->pluck('id');
        $physicalStatuses = [];

        if ($projectIds->isNotEmpty()) {
            $physicalUpdates = \Illuminate\Support\Facades\DB::table('locally_funded_physical_updates')
                ->whereIn('project_id', $projectIds)
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->select('project_id', 'status_project_fou', 'status_project_ro')
                ->get()
                ->keyBy('project_id');

            foreach ($physicalUpdates as $update) {
                $physicalStatuses[$update->project_id] = [
                    'status_actual' => $update->status_project_fou,
                    'status_subaybayan' => $update->status_project_ro,
                ];
            }
        }

        return view('projects.locally-funded', compact('projects', 'physicalStatuses'));
    }

    /**
     * Display the specified locally funded project.
     */
    public function show(LocallyFundedProject $project)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $physicalUpdates = \Illuminate\Support\Facades\DB::table('locally_funded_physical_updates')
            ->leftJoin('tbusers', 'tbusers.idno', '=', 'locally_funded_physical_updates.updated_by')
            ->where('locally_funded_physical_updates.project_id', $project->id)
            ->where('locally_funded_physical_updates.year', $currentYear)
            ->select(
                'locally_funded_physical_updates.month',
                'locally_funded_physical_updates.status_project_fou',
                'locally_funded_physical_updates.status_project_ro',
                'locally_funded_physical_updates.accomplishment_pct',
                'locally_funded_physical_updates.accomplishment_pct_ro',
                'locally_funded_physical_updates.slippage',
                'locally_funded_physical_updates.slippage_ro',
                'locally_funded_physical_updates.risk_aging',
                'locally_funded_physical_updates.nc_letters',
                'locally_funded_physical_updates.status_project_fou_updated_at',
                'locally_funded_physical_updates.status_project_ro_updated_at',
                'locally_funded_physical_updates.accomplishment_pct_updated_at',
                'locally_funded_physical_updates.accomplishment_pct_ro_updated_at',
                'locally_funded_physical_updates.slippage_updated_at',
                'locally_funded_physical_updates.slippage_ro_updated_at',
                'locally_funded_physical_updates.risk_aging_updated_at',
                'locally_funded_physical_updates.nc_letters_updated_at',
                'locally_funded_physical_updates.status_project_fou_updated_by',
                'locally_funded_physical_updates.status_project_ro_updated_by',
                'locally_funded_physical_updates.accomplishment_pct_updated_by',
                'locally_funded_physical_updates.accomplishment_pct_ro_updated_by',
                'locally_funded_physical_updates.slippage_updated_by',
                'locally_funded_physical_updates.slippage_ro_updated_by',
                'locally_funded_physical_updates.risk_aging_updated_by',
                'locally_funded_physical_updates.nc_letters_updated_by'
            )
            ->get();

        $userIds = $physicalUpdates->flatMap(function ($row) {
            return [
                $row->status_project_fou_updated_by,
                $row->status_project_ro_updated_by,
                $row->accomplishment_pct_updated_by,
                $row->accomplishment_pct_ro_updated_by,
                $row->slippage_updated_by,
                $row->slippage_ro_updated_by,
                $row->risk_aging_updated_by,
                $row->nc_letters_updated_by,
            ];
        })->filter()->unique()->values();

        $usersById = $userIds->isEmpty()
            ? collect()
            : \Illuminate\Support\Facades\DB::table('tbusers')
                ->whereIn('idno', $userIds)
                ->get(['idno', 'fname', 'lname'])
                ->keyBy('idno');

        $actualCompletionUpdatedByName = null;
        if ($project->actual_date_completion_updated_by) {
            $user = \Illuminate\Support\Facades\DB::table('tbusers')
                ->where('idno', $project->actual_date_completion_updated_by)
                ->first(['fname', 'lname']);
            if ($user) {
                $actualCompletionUpdatedByName = trim($user->fname . ' ' . $user->lname);
            }
        }

        $physicalByMonth = [];
        foreach ($physicalUpdates as $row) {
            $physicalByMonth[(int) $row->month] = [
                'status_project_fou' => $row->status_project_fou,
                'status_project_ro' => $row->status_project_ro ?? null,
                'accomplishment_pct' => $row->accomplishment_pct,
                'accomplishment_pct_ro' => $row->accomplishment_pct_ro,
                'slippage' => $row->slippage,
                'slippage_ro' => $row->slippage_ro,
                'risk_aging' => $row->risk_aging,
                'nc_letters' => $row->nc_letters,
                'status_project_fou_updated_at' => $row->status_project_fou_updated_at,
                'status_project_ro_updated_at' => $row->status_project_ro_updated_at,
                'accomplishment_pct_updated_at' => $row->accomplishment_pct_updated_at,
                'accomplishment_pct_ro_updated_at' => $row->accomplishment_pct_ro_updated_at,
                'slippage_updated_at' => $row->slippage_updated_at,
                'slippage_ro_updated_at' => $row->slippage_ro_updated_at,
                'risk_aging_updated_at' => $row->risk_aging_updated_at,
                'nc_letters_updated_at' => $row->nc_letters_updated_at,
                'status_project_fou_updated_by' => $row->status_project_fou_updated_by,
                'status_project_ro_updated_by' => $row->status_project_ro_updated_by,
                'accomplishment_pct_updated_by' => $row->accomplishment_pct_updated_by,
                'accomplishment_pct_ro_updated_by' => $row->accomplishment_pct_ro_updated_by,
                'slippage_updated_by' => $row->slippage_updated_by,
                'slippage_ro_updated_by' => $row->slippage_ro_updated_by,
                'risk_aging_updated_by' => $row->risk_aging_updated_by,
                'nc_letters_updated_by' => $row->nc_letters_updated_by,
                'status_project_fou_updated_by_name' => $row->status_project_fou_updated_by && $usersById->has($row->status_project_fou_updated_by)
                    ? trim($usersById[$row->status_project_fou_updated_by]->fname . ' ' . $usersById[$row->status_project_fou_updated_by]->lname)
                    : null,
                'status_project_ro_updated_by_name' => $row->status_project_ro_updated_by && $usersById->has($row->status_project_ro_updated_by)
                    ? trim($usersById[$row->status_project_ro_updated_by]->fname . ' ' . $usersById[$row->status_project_ro_updated_by]->lname)
                    : null,
                'accomplishment_pct_updated_by_name' => $row->accomplishment_pct_updated_by && $usersById->has($row->accomplishment_pct_updated_by)
                    ? trim($usersById[$row->accomplishment_pct_updated_by]->fname . ' ' . $usersById[$row->accomplishment_pct_updated_by]->lname)
                    : null,
                'accomplishment_pct_ro_updated_by_name' => $row->accomplishment_pct_ro_updated_by && $usersById->has($row->accomplishment_pct_ro_updated_by)
                    ? trim($usersById[$row->accomplishment_pct_ro_updated_by]->fname . ' ' . $usersById[$row->accomplishment_pct_ro_updated_by]->lname)
                    : null,
                'slippage_updated_by_name' => $row->slippage_updated_by && $usersById->has($row->slippage_updated_by)
                    ? trim($usersById[$row->slippage_updated_by]->fname . ' ' . $usersById[$row->slippage_updated_by]->lname)
                    : null,
                'slippage_ro_updated_by_name' => $row->slippage_ro_updated_by && $usersById->has($row->slippage_ro_updated_by)
                    ? trim($usersById[$row->slippage_ro_updated_by]->fname . ' ' . $usersById[$row->slippage_ro_updated_by]->lname)
                    : null,
                'risk_aging_updated_by_name' => $row->risk_aging_updated_by && $usersById->has($row->risk_aging_updated_by)
                    ? trim($usersById[$row->risk_aging_updated_by]->fname . ' ' . $usersById[$row->risk_aging_updated_by]->lname)
                    : null,
                'nc_letters_updated_by_name' => $row->nc_letters_updated_by && $usersById->has($row->nc_letters_updated_by)
                    ? trim($usersById[$row->nc_letters_updated_by]->fname . ' ' . $usersById[$row->nc_letters_updated_by]->lname)
                    : null,
            ];
        }

        $currentPhysical = $physicalByMonth[$currentMonth] ?? null;

        $financialByMonth = [];
        $financialTotals = [
            'obligation' => 0,
            'disbursed_amount' => 0,
            'reverted_amount' => 0,
        ];
        $financialUpdates = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('locally_funded_financial_updates')) {
            $financialUpdates = \Illuminate\Support\Facades\DB::table('locally_funded_financial_updates')
                ->leftJoin('tbusers', 'tbusers.idno', '=', 'locally_funded_financial_updates.updated_by')
                ->where('project_id', $project->id)
                ->where('year', $currentYear)
                ->select(
                    'locally_funded_financial_updates.month',
                    'locally_funded_financial_updates.obligation',
                    'locally_funded_financial_updates.disbursed_amount',
                    'locally_funded_financial_updates.reverted_amount',
                    'locally_funded_financial_updates.utilization_rate',
                    'locally_funded_financial_updates.updated_at',
                    'locally_funded_financial_updates.updated_by',
                    'locally_funded_financial_updates.obligation_updated_at',
                    'locally_funded_financial_updates.obligation_updated_by',
                    'locally_funded_financial_updates.disbursed_amount_updated_at',
                    'locally_funded_financial_updates.disbursed_amount_updated_by',
                    'locally_funded_financial_updates.reverted_amount_updated_at',
                    'locally_funded_financial_updates.reverted_amount_updated_by',
                    'locally_funded_financial_updates.utilization_rate_updated_at',
                    'locally_funded_financial_updates.utilization_rate_updated_by',
                    'tbusers.fname',
                    'tbusers.lname'
                )
                ->get();

            foreach ($financialUpdates as $row) {
                $financialByMonth[(int) $row->month] = [
                    'obligation' => $row->obligation,
                    'disbursed_amount' => $row->disbursed_amount,
                    'reverted_amount' => $row->reverted_amount,
                    'utilization_rate' => $row->utilization_rate,
                    'updated_at' => $row->updated_at,
                    'updated_by' => $row->updated_by,
                    'updated_by_name' => $row->updated_by ? trim(($row->fname ?? '') . ' ' . ($row->lname ?? '')) : null,
                    'obligation_updated_at' => $row->obligation_updated_at,
                    'obligation_updated_by' => $row->obligation_updated_by,
                    'disbursed_amount_updated_at' => $row->disbursed_amount_updated_at,
                    'disbursed_amount_updated_by' => $row->disbursed_amount_updated_by,
                    'reverted_amount_updated_at' => $row->reverted_amount_updated_at,
                    'reverted_amount_updated_by' => $row->reverted_amount_updated_by,
                    'utilization_rate_updated_at' => $row->utilization_rate_updated_at,
                    'utilization_rate_updated_by' => $row->utilization_rate_updated_by,
                ];
            }

            foreach ($financialByMonth as $row) {
                $financialTotals['obligation'] += (float) ($row['obligation'] ?? 0);
                $financialTotals['disbursed_amount'] += (float) ($row['disbursed_amount'] ?? 0);
                $financialTotals['reverted_amount'] += (float) ($row['reverted_amount'] ?? 0);
            }
        }

        $activityLogs = [];
        $pushLog = function ($timestamp, $userId, $action, $section, $field, array $meta = []) use (&$activityLogs) {
            if (empty($timestamp)) {
                return;
            }

            try {
                $loggedAt = $timestamp instanceof \DateTimeInterface
                    ? $timestamp
                    : \Carbon\Carbon::parse($timestamp);
            } catch (\Exception $e) {
                return;
            }

            $details = [];
            if (array_key_exists('month', $meta) && $meta['month'] !== null) {
                $details[] = 'Month: ' . $meta['month'];
            }
            if (array_key_exists('value', $meta) && $meta['value'] !== null && $meta['value'] !== '') {
                $details[] = $meta['value'];
            }

            $activityLogs[] = [
                'timestamp' => $loggedAt,
                'user_id' => $userId,
                'action' => $action,
                'section' => $section,
                'field' => $field,
                'details' => count($details) ? implode(' • ', $details) : null,
            ];
        };

        $formatPhysicalValue = function ($field, $value) {
            if ($value === null || $value === '') {
                return null;
            }
            if (in_array($field, ['accomplishment_pct', 'accomplishment_pct_ro', 'slippage', 'slippage_ro'], true)) {
                return number_format((float) $value, 2) . '%';
            }
            if (is_numeric($value)) {
                return (string) $value;
            }
            return (string) $value;
        };

        $formatFinancialValue = function ($field, $value) {
            if ($value === null || $value === '') {
                return null;
            }
            if (in_array($field, ['obligation', 'disbursed_amount', 'reverted_amount'], true)) {
                return '₱ ' . number_format((float) $value, 2);
            }
            if ($field === 'utilization_rate') {
                return number_format((float) $value, 2) . '%';
            }
            if (is_numeric($value)) {
                return (string) $value;
            }
            return (string) $value;
        };

        $physicalFieldMap = [
            'status_project_fou' => 'Status (Actual)',
            'status_project_ro' => 'Status (Subaybayan)',
            'accomplishment_pct' => 'Accomplishment % (Actual)',
            'accomplishment_pct_ro' => 'Accomplishment % (Subaybayan)',
            'slippage' => 'Slippage (Actual)',
            'slippage_ro' => 'Slippage (Subaybayan)',
            'risk_aging' => 'Risk/Aging',
            'nc_letters' => 'NC Letters',
        ];

        foreach ($physicalUpdates as $row) {
            $month = $row->month ?? null;
            foreach ($physicalFieldMap as $field => $label) {
                $updatedAtField = $field . '_updated_at';
                $updatedByField = $field . '_updated_by';
                if (!empty($row->{$updatedAtField})) {
                    $pushLog(
                        $row->{$updatedAtField},
                        $row->{$updatedByField} ?? null,
                        'update',
                        'Physical',
                        $label,
                        [
                            'month' => $month,
                            'value' => $formatPhysicalValue($field, $row->{$field} ?? null),
                        ]
                    );
                }
            }
        }

        $financialFieldMap = [
            'obligation' => 'Obligation',
            'disbursed_amount' => 'Disbursed Amount',
            'reverted_amount' => 'Reverted Amount',
            'utilization_rate' => 'Utilization Rate',
        ];

        foreach ($financialUpdates as $row) {
            $month = $row->month ?? null;
            foreach ($financialFieldMap as $field => $label) {
                $updatedAtField = $field . '_updated_at';
                $updatedByField = $field . '_updated_by';
                if (!empty($row->{$updatedAtField})) {
                    $pushLog(
                        $row->{$updatedAtField},
                        $row->{$updatedByField} ?? null,
                        'update',
                        'Financial',
                        $label,
                        [
                            'month' => $month,
                            'value' => $formatFinancialValue($field, $row->{$field} ?? null),
                        ]
                    );
                }
            }
        }

        $formatProjectValue = function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('M d, Y');
            }
            if ($value === null || $value === '') {
                return null;
            }
            return (string) $value;
        };

        $projectLogFields = [
            ['field' => 'physical_remarks', 'label' => 'Physical Remarks', 'section' => 'Physical', 'action' => 'remarks', 'updated_at' => 'physical_remarks_updated_at', 'updated_by' => 'physical_remarks_updated_by'],
            ['field' => 'financial_remarks', 'label' => 'Financial Remarks', 'section' => 'Financial', 'action' => 'remarks', 'updated_at' => 'financial_remarks_updated_at', 'updated_by' => 'financial_remarks_updated_by'],
            ['field' => 'po_monitoring_date', 'label' => 'PO Monitoring Date', 'section' => 'Monitoring', 'action' => 'update', 'updated_at' => 'po_monitoring_date_updated_at', 'updated_by' => 'po_monitoring_date_updated_by'],
            ['field' => 'po_final_inspection', 'label' => 'PO Final Inspection', 'section' => 'Monitoring', 'action' => 'update', 'updated_at' => 'po_final_inspection_updated_at', 'updated_by' => 'po_final_inspection_updated_by'],
            ['field' => 'po_remarks', 'label' => 'PO Remarks', 'section' => 'Monitoring', 'action' => 'remarks', 'updated_at' => 'po_remarks_updated_at', 'updated_by' => 'po_remarks_updated_by'],
            ['field' => 'ro_monitoring_date', 'label' => 'RO Monitoring Date', 'section' => 'Monitoring', 'action' => 'update', 'updated_at' => 'ro_monitoring_date_updated_at', 'updated_by' => 'ro_monitoring_date_updated_by'],
            ['field' => 'ro_final_inspection', 'label' => 'RO Final Inspection', 'section' => 'Monitoring', 'action' => 'update', 'updated_at' => 'ro_final_inspection_updated_at', 'updated_by' => 'ro_final_inspection_updated_by'],
            ['field' => 'ro_remarks', 'label' => 'RO Remarks', 'section' => 'Monitoring', 'action' => 'remarks', 'updated_at' => 'ro_remarks_updated_at', 'updated_by' => 'ro_remarks_updated_by'],
            ['field' => 'pcr_submission_deadline', 'label' => 'PCR Submission Deadline', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'pcr_submission_deadline_updated_at', 'updated_by' => 'pcr_submission_deadline_updated_by'],
            ['field' => 'pcr_date_submitted_to_po', 'label' => 'PCR Date Submitted to PO', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'pcr_date_submitted_to_po_updated_at', 'updated_by' => 'pcr_date_submitted_to_po_updated_by'],
            ['field' => 'pcr_date_received_by_ro', 'label' => 'PCR Date Received by RO', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'pcr_date_received_by_ro_updated_at', 'updated_by' => 'pcr_date_received_by_ro_updated_by'],
            ['field' => 'pcr_remarks', 'label' => 'PCR Remarks', 'section' => 'Post Implementation', 'action' => 'remarks', 'updated_at' => 'pcr_remarks_updated_at', 'updated_by' => 'pcr_remarks_updated_by'],
            ['field' => 'rssa_report_deadline', 'label' => 'RSSA Report Deadline', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'rssa_report_deadline_updated_at', 'updated_by' => 'rssa_report_deadline_updated_by'],
            ['field' => 'rssa_submission_status', 'label' => 'RSSA Submission Status', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'rssa_submission_status_updated_at', 'updated_by' => 'rssa_submission_status_updated_by'],
            ['field' => 'rssa_date_submitted_to_po', 'label' => 'RSSA Date Submitted to PO', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'rssa_date_submitted_to_po_updated_at', 'updated_by' => 'rssa_date_submitted_to_po_updated_by'],
            ['field' => 'rssa_date_received_by_ro', 'label' => 'RSSA Date Received by RO', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'rssa_date_received_by_ro_updated_at', 'updated_by' => 'rssa_date_received_by_ro_updated_by'],
            ['field' => 'rssa_date_submitted_to_co', 'label' => 'RSSA Date Submitted to CO', 'section' => 'Post Implementation', 'action' => 'update', 'updated_at' => 'rssa_date_submitted_to_co_updated_at', 'updated_by' => 'rssa_date_submitted_to_co_updated_by'],
            ['field' => 'rssa_remarks', 'label' => 'RSSA Remarks', 'section' => 'Post Implementation', 'action' => 'remarks', 'updated_at' => 'rssa_remarks_updated_at', 'updated_by' => 'rssa_remarks_updated_by'],
        ];

        foreach ($projectLogFields as $config) {
            $updatedAtField = $config['updated_at'];
            $updatedByField = $config['updated_by'];
            $updatedAtValue = $project->{$updatedAtField} ?? null;
            if (!empty($updatedAtValue)) {
                $pushLog(
                    $updatedAtValue,
                    $project->{$updatedByField} ?? null,
                    $config['action'],
                    $config['section'],
                    $config['label'],
                    ['value' => $formatProjectValue($project->{$config['field']} ?? null)]
                );
            }
        }

        $activityLogs = collect($activityLogs)
            ->sortByDesc('timestamp')
            ->values()
            ->take(200)
            ->all();

        $logUserIds = collect($activityLogs)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $logUsers = $logUserIds->isEmpty()
            ? collect()
            : \Illuminate\Support\Facades\DB::table('tbusers')
                ->whereIn('idno', $logUserIds)
                ->get(['idno', 'fname', 'lname', 'agency'])
                ->keyBy('idno');

        foreach ($activityLogs as &$log) {
            $user = $log['user_id'] && $logUsers->has($log['user_id'])
                ? $logUsers[$log['user_id']]
                : null;
            $log['user_name'] = $user ? trim($user->fname . ' ' . $user->lname) : null;
            $log['user_agency'] = $user->agency ?? null;
        }
        unset($log);

        // Cordillera Administrative Region (CAR) provinces
        $provinces = [
            'Abra',
            'Apayao',
            'Benguet',
            'City of Baguio',
            'Ifugao',
            'Kalinga',
            'Mountain Province'
        ];

        // Province to municipalities/cities mapping
        $provinceMunicipalities = [
            'Abra' => ['Bangued', 'Boliney', 'Bucay', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Malibcong', 'Manabo', 'Peñarrubia', 'Pidcal', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin'],
            'Apayao' => ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Pudtol', 'Santa Marcela'],
            'Benguet' => ['Atok', 'Baguio City', 'Bakun', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'],
            'City of Baguio' => ['Baguio City'],
            'Ifugao' => ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Mayoyao', 'Tinoc'],
            'Kalinga' => ['Balbalan', 'Dagupagsan', 'Lubuagan', 'Mabunguran', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk City', 'Tanudan', 'Tinglayan'],
            'Mountain Province' => ['Amlang', 'Amtan', 'Bauko', 'Besao', 'Cervantes', 'Natonin', 'Paracelis', 'Sabangan', 'Sagada', 'Tadian']
        ];

        // Fund source and funding year options
        $fundSources = ['SBDP', 'FALGU', 'CMGP', 'SGLGIF', 'SAFPB'];
        $fundingYears = [2025, 2024, 2023, 2022, 2021];

        $financialAllocationTotal = (float) $project->lgsf_allocation;
        $financialBalance = $financialAllocationTotal
            - ($financialTotals['disbursed_amount'] + $financialTotals['reverted_amount']);
        $financialUtilizationRate = $financialAllocationTotal > 0
            ? (100 - (($financialBalance / $financialAllocationTotal) * 100))
            : 0;

        $remarksUserIds = collect([
            $project->physical_remarks_updated_by,
            $project->physical_remarks_encoded_by,
            $project->financial_remarks_updated_by,
            $project->financial_remarks_encoded_by,
            $project->po_monitoring_date_updated_by,
            $project->po_final_inspection_updated_by,
            $project->po_remarks_updated_by,
            $project->ro_monitoring_date_updated_by,
            $project->ro_final_inspection_updated_by,
            $project->ro_remarks_updated_by,
            $project->pcr_submission_deadline_updated_by,
            $project->pcr_date_submitted_to_po_updated_by,
            $project->pcr_date_received_by_ro_updated_by,
            $project->pcr_remarks_updated_by,
            $project->rssa_report_deadline_updated_by,
            $project->rssa_submission_status_updated_by,
            $project->rssa_date_submitted_to_po_updated_by,
            $project->rssa_date_received_by_ro_updated_by,
            $project->rssa_date_submitted_to_co_updated_by,
            $project->rssa_remarks_updated_by,
        ])->filter()->unique()->values();

        $remarksUsers = $remarksUserIds->isEmpty()
            ? collect()
            : \Illuminate\Support\Facades\DB::table('tbusers')
                ->whereIn('idno', $remarksUserIds)
                ->get(['idno', 'fname', 'lname'])
                ->keyBy('idno');

        $physicalRemarksUpdatedByName = $project->physical_remarks_updated_by && $remarksUsers->has($project->physical_remarks_updated_by)
            ? trim($remarksUsers[$project->physical_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->physical_remarks_updated_by]->lname)
            : null;
        $physicalRemarksEncodedByName = $project->physical_remarks_encoded_by && $remarksUsers->has($project->physical_remarks_encoded_by)
            ? trim($remarksUsers[$project->physical_remarks_encoded_by]->fname . ' ' . $remarksUsers[$project->physical_remarks_encoded_by]->lname)
            : null;
        $financialRemarksUpdatedByName = $project->financial_remarks_updated_by && $remarksUsers->has($project->financial_remarks_updated_by)
            ? trim($remarksUsers[$project->financial_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->financial_remarks_updated_by]->lname)
            : null;
        $financialRemarksEncodedByName = $project->financial_remarks_encoded_by && $remarksUsers->has($project->financial_remarks_encoded_by)
            ? trim($remarksUsers[$project->financial_remarks_encoded_by]->fname . ' ' . $remarksUsers[$project->financial_remarks_encoded_by]->lname)
            : null;

        // Monitoring field user names
        $poMonitoringDateUpdatedByName = $project->po_monitoring_date_updated_by && $remarksUsers->has($project->po_monitoring_date_updated_by)
            ? trim($remarksUsers[$project->po_monitoring_date_updated_by]->fname . ' ' . $remarksUsers[$project->po_monitoring_date_updated_by]->lname)
            : null;
        $poFinalInspectionUpdatedByName = $project->po_final_inspection_updated_by && $remarksUsers->has($project->po_final_inspection_updated_by)
            ? trim($remarksUsers[$project->po_final_inspection_updated_by]->fname . ' ' . $remarksUsers[$project->po_final_inspection_updated_by]->lname)
            : null;
        $poRemarksUpdatedByName = $project->po_remarks_updated_by && $remarksUsers->has($project->po_remarks_updated_by)
            ? trim($remarksUsers[$project->po_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->po_remarks_updated_by]->lname)
            : null;
        $roMonitoringDateUpdatedByName = $project->ro_monitoring_date_updated_by && $remarksUsers->has($project->ro_monitoring_date_updated_by)
            ? trim($remarksUsers[$project->ro_monitoring_date_updated_by]->fname . ' ' . $remarksUsers[$project->ro_monitoring_date_updated_by]->lname)
            : null;
        $roFinalInspectionUpdatedByName = $project->ro_final_inspection_updated_by && $remarksUsers->has($project->ro_final_inspection_updated_by)
            ? trim($remarksUsers[$project->ro_final_inspection_updated_by]->fname . ' ' . $remarksUsers[$project->ro_final_inspection_updated_by]->lname)
            : null;
        $roRemarksUpdatedByName = $project->ro_remarks_updated_by && $remarksUsers->has($project->ro_remarks_updated_by)
            ? trim($remarksUsers[$project->ro_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->ro_remarks_updated_by]->lname)
            : null;

        // Post implementation requirements user names
        $pcrSubmissionDeadlineUpdatedByName = $project->pcr_submission_deadline_updated_by && $remarksUsers->has($project->pcr_submission_deadline_updated_by)
            ? trim($remarksUsers[$project->pcr_submission_deadline_updated_by]->fname . ' ' . $remarksUsers[$project->pcr_submission_deadline_updated_by]->lname)
            : null;
        $pcrDateSubmittedToPoUpdatedByName = $project->pcr_date_submitted_to_po_updated_by && $remarksUsers->has($project->pcr_date_submitted_to_po_updated_by)
            ? trim($remarksUsers[$project->pcr_date_submitted_to_po_updated_by]->fname . ' ' . $remarksUsers[$project->pcr_date_submitted_to_po_updated_by]->lname)
            : null;
        $pcrDateReceivedByRoUpdatedByName = $project->pcr_date_received_by_ro_updated_by && $remarksUsers->has($project->pcr_date_received_by_ro_updated_by)
            ? trim($remarksUsers[$project->pcr_date_received_by_ro_updated_by]->fname . ' ' . $remarksUsers[$project->pcr_date_received_by_ro_updated_by]->lname)
            : null;
        $pcrRemarksUpdatedByName = $project->pcr_remarks_updated_by && $remarksUsers->has($project->pcr_remarks_updated_by)
            ? trim($remarksUsers[$project->pcr_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->pcr_remarks_updated_by]->lname)
            : null;
        $rssaReportDeadlineUpdatedByName = $project->rssa_report_deadline_updated_by && $remarksUsers->has($project->rssa_report_deadline_updated_by)
            ? trim($remarksUsers[$project->rssa_report_deadline_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_report_deadline_updated_by]->lname)
            : null;
        $rssaSubmissionStatusUpdatedByName = $project->rssa_submission_status_updated_by && $remarksUsers->has($project->rssa_submission_status_updated_by)
            ? trim($remarksUsers[$project->rssa_submission_status_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_submission_status_updated_by]->lname)
            : null;
        $rssaDateSubmittedToPoUpdatedByName = $project->rssa_date_submitted_to_po_updated_by && $remarksUsers->has($project->rssa_date_submitted_to_po_updated_by)
            ? trim($remarksUsers[$project->rssa_date_submitted_to_po_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_date_submitted_to_po_updated_by]->lname)
            : null;
        $rssaDateReceivedByRoUpdatedByName = $project->rssa_date_received_by_ro_updated_by && $remarksUsers->has($project->rssa_date_received_by_ro_updated_by)
            ? trim($remarksUsers[$project->rssa_date_received_by_ro_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_date_received_by_ro_updated_by]->lname)
            : null;
        $rssaDateSubmittedToCoUpdatedByName = $project->rssa_date_submitted_to_co_updated_by && $remarksUsers->has($project->rssa_date_submitted_to_co_updated_by)
            ? trim($remarksUsers[$project->rssa_date_submitted_to_co_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_date_submitted_to_co_updated_by]->lname)
            : null;
        $rssaRemarksUpdatedByName = $project->rssa_remarks_updated_by && $remarksUsers->has($project->rssa_remarks_updated_by)
            ? trim($remarksUsers[$project->rssa_remarks_updated_by]->fname . ' ' . $remarksUsers[$project->rssa_remarks_updated_by]->lname)
            : null;

        return view('projects.locally-funded-show', compact('project', 'provinces', 'provinceMunicipalities', 'fundSources', 'fundingYears', 'physicalByMonth', 'currentPhysical', 'currentYear', 'currentMonth', 'actualCompletionUpdatedByName', 'financialByMonth', 'financialTotals', 'financialBalance', 'financialUtilizationRate', 'physicalRemarksUpdatedByName', 'physicalRemarksEncodedByName', 'financialRemarksUpdatedByName', 'financialRemarksEncodedByName', 'poMonitoringDateUpdatedByName', 'poFinalInspectionUpdatedByName', 'poRemarksUpdatedByName', 'roMonitoringDateUpdatedByName', 'roFinalInspectionUpdatedByName', 'roRemarksUpdatedByName', 'pcrSubmissionDeadlineUpdatedByName', 'pcrDateSubmittedToPoUpdatedByName', 'pcrDateReceivedByRoUpdatedByName', 'pcrRemarksUpdatedByName', 'rssaReportDeadlineUpdatedByName', 'rssaSubmissionStatusUpdatedByName', 'rssaDateSubmittedToPoUpdatedByName', 'rssaDateReceivedByRoUpdatedByName', 'rssaDateSubmittedToCoUpdatedByName', 'rssaRemarksUpdatedByName', 'activityLogs'));
    }

    /**
     * Show the edit form for a locally funded project.
     */
    public function edit(LocallyFundedProject $project)
    {
        // Cordillera Administrative Region (CAR) provinces
        $provinces = [
            'Abra',
            'Apayao',
            'Benguet',
            'City of Baguio',
            'Ifugao',
            'Kalinga',
            'Mountain Province'
        ];

        // Province to municipalities/cities mapping
        $provinceMunicipalities = [
            'Abra' => ['Bangued', 'Boliney', 'Bucay', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Malibcong', 'Manabo', 'Peñarrubia', 'Pidcal', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin'],
            'Apayao' => ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Pudtol', 'Santa Marcela'],
            'Benguet' => ['Atok', 'Baguio City', 'Bakun', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'],
            'City of Baguio' => ['Baguio City'],
            'Ifugao' => ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Mayoyao', 'Tinoc'],
            'Kalinga' => ['Balbalan', 'Dagupagsan', 'Lubuagan', 'Mabunguran', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk City', 'Tanudan', 'Tinglayan'],
            'Mountain Province' => ['Amlang', 'Amtan', 'Bauko', 'Besao', 'Cervantes', 'Natonin', 'Paracelis', 'Sabangan', 'Sagada', 'Tadian']
        ];

        // Get current user's office
        $currentUserOffice = Auth::user()->office;

        // Fund source and funding year options
        $fundSources = ['SBDP', 'FALGU', 'CMGP', 'SGLGIF', 'SAFPB'];
        $fundingYears = [2025, 2024, 2023, 2022, 2021];

        $prefill = $project->toArray();
        $prefill['barangay_json'] = json_encode(array_values(array_filter(array_map('trim', explode(',', $project->barangay)))));
        $dateFields = [
            'date_nadai',
            'date_confirmation_fund_receipt',
            'date_posting_itb',
            'date_bid_opening',
            'date_noa',
            'date_ntp',
            'actual_start_date',
            'target_date_completion',
            'revised_target_date_completion',
            'actual_date_completion',
        ];

        foreach ($dateFields as $field) {
            $prefill[$field] = $project->{$field} ? $project->{$field}->format('Y-m-d') : null;
        }
        request()->session()->flashInput($prefill);

        $section = request()->query('section');

        return view('projects.locally-funded-edit', compact('project', 'provinces', 'provinceMunicipalities', 'currentUserOffice', 'fundSources', 'fundingYears', 'section'));
    }

    /**
     * Show the create form for locally funded projects
     */
    public function create()
    {
        // Cordillera Administrative Region (CAR) provinces
        $provinces = [
            'Abra',
            'Apayao',
            'Benguet',
            'City of Baguio',
            'Ifugao',
            'Kalinga',
            'Mountain Province'
        ];
        
        // Province to municipalities/cities mapping
        $provinceMunicipalities = [
            'Abra' => ['Bangued', 'Boliney', 'Bucay', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Malibcong', 'Manabo', 'Peñarrubia', 'Pidcal', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin'],
            'Apayao' => ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Pudtol', 'Santa Marcela'],
            'Benguet' => ['Atok', 'Baguio City', 'Bakun', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'],
            'City of Baguio' => ['Baguio City'],
            'Ifugao' => ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Mayoyao', 'Tinoc'],
            'Kalinga' => ['Balbalan', 'Dagupagsan', 'Lubuagan', 'Mabunguran', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk City', 'Tanudan', 'Tinglayan'],
            'Mountain Province' => ['Amlang', 'Amtan', 'Bauko', 'Besao', 'Cervantes', 'Natonin', 'Paracelis', 'Sabangan', 'Sagada', 'Tadian']
        ];
        
        // Get current user's information
        $user = Auth::user();
        $currentUserOffice = $user->office;
        $currentUserRegion = $user->region;
        $currentUserAgency = $user->agency;
        $currentUserProvince = $user->province;
        
        // Fund source and funding year options
        $fundSources = ['SBDP', 'FALGU', 'CMGP', 'SGLGIF', 'SAFPB'];
        $fundingYears = [2025, 2024, 2023, 2022, 2021];
        
        return view('projects.locally-funded-create', compact('provinces', 'provinceMunicipalities', 'currentUserOffice', 'currentUserRegion', 'currentUserAgency', 'currentUserProvince', 'fundSources', 'fundingYears'));
    }

    /**
     * Get municipalities for a selected province (API endpoint)
     */
    public function getMunicipalities($province)
    {
        $provinceMunicipalities = [
            'Abra' => ['Bangued', 'Boliney', 'Bucay', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Malibcong', 'Manabo', 'Peñarrubia', 'Pidcal', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin'],
            'Apayao' => ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Pudtol', 'Santa Marcela'],
            'Benguet' => ['Atok', 'Baguio City', 'Bakun', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'],
            'City of Baguio' => ['Baguio City'],
            'Ifugao' => ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Mayoyao', 'Tinoc'],
            'Kalinga' => ['Balbalan', 'Dagupagsan', 'Lubuagan', 'Mabunguran', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk City', 'Tanudan', 'Tinglayan'],
            'Mountain Province' => ['Amlang', 'Amtan', 'Bauko', 'Besao', 'Cervantes', 'Natonin', 'Paracelis', 'Sabangan', 'Sagada', 'Tadian']
        ];

        $municipalities = $provinceMunicipalities[$province] ?? [];
        return response()->json($municipalities);
    }

    /**
     * Store a newly created locally funded project
     */
    public function store(Request $request)
    {
        $currencyFields = ['lgsf_allocation', 'lgu_counterpart', 'contract_amount', 'disbursed_amount', 'obligation', 'reverted_amount', 'balance'];
        $cleaned = [];

        foreach ($currencyFields as $field) {
            if ($request->has($field)) {
                $raw = $request->input($field);
                if (is_array($raw)) {
                    continue;
                }
                $value = (string) $raw;
                $value = preg_replace('/[^0-9.]/', '', $value);
                if (substr_count($value, '.') > 1) {
                    $firstDot = strpos($value, '.');
                    $value = substr($value, 0, $firstDot + 1) . str_replace('.', '', substr($value, $firstDot + 1));
                }
                $cleaned[$field] = $value;
            }
        }

        if (!empty($cleaned)) {
            $request->merge($cleaned);
        }

        // Validate the request
        $validated = $request->validate([
            // Project Profile
            'province' => 'required|string',
            'city_municipality' => 'required|string',
            'barangay_json' => 'required|string',
            'project_name' => 'required|string',
            'funding_year' => 'required|integer|min:2020|max:2099',
            'fund_source' => 'required|string',
            'subaybayan_project_code' => 'required|string|unique:locally_funded_projects,subaybayan_project_code',
            'project_description' => 'required|string',
            'project_type' => 'required|string',
            'date_nadai' => 'required|date',
            'lgsf_allocation' => 'required|numeric|min:0',
            'lgu_counterpart' => 'required|numeric|min:0',
            'no_of_beneficiaries' => 'required|integer|min:0',
            'rainwater_collection_system' => 'nullable|string',
            'date_confirmation_fund_receipt' => 'required|date',
            
            // Contract Information
            'mode_of_procurement' => 'required|string',
            'implementing_unit' => 'required|string',
            'date_posting_itb' => 'required|date',
            'date_bid_opening' => 'required|date',
            'date_noa' => 'required|date',
            'date_ntp' => 'required|date',
            'contractor' => 'required|string',
            'contract_amount' => 'required|numeric|min:0',
            'project_duration' => 'required|string',
            'actual_start_date' => 'required|date',
            'target_date_completion' => 'required|date',
            'revised_target_date_completion' => 'nullable|date',
            'actual_date_completion' => 'nullable|date',

            // Financial Accomplishment
            'disbursed_amount' => 'nullable|numeric|min:0',
            'obligation' => 'nullable|numeric|min:0',
            'reverted_amount' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'utilization_rate' => 'nullable|numeric|min:0|max:100',
            'financial_remarks' => 'nullable|string',
        ]);

        // Parse the JSON array of barangays and convert to comma-separated string
        $barangayList = json_decode($validated['barangay_json'], true);
        if (is_array($barangayList) && count($barangayList) > 0) {
            $validated['barangay'] = implode(',', $barangayList);
        } else {
            return redirect()->back()->withInput()->withErrors(['barangay' => 'Please select at least one barangay']);
        }
        
        // Remove the JSON field as we've converted it
        unset($validated['barangay_json']);

        // Add user_id
        $validated['user_id'] = Auth::id();
        
        // Add office and region from authenticated user
        $user = Auth::user();
        $validated['office'] = $user->office;
        $validated['region'] = $user->region;

        // Create the project
        \Illuminate\Support\Facades\DB::table('locally_funded_projects')->insert($validated);

        return redirect()->route('projects.locally-funded')
                       ->with('success', 'Locally funded project created successfully!');
    }

    /**
     * Update a locally funded project.
     */
    public function update(Request $request, LocallyFundedProject $project)
    {
        $section = $request->input('section');
        $currencyFields = ['lgsf_allocation', 'lgu_counterpart', 'contract_amount', 'disbursed_amount', 'obligation', 'reverted_amount', 'balance'];
        $cleaned = [];

        foreach ($currencyFields as $field) {
            if ($request->has($field)) {
                $raw = $request->input($field);
                if (is_array($raw)) {
                    continue;
                }
                $value = (string) $raw;
                $value = preg_replace('/[^0-9.]/', '', $value);
                if (substr_count($value, '.') > 1) {
                    $firstDot = strpos($value, '.');
                    $value = substr($value, 0, $firstDot + 1) . str_replace('.', '', substr($value, $firstDot + 1));
                }
                $cleaned[$field] = $value;
            }
        }

        if (!empty($cleaned)) {
            $request->merge($cleaned);
        }

        if ($section === 'physical') {
            if ($request->has('physical_remarks')) {
                $validated = $request->validate([
                    'physical_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'physical_remarks' => $validated['physical_remarks'] ?? null,
                    'physical_remarks_updated_at' => now(),
                    'physical_remarks_updated_by' => Auth::id(),
                    'physical_remarks_encoded_by' => $project->physical_remarks_encoded_by ?: Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Physical remarks updated successfully!');
            }

            if ($request->has('actual_date_completion')) {
                $validated = $request->validate([
                    'actual_date_completion' => 'nullable|date',
                ]);

                $project->update([
                    'actual_date_completion' => $validated['actual_date_completion'] ?? null,
                    'actual_date_completion_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Actual date of completion updated successfully!');
            }


            $field = $request->input('physical_field');
            $month = (int) $request->input('month', now()->month);

            $rulesByField = [
                'status_project_fou' => 'nullable|string',
                'status_project_ro' => 'nullable|string',
                'accomplishment_pct' => 'nullable|numeric|min:0|max:100',
                'accomplishment_pct_ro' => 'nullable|numeric|min:0|max:100',
                'slippage' => 'nullable|numeric|min:0|max:100',
                'slippage_ro' => 'nullable|numeric|min:0|max:100',
                'risk_aging' => 'nullable|string',
                'nc_letters' => 'nullable|string',
            ];

            if (array_key_exists($field, $rulesByField)) {
                $validated = $request->validate([
                    'month' => 'required|integer|min:1|max:12',
                    $field => $rulesByField[$field],
                ]);

                $now = now();

                \Illuminate\Support\Facades\DB::table('locally_funded_physical_updates')->updateOrInsert(
                    [
                        'project_id' => $project->id,
                        'year' => $now->year,
                        'month' => $month,
                    ],
                    [
                        $field => $validated[$field] ?? null,
                        'updated_by' => Auth::id(),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Physical accomplishment updated successfully!');
            }

            $field = null;
            foreach (array_keys($rulesByField) as $candidate) {
                if (array_key_exists($candidate, $request->all())) {
                    $field = $candidate;
                    break;
                }
            }

            if ($field) {
                $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    $field => 'sometimes|array',
                    $field . '.*' => $rulesByField[$field],
                ])->validate();

                $now = now();
                $m = (int) $now->month;

                if (isset($validated[$field]) && array_key_exists($m, $validated[$field])) {
                    $value = $validated[$field][$m];
                    $data = [$field => $value === '' ? null : $value];
                    $data[$field . '_updated_at'] = $now;
                    $data[$field . '_updated_by'] = Auth::id();

                    \Illuminate\Support\Facades\DB::table('locally_funded_physical_updates')->updateOrInsert(
                        [
                            'project_id' => $project->id,
                            'year' => $now->year,
                            'month' => $m,
                        ],
                        array_merge($data, [
                            'updated_by' => Auth::id(),
                            'updated_at' => $now,
                            'created_at' => $now,
                        ])
                    );
                }

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Physical accomplishment updated successfully!');
            }

            return redirect()->route('locally-funded-project.show', $project)
                ->with('success', 'Physical accomplishment updated successfully!');
        }

        if ($section === 'financial') {
            if (!\Illuminate\Support\Facades\Schema::hasTable('locally_funded_financial_updates')) {
                return redirect()->route('locally-funded-project.show', $project)
                    ->with('error', 'Financial updates table is missing. Please create locally_funded_financial_updates first.');
            }

            if ($request->has('financial_remarks')) {
                $validated = $request->validate([
                    'financial_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'financial_remarks' => $validated['financial_remarks'] ?? null,
                    'financial_remarks_updated_at' => now(),
                    'financial_remarks_updated_by' => Auth::id(),
                    'financial_remarks_encoded_by' => $project->financial_remarks_encoded_by ?: Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Financial remarks updated successfully!');
            }

            $rulesByField = [
                'obligation' => 'nullable|numeric|min:0',
                'disbursed_amount' => 'nullable|numeric|min:0',
                'reverted_amount' => 'nullable|numeric|min:0',
                'utilization_rate' => 'nullable|numeric|min:0|max:100',
            ];

            $field = null;
            foreach (array_keys($rulesByField) as $candidate) {
                if (array_key_exists($candidate, $request->all())) {
                    $field = $candidate;
                    break;
                }
            }

            if ($field) {
                $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    $field => 'sometimes|array',
                    $field . '.*' => $rulesByField[$field],
                ])->validate();

                $now = now();
                $m = (int) $now->month;

                if (isset($validated[$field]) && array_key_exists($m, $validated[$field])) {
                    $value = $validated[$field][$m];
                    $data = [$field => $value === '' ? null : $value];
                    $data[$field . '_updated_at'] = $now;
                    $data[$field . '_updated_by'] = Auth::id();

                    \Illuminate\Support\Facades\DB::table('locally_funded_financial_updates')->updateOrInsert(
                        [
                            'project_id' => $project->id,
                            'year' => $now->year,
                            'month' => $m,
                        ],
                        array_merge($data, [
                            'updated_by' => Auth::id(),
                            'updated_at' => $now,
                            'created_at' => $now,
                        ])
                    );
                }

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'Financial accomplishment updated successfully!');
            }

            return redirect()->route('locally-funded-project.show', $project)
                ->with('success', 'Financial accomplishment updated successfully!');
        }

        if ($section === 'monitoring') {
            // Handle PO monitoring fields
            if ($request->has('po_monitoring_date')) {
                $validated = $request->validate([
                    'po_monitoring_date' => 'nullable|date',
                ]);

                $project->update([
                    'po_monitoring_date' => $validated['po_monitoring_date'] ?? null,
                    'po_monitoring_date_updated_at' => now(),
                    'po_monitoring_date_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PO monitoring date updated successfully!');
            }

            if ($request->has('po_final_inspection')) {
                $validated = $request->validate([
                    'po_final_inspection' => 'nullable|string|in:Yes,No',
                ]);

                $project->update([
                    'po_final_inspection' => $validated['po_final_inspection'] ?? null,
                    'po_final_inspection_updated_at' => now(),
                    'po_final_inspection_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PO final inspection updated successfully!');
            }

            if ($request->has('po_remarks')) {
                $validated = $request->validate([
                    'po_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'po_remarks' => $validated['po_remarks'] ?? null,
                    'po_remarks_updated_at' => now(),
                    'po_remarks_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PO remarks updated successfully!');
            }

            // Handle RO monitoring fields
            if ($request->has('ro_monitoring_date')) {
                $validated = $request->validate([
                    'ro_monitoring_date' => 'nullable|date',
                ]);

                $project->update([
                    'ro_monitoring_date' => $validated['ro_monitoring_date'] ?? null,
                    'ro_monitoring_date_updated_at' => now(),
                    'ro_monitoring_date_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RO monitoring date updated successfully!');
            }

            if ($request->has('ro_final_inspection')) {
                $validated = $request->validate([
                    'ro_final_inspection' => 'nullable|string|in:Yes,No',
                ]);

                $project->update([
                    'ro_final_inspection' => $validated['ro_final_inspection'] ?? null,
                    'ro_final_inspection_updated_at' => now(),
                    'ro_final_inspection_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RO final inspection updated successfully!');
            }

            if ($request->has('ro_remarks')) {
                $validated = $request->validate([
                    'ro_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'ro_remarks' => $validated['ro_remarks'] ?? null,
                    'ro_remarks_updated_at' => now(),
                    'ro_remarks_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RO remarks updated successfully!');
            }

            // Post implementation requirements (PCR + RSSA)
            if ($request->has('pcr_submission_deadline')) {
                $validated = $request->validate([
                    'pcr_submission_deadline' => 'nullable|date',
                ]);

                $project->update([
                    'pcr_submission_deadline' => $validated['pcr_submission_deadline'] ?? null,
                    'pcr_submission_deadline_updated_at' => now(),
                    'pcr_submission_deadline_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PCR submission deadline updated successfully!');
            }

            if ($request->has('pcr_date_submitted_to_po')) {
                $validated = $request->validate([
                    'pcr_date_submitted_to_po' => 'nullable|date',
                ]);

                $project->update([
                    'pcr_date_submitted_to_po' => $validated['pcr_date_submitted_to_po'] ?? null,
                    'pcr_date_submitted_to_po_updated_at' => now(),
                    'pcr_date_submitted_to_po_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PCR date submitted to PO updated successfully!');
            }

            if ($request->has('pcr_date_received_by_ro')) {
                $validated = $request->validate([
                    'pcr_date_received_by_ro' => 'nullable|date',
                ]);

                $project->update([
                    'pcr_date_received_by_ro' => $validated['pcr_date_received_by_ro'] ?? null,
                    'pcr_date_received_by_ro_updated_at' => now(),
                    'pcr_date_received_by_ro_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PCR date received by RO updated successfully!');
            }

            if ($request->has('pcr_remarks')) {
                $validated = $request->validate([
                    'pcr_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'pcr_remarks' => $validated['pcr_remarks'] ?? null,
                    'pcr_remarks_updated_at' => now(),
                    'pcr_remarks_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'PCR remarks updated successfully!');
            }

            if ($request->has('rssa_report_deadline')) {
                $validated = $request->validate([
                    'rssa_report_deadline' => 'nullable|date',
                ]);

                $project->update([
                    'rssa_report_deadline' => $validated['rssa_report_deadline'] ?? null,
                    'rssa_report_deadline_updated_at' => now(),
                    'rssa_report_deadline_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA report deadline updated successfully!');
            }

            if ($request->has('rssa_submission_status')) {
                $validated = $request->validate([
                    'rssa_submission_status' => 'nullable|string',
                ]);

                $project->update([
                    'rssa_submission_status' => $validated['rssa_submission_status'] ?? null,
                    'rssa_submission_status_updated_at' => now(),
                    'rssa_submission_status_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA submission status updated successfully!');
            }

            if ($request->has('rssa_date_submitted_to_po')) {
                $validated = $request->validate([
                    'rssa_date_submitted_to_po' => 'nullable|date',
                ]);

                $project->update([
                    'rssa_date_submitted_to_po' => $validated['rssa_date_submitted_to_po'] ?? null,
                    'rssa_date_submitted_to_po_updated_at' => now(),
                    'rssa_date_submitted_to_po_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA date submitted to PO updated successfully!');
            }

            if ($request->has('rssa_date_received_by_ro')) {
                $validated = $request->validate([
                    'rssa_date_received_by_ro' => 'nullable|date',
                ]);

                $project->update([
                    'rssa_date_received_by_ro' => $validated['rssa_date_received_by_ro'] ?? null,
                    'rssa_date_received_by_ro_updated_at' => now(),
                    'rssa_date_received_by_ro_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA date received by RO updated successfully!');
            }

            if ($request->has('rssa_date_submitted_to_co')) {
                $validated = $request->validate([
                    'rssa_date_submitted_to_co' => 'nullable|date',
                ]);

                $project->update([
                    'rssa_date_submitted_to_co' => $validated['rssa_date_submitted_to_co'] ?? null,
                    'rssa_date_submitted_to_co_updated_at' => now(),
                    'rssa_date_submitted_to_co_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA date submitted to CO updated successfully!');
            }

            if ($request->has('rssa_remarks')) {
                $validated = $request->validate([
                    'rssa_remarks' => 'nullable|string',
                ]);

                $project->update([
                    'rssa_remarks' => $validated['rssa_remarks'] ?? null,
                    'rssa_remarks_updated_at' => now(),
                    'rssa_remarks_updated_by' => Auth::id(),
                ]);

                return redirect()->route('locally-funded-project.show', $project)
                    ->with('success', 'RSSA remarks updated successfully!');
            }

            return redirect()->route('locally-funded-project.show', $project)
                ->with('success', 'Monitoring information updated successfully!');
        }

        if ($section === 'profile') {
            $validated = $request->validate([
                // Project Profile
                'province' => 'required|string',
                'city_municipality' => 'required|string',
                'barangay_json' => 'required|string',
                'project_name' => 'required|string',
                'funding_year' => 'required|integer|min:2020|max:2099',
                'fund_source' => 'required|string',
                'subaybayan_project_code' => 'required|string|unique:locally_funded_projects,subaybayan_project_code,' . $project->id,
                'project_description' => 'required|string',
                'project_type' => 'required|string',
                'date_nadai' => 'required|date',
                'lgsf_allocation' => 'required|numeric|min:0',
                'lgu_counterpart' => 'required|numeric|min:0',
                'no_of_beneficiaries' => 'required|integer|min:0',
                'rainwater_collection_system' => 'nullable|string',
                'date_confirmation_fund_receipt' => 'required|date',
            ]);
        } elseif ($section === 'contract') {
            $validated = $request->validate([
                // Contract Information
                'mode_of_procurement' => 'required|string',
                'implementing_unit' => 'required|string',
                'date_posting_itb' => 'required|date',
                'date_bid_opening' => 'required|date',
                'date_noa' => 'required|date',
                'date_ntp' => 'required|date',
                'contractor' => 'required|string',
                'contract_amount' => 'required|numeric|min:0',
                'project_duration' => 'required|string',
                'actual_start_date' => 'required|date',
                'target_date_completion' => 'required|date',
                'revised_target_date_completion' => 'nullable|date',
                'actual_date_completion' => 'nullable|date',
            ]);
        } else {
            $validated = $request->validate([
                // Project Profile
                'province' => 'required|string',
                'city_municipality' => 'required|string',
                'barangay_json' => 'required|string',
                'project_name' => 'required|string',
                'funding_year' => 'required|integer|min:2020|max:2099',
                'fund_source' => 'required|string',
                'subaybayan_project_code' => 'required|string|unique:locally_funded_projects,subaybayan_project_code,' . $project->id,
                'project_description' => 'required|string',
                'project_type' => 'required|string',
                'date_nadai' => 'required|date',
                'lgsf_allocation' => 'required|numeric|min:0',
                'lgu_counterpart' => 'required|numeric|min:0',
                'no_of_beneficiaries' => 'required|integer|min:0',
                'rainwater_collection_system' => 'nullable|string',
                'date_confirmation_fund_receipt' => 'required|date',

                // Contract Information
                'mode_of_procurement' => 'required|string',
                'implementing_unit' => 'required|string',
                'date_posting_itb' => 'required|date',
                'date_bid_opening' => 'required|date',
                'date_noa' => 'required|date',
                'date_ntp' => 'required|date',
                'contractor' => 'required|string',
                'contract_amount' => 'required|numeric|min:0',
                'project_duration' => 'required|string',
                'actual_start_date' => 'required|date',
                'target_date_completion' => 'required|date',
                'revised_target_date_completion' => 'nullable|date',
                'actual_date_completion' => 'nullable|date',

                // Financial Accomplishment
                'disbursed_amount' => 'nullable|numeric|min:0',
                'obligation' => 'nullable|numeric|min:0',
                'reverted_amount' => 'nullable|numeric|min:0',
                'balance' => 'nullable|numeric|min:0',
                'utilization_rate' => 'nullable|numeric|min:0|max:100',
                'financial_remarks' => 'nullable|string',
            ]);
        }

        if (array_key_exists('barangay_json', $validated)) {
            $barangayList = json_decode($validated['barangay_json'], true);
            if (is_array($barangayList) && count($barangayList) > 0) {
                $validated['barangay'] = implode(',', $barangayList);
            } else {
                return redirect()->back()->withInput()->withErrors(['barangay' => 'Please select at least one barangay']);
            }

            unset($validated['barangay_json']);
        }

        $project->update($validated);

        return redirect()->route('locally-funded-project.show', $project)
            ->with('success', 'Locally funded project updated successfully!');
    }

    /**
     * Delete the specified locally funded project
     */
    public function destroy(LocallyFundedProject $project)
    {
        $project->delete();
        return redirect()->route('projects.locally-funded')
            ->with('success', 'Locally funded project deleted successfully!');
    }
}
