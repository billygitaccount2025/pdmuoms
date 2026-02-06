<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\LpmcDocument;
use App\Models\User;

class LocalProjectMonitoringCommitteeController extends Controller
{
    private function getOffices(): array
    {
        return [
            'Abra' => [
                'PLGU Abra', 'Bangued', 'Boliney', 'Bucay', 'Bucloc', 'Daguioman', 'Danglas', 'Dolores',
                'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Luba', 'Malibcong',
                'Manabo', 'Peñarrubia', 'Pidigan', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan',
                'San Quintin', 'Tayum', 'Tineg', 'Tubo', 'Villaviciosa',
            ],
            'Apayao' => [
                'PLGU Apayao', 'Calanasan', 'Conner', 'Flora', 'Kabugao', 'Luna', 'Pudtol', 'Santa Marcela',
            ],
            'Benguet' => [
                'PLGU Benguet', 'Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan',
                'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay',
            ],
            'City of Baguio' => [
                'City of Baguio',
            ],
            'Ifugao' => [
                'PLGU Ifugao', 'Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan',
                'Kiangan', 'Lagawe', 'Lamut', 'Mayoyao', 'Tinoc',
            ],
            'Kalinga' => [
                'PLGU Kalinga', 'Balbalan', 'Lubuagan', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk', 'Tanudan',
            ],
            'Mountain Province' => [
                'PLGU Mountain Province', 'Barlig', 'Bauko', 'Besao', 'Bontoc', 'Natonin', 'Paracelis',
                'Sabangan', 'Sadanga', 'Sagada', 'Tadian',
            ],
        ];
    }

    private function buildOfficeRows(array $offices): array
    {
        $officeRows = [];
        foreach ($offices as $province => $municipalities) {
            foreach ($municipalities as $office) {
                $officeRows[] = [
                    'province' => $province,
                    'city_municipality' => $office,
                ];
            }
        }
        return $officeRows;
    }

    private function findProvinceByOffice(string $officeName): ?string
    {
        foreach ($this->getOffices() as $province => $municipalities) {
            if (in_array($officeName, $municipalities, true)) {
                return $province;
            }
        }
        return null;
    }

