@extends('layouts.dashboard')

@section('title', 'Locally Funded Projects')
@section('page-title', 'Locally Funded Projects')

@section('content')
    <div class="content-header">
        <h1>Locally Funded Projects</h1>
        <p>Manage and review locally funded project records.</p>
    </div>

    <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header with Create Button -->
        <div class="projects-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #002C76; font-size: 18px; margin: 0;">Projects</h2>
            @if(Auth::user()->agency === 'DILG' && Auth::user()->province === 'Regional Office')
            <a href="{{ route('locally-funded-project.create') }}" style="padding: 10px 20px; background-color: #002C76; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                <i class="fas fa-plus"></i> Create Project
            </a>
            @endif
        </div>

        @if($projects->isEmpty())
            @if(Auth::user()->agency === 'DILG' && Auth::user()->province === 'Regional Office')
            <p style="margin: 0; color: #6b7280; text-align: center; padding: 40px 0;">No projects found. <a href="{{ route('locally-funded-project.create') }}" style="color: #002C76; text-decoration: none; font-weight: 600;">Create one now</a></p>
            @else
            <p style="margin: 0; color: #6b7280; text-align: center; padding: 40px 0;">No projects found.</p>
            @endif
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background-color: #f3f4f6; border-bottom: 2px solid #d1d5db;">
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Project Code</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Location</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Funding Year</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Fund Source</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Project Title</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Procurement Type</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">LGSF Allocation</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Status (Actual)</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151;">Status (Subaybayan)</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600; color: #374151;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr style="border-bottom: 1px solid #e5e7eb; transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='white'">
                                <td style="padding: 12px; color: #374151; font-weight: 500;">{{ $project->subaybayan_project_code }}</td>
                                <td style="padding: 12px; color: #374151;">
                                    <div style="font-size: 12px; line-height: 1.4;">
                                        <strong>Province:</strong> {{ $project->province }}<br>
                                        <strong>City/Mun:</strong> {{ $project->city_municipality }}<br>
                                        @php
                                            $barangays = array_filter(array_map('trim', explode(',', (string) $project->barangay)));
                                        @endphp
                                        <strong>Barangay:</strong>
                                        @if(count($barangays))
                                            <ul style="margin: 4px 0 0 16px; padding: 0;">
                                                @foreach($barangays as $barangay)
                                                    <li style="margin: 0; list-style: disc;">{{ $barangay }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 12px; color: #374151;">{{ $project->funding_year }}</td>
                                <td style="padding: 12px; color: #374151;">{{ $project->fund_source }}</td>
                                <td style="padding: 12px; color: #374151;">
                                    <span title="{{ $project->project_name }}" style="display: block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $project->project_name }}
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #374151;">{{ $project->mode_of_procurement }}</td>
                                <td style="padding: 12px; color: #374151; text-align: right;">₱ {{ number_format($project->lgsf_allocation, 2) }}</td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="display: inline-block; padding: 4px 8px; background-color: #dbeafe; color: #0369a1; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        {{ $physicalStatuses[$project->id]['status_actual'] ?? 'Pending' }}
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="display: inline-block; padding: 4px 8px; background-color: #dbeafe; color: #0369a1; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        {{ $physicalStatuses[$project->id]['status_subaybayan'] ?? 'Pending' }}
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="{{ route('locally-funded-project.show', $project) }}" style="padding: 6px 10px; background-color: #0369a1; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; text-decoration: none; transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='#0c4a6e'" onmouseout="this.style.backgroundColor='#0369a1'">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <style>
        table td {
            vertical-align: top;
        }

        .projects-header {
            flex-wrap: wrap;
            gap: 12px;
        }

        @media (max-width: 1024px) {
            .projects-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            table {
                min-width: 960px;
            }

            th, td {
                padding: 8px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .content-header h1 {
                font-size: 20px;
            }

            .content-header p {
                font-size: 12px;
            }
        }
    </style>
@endsection
