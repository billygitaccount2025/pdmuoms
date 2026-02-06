@extends('layouts.dashboard')

@section('page-title', 'Local Project Monitoring Committee')

@section('content')
<div class="content-header">
    <h1>Local Project Monitoring Committee</h1>
    <p>Manage and monitor local project committees</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
            </div>
            <div class="card-body">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
            </div>
            <div class="card-body">
                <div style="margin-bottom: 16px;">
                    <div style="position: relative; max-width: 420px;">
                        <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 12px;"></i>
                        <input
                            type="text"
                            id="lpmc-search"
                            placeholder="Search province or city/municipality..."
                            style="width: 100%; padding: 10px 12px 10px 32px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; background-color: #f9fafb;"
                        >
                    </div>
                </div>
                <div class="table-responsive" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">Province</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">City/Municipality</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">Executive Order for CY 2025 (MOV)</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">Annual Work and Financial Plan (AWFP) for CY 2025</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">Monitoring and Evaluation Plan for CY 2025</th>
                                <th colspan="12" style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Per Quarter Uploads</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">Executive Order for 2026</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">CY 2026 Annual Work and Financial Plan</th>
                                <th rowspan="3" style="padding: 12px; text-align: left; color: #374151; font-weight: 600; font-size: 14px;">CY 2026 Monitoring and Evaluation Plan</th>
                                <th rowspan="3" style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Actions</th>
                            </tr>
                            <tr>
                                <th colspan="4" style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Meetings Conducted</th>
                                <th colspan="4" style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Monitoring Conducted</th>
                                <th colspan="4" style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Training Conducted</th>
                            </tr>
                            <tr>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q1</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q2</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q3</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q4</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q1</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q2</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q3</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q4</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q1</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q2</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q3</th>
                                <th style="padding: 12px; text-align: center; color: #374151; font-weight: 600; font-size: 14px;">Q4</th>
                            </tr>
                        </thead>
                        <tbody id="lpmc-table-body">
                            @forelse ($officeRows as $row)
                                @php
                                    $officeDocs = $documentsByOffice[$row['city_municipality']] ?? [];
                                    $statusIcon = function ($doc) {
                                        if ($doc && $doc->status === 'approved') {
                                            return '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
                                        }
                                        return '<span style="color: #6b7280;">-</span>';
                                    };
                                @endphp
                                <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease;">
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{{ $row['province'] }}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{{ $row['city_municipality'] }}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['eo|2025|'] ?? null) !!}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['awfp|2025|'] ?? null) !!}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['mep|2025|'] ?? null) !!}</td>

                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['meetings||Q1'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['meetings||Q2'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['meetings||Q3'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['meetings||Q4'] ?? null) !!}</td>

                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['monitoring||Q1'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['monitoring||Q2'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['monitoring||Q3'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['monitoring||Q4'] ?? null) !!}</td>

                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['training||Q1'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['training||Q2'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['training||Q3'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['training||Q4'] ?? null) !!}</td>

                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['eo|2026|'] ?? null) !!}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['awfp|2026|'] ?? null) !!}</td>
                                    <td style="padding: 12px; color: #111827; font-size: 14px;">{!! $statusIcon($officeDocs['mep|2026|'] ?? null) !!}</td>
                                    <td style="padding: 12px; text-align: center;">
                                        <a href="{{ route('local-project-monitoring-committee.edit', $row['city_municipality']) }}" style="display: inline-block; padding: 8px 16px; background-color: #002C76; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; transition: all 0.3s ease;">
                                            <i class="fas fa-eye" style="margin-right: 4px;"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease;">
                                    <td colspan="21" style="padding: 40px; text-align: center; color: #6b7280;">
                                        <i class="fas fa-table" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                                        No records found.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="lpmc-no-results" style="display: none; border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease;">
                                <td colspan="21" style="padding: 40px; text-align: center; color: #6b7280;">
                                    <i class="fas fa-search" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                                    No matching records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    tr:hover {
        background-color: #f9fafb !important;
    }
</style>

<script>
    (function () {
        const searchInput = document.getElementById('lpmc-search');
        const tableBody = document.getElementById('lpmc-table-body');
        const noResultsRow = document.getElementById('lpmc-no-results');

        if (!searchInput || !tableBody) return;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const rows = Array.from(tableBody.querySelectorAll('tr'))
                .filter(row => row.id !== 'lpmc-no-results');

            let visibleCount = 0;

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (!cells.length) return;

                const province = (cells[0].textContent || '').trim().toLowerCase();
                const city = (cells[1].textContent || '').trim().toLowerCase();
                const matches = province.includes(query) || city.includes(query);

                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount += 1;
            });

            if (noResultsRow) {
                noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        });
    })();
</script>
@endsection