    private function indexDocumentsByKey($documents): array
    {
        $indexed = [];
        foreach ($documents as $doc) {
            $key = $doc->doc_type . '|' . ($doc->year ?? '') . '|' . ($doc->quarter ?? '');
            $indexed[$key] = $doc;
        }
        return $indexed;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officeRows = $this->buildOfficeRows($this->getOffices());

        $user = auth()->user();
        if ($user && $user->agency === 'LGU' && !empty($user->office)) {
            $officeRows = array_values(array_filter($officeRows, function ($row) use ($user) {
                return $row['city_municipality'] === $user->office;
            }));
        } elseif ($user && $user->agency === 'DILG' && !empty($user->province)) {
            $selectedProvince = request('province');
            $userProvince = !empty($selectedProvince) ? $selectedProvince : $user->province;
            if ($userProvince !== 'Regional Office') {
                $officeRows = array_values(array_filter($officeRows, function ($row) use ($userProvince) {
                    return $row['province'] === $userProvince;
                }));
            }
        }

        $officeNames = array_values(array_unique(array_map(function ($row) {
            return $row['city_municipality'];
        }, $officeRows)));

        $documentsByOffice = [];
        if (!empty($officeNames)) {
            $documents = LpmcDocument::whereIn('office', $officeNames)->get();
            foreach ($documents as $doc) {
                $key = $doc->doc_type . '|' . ($doc->year ?? '') . '|' . ($doc->quarter ?? '');
                $documentsByOffice[$doc->office][$key] = $doc;
            }
        }

        return view('reports.local-project-monitoring-committee.index', compact('officeRows', 'documentsByOffice'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('reports.local-project-monitoring-committee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation for storing a new record
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $officeName = $id;
        $province = $this->findProvinceByOffice($officeName);
        $documents = LpmcDocument::where('office', $officeName)->get();
        $documentsByKey = $this->indexDocumentsByKey($documents);
        return view('reports.local-project-monitoring-committee.show', compact('officeName', 'province', 'documents', 'documentsByKey'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $officeName = $id;
        $province = $this->findProvinceByOffice($officeName);
        $documents = LpmcDocument::where('office', $officeName)->get();
        $documentsByKey = $this->indexDocumentsByKey($documents);
        $uploaderIds = $documents->pluck('uploaded_by')->filter()->unique()->values()->all();
        $approverIds = $documents->pluck('approved_by_dilg_po')
            ->merge($documents->pluck('approved_by_dilg_ro'))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $userIds = array_values(array_unique(array_merge($uploaderIds, $approverIds)));
        $usersById = $userIds
            ? User::whereIn('idno', $userIds)->get()->keyBy('idno')
            : collect();

        return view('reports.local-project-monitoring-committee.edit', compact('officeName', 'province', 'documentsByKey', 'usersById'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementation for updating the record
    }

    public function upload(Request $request, $id)
    {
        $officeName = $id;
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'doc_type' => ['required', 'string', 'max:50'],
            'year' => ['nullable', 'integer'],
            'quarter' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
        ]);

        $province = $this->findProvinceByOffice($officeName) ?? 'Unknown';
        $docType = $request->input('doc_type');
        $year = $request->input('year');
        $quarter = $request->input('quarter');

        $file = $request->file('document');
        $officeSlug = Str::slug($officeName, '_');
        $path = $file->store('lpmc/' . $officeSlug, 'public');

        LpmcDocument::updateOrCreate(
            [
                'office' => $officeName,
                'doc_type' => $docType,
                'year' => $year,
                'quarter' => $quarter,
            ],
            [
                'province' => $province,
                'file_path' => $path,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'status' => 'pending',
                'approved_at' => null,
                'approved_at_dilg_po' => null,
                'approved_at_dilg_ro' => null,
                'approved_by_dilg_po' => null,
                'approved_by_dilg_ro' => null,
                'approval_remarks' => null,
                'user_remarks' => null,
            ]
        );

        return redirect()
            ->back()
            ->with('success', 'Document uploaded successfully.');
    }

    public function viewDocument($id, $docId)
    {
        $officeName = $id;
        $document = LpmcDocument::where('office', $officeName)->where('id', $docId)->firstOrFail();
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }
        $filePath = Storage::disk('public')->path($document->file_path);
        return response()->file($filePath);
    }

    public function approveDocument(Request $request, $id, $docId)
    {
        $officeName = $id;
        $user = auth()->user();
        if (!$user || $user->agency !== 'DILG') {
            abort(403);
        }

        $request->validate([
            'action' => ['required', 'in:approve,return'],
            'remarks' => ['required_if:action,return', 'nullable', 'string'],
        ]);

        $document = LpmcDocument::where('office', $officeName)->where('id', $docId)->firstOrFail();
        $now = now();
        $action = $request->input('action');
        $remarks = $request->input('remarks');

        $isRegionalOffice = $user->province === 'Regional Office';
        $isProvincialOffice = !$isRegionalOffice;

        $updates = [
            'approved_at' => $now,
        ];

        if ($action === 'approve') {
            if ($isProvincialOffice) {
                $updates['approved_at_dilg_po'] = $now;
                $updates['approved_by_dilg_po'] = $user->idno;
                $updates['status'] = 'pending_ro';
                $updates['approval_remarks'] = null;
            } else {
                $updates['approved_at_dilg_ro'] = $now;
                $updates['approved_by_dilg_ro'] = $user->idno;
                $updates['status'] = 'approved';
                $updates['approval_remarks'] = null;
            }
        } else {
            if ($isRegionalOffice) {
                $updates['approved_at_dilg_ro'] = null;
                $updates['approved_by_dilg_ro'] = $user->idno;
            } else {
                $updates['approved_by_dilg_po'] = $user->idno;
            }
            $updates['status'] = 'returned';
            $updates['approval_remarks'] = $remarks;
            $updates['user_remarks'] = $remarks;
        }

        $document->update($updates);

        return back()->with('success', $action === 'approve' ? 'Document validated.' : 'Document returned.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Implementation for deleting the record
    }
}
