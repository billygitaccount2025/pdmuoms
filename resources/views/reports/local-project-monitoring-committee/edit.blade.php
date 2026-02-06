@extends('layouts.dashboard')

@section('title', 'Local Project Monitoring Committee - Update')
@section('page-title', 'Update Local Project Monitoring Committee')

@section('content')
    <div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
        <div>
            <h1>Update - {{ $officeName }}</h1>
            <p>Upload or update committee documents and activities.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('local-project-monitoring-committee.index') }}" style="display: inline-flex; padding: 10px 18px; background-color: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none; align-items: center; gap: 6px; white-space: nowrap;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if (session('success'))
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div>
                <label style="display: block; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Province</label>
                <p style="color: #111827; font-size: 15px; font-weight: 500; margin: 0;">{{ $province ?? '—' }}</p>
            </div>
            <div>
                <label style="display: block; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">City/Municipality</label>
                <p style="color: #111827; font-size: 15px; font-weight: 500; margin: 0;">{{ $officeName }}</p>
            </div>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #002C76; font-size: 18px; margin-bottom: 20px; font-weight: 600;">Uploading of Documents</h2>

        <div style="display: grid; grid-template-columns: repeat(3, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
            @php
                $docBlocks = [
                    ['label' => 'Executive Order for CY 2025 (MOV)', 'doc_type' => 'eo', 'year' => 2025],
                    ['label' => 'Annual Work and Financial Plan (AWFP) for CY 2025', 'doc_type' => 'awfp', 'year' => 2025],
                    ['label' => 'Monitoring and Evaluation Plan for CY 2025', 'doc_type' => 'mep', 'year' => 2025],
                    ['label' => 'Executive Order for 2026', 'doc_type' => 'eo', 'year' => 2026],
                    ['label' => 'CY 2026 Annual Work and Financial Plan', 'doc_type' => 'awfp', 'year' => 2026],
                    ['label' => 'CY 2026 Monitoring and Evaluation Plan', 'doc_type' => 'mep', 'year' => 2026],
                ];
            @endphp
            @foreach ($docBlocks as $docBlock)
                @php
                    $docKey = $docBlock['doc_type'] . '|' . $docBlock['year'] . '|';
                    $doc = $documentsByKey[$docKey] ?? null;
                    $inputId = 'lpmc-doc-input-' . $docBlock['doc_type'] . '-' . $docBlock['year'];
                    $buttonId = 'lpmc-doc-btn-' . $docBlock['doc_type'] . '-' . $docBlock['year'];
                    $filenameId = 'lpmc-doc-file-' . $docBlock['doc_type'] . '-' . $docBlock['year'];
                    $hasFile = $doc && $doc->file_path;
                    $isReturned = $doc && $doc->status === 'returned';
                    $isApprovedRo = $doc && $doc->approved_at_dilg_ro;
                    $isPendingRo = $doc && $doc->approved_at_dilg_po && !$doc->approved_at_dilg_ro;
                    $statusLabel = 'Pending Upload';
                    $statusColor = '#f59e0b';
                    if ($hasFile) {
                        $statusLabel = 'For DILG Provincial Office Validation';
                        $statusColor = '#3b82f6';
                    }
                    if ($isPendingRo) {
                        $statusLabel = 'For DILG Regional Office Validation';
                        $statusColor = '#3b82f6';
                    }
                    if ($isApprovedRo) {
                        $statusLabel = 'Approved';
                        $statusColor = '#059669';
                    }
                    if ($isReturned) {
                        $statusLabel = 'Returned';
                        $statusColor = '#dc2626';
                    }
                    $uploader = $doc && $doc->uploaded_by && isset($usersById[$doc->uploaded_by]) ? $usersById[$doc->uploaded_by] : null;
                    $poApprover = $doc && $doc->approved_by_dilg_po && isset($usersById[$doc->approved_by_dilg_po]) ? $usersById[$doc->approved_by_dilg_po] : null;
                    $roApprover = $doc && $doc->approved_by_dilg_ro && isset($usersById[$doc->approved_by_dilg_ro]) ? $usersById[$doc->approved_by_dilg_ro] : null;
                @endphp
                <form method="POST" action="{{ route('local-project-monitoring-committee.upload', $officeName) }}" enctype="multipart/form-data" style="border: 1px dashed #cbd5f5; padding: 18px; border-radius: 8px; background-color: #f9fafb;">
                    @csrf
                    <input type="hidden" name="doc_type" value="{{ $docBlock['doc_type'] }}">
                    <input type="hidden" name="year" value="{{ $docBlock['year'] }}">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 6px;">
                        <label style="display: block; color: #374151; font-weight: 600; font-size: 13px; margin: 0;">{{ $docBlock['label'] }}</label>
                        <span style="display: inline-block; padding: 4px 10px; background-color: {{ $statusColor }}; color: white; border-radius: 20px; font-size: 10px; font-weight: 600;">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div style="font-size: 11px; color: #6b7280; margin-bottom: 8px;">
                        @if ($doc && $doc->uploaded_at)
                            Uploaded at: {{ $doc->uploaded_at->format('M d, Y H:i') }}
                            @if ($uploader)
                                by {{ trim($uploader->fname . ' ' . $uploader->lname) }}
                            @endif
                        @endif
                        @if ($doc && $doc->status === 'returned' && $doc->approval_remarks)
                            <div style="color: #dc2626; font-weight: 600;">
                                Return remarks: {{ $doc->approval_remarks }}
                            </div>
                        @endif
                        @if ($doc && $doc->approved_at_dilg_po)
                            <div>
                                DILG Provincial Validated at: {{ $doc->approved_at_dilg_po->format('M d, Y H:i') }}
                                @if ($poApprover)
                                    by {{ trim($poApprover->fname . ' ' . $poApprover->lname) }}
                                @endif
                            </div>
                        @endif
                        @if ($doc && $doc->approved_at_dilg_ro)
                            <div>
                                DILG Regional Validated at: {{ $doc->approved_at_dilg_ro->format('M d, Y H:i') }}
                                @if ($roApprover)
                                    by {{ trim($roApprover->fname . ' ' . $roApprover->lname) }}
                                @endif
                            </div>
                        @endif
                    </div>
                    @if ($doc && $doc->file_path)
                        <a href="{{ route('local-project-monitoring-committee.document', [$officeName, $doc->id]) }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin-bottom: 8px; color: #002C76; font-size: 12px; text-decoration: none;">
                            <i class="fas fa-file"></i> View current file
                        </a>
                    @endif
                    <input
                        id="{{ $inputId }}"
                        type="file"
                        name="document"
                        required
                        style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; margin-bottom: 8px;"
                        onchange="showLpmcSaveButton(this, '{{ $buttonId }}', '{{ $filenameId }}')"
                    >
                    <div id="{{ $filenameId }}" style="display: none; margin-bottom: 8px; font-size: 12px; color: #6b7280;"></div>
                    <button
                        type="submit"
                        id="{{ $buttonId }}"
                        style="width: 100%; padding: 8px 12px; background-color: #002C76; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px; opacity: 0; pointer-events: none; transition: all 0.3s ease;"
                    >
                        Upload
                    </button>
                    @php
                        $isRegionalOfficeUser = Auth::user()->agency === 'DILG' && Auth::user()->province === 'Regional Office';
                        $isProvincialDilgUser = Auth::user()->agency === 'DILG' && Auth::user()->province !== 'Regional Office';
                        $isForRegionalValidation = $doc && $doc->approved_at_dilg_po && !$doc->approved_at_dilg_ro;
                        $isApproved = $doc && $doc->status === 'approved';
                        $showApprovalButtons = $doc
                            && Auth::user()->agency === 'DILG'
                            && !($isProvincialDilgUser && $isForRegionalValidation)
                            && !($isRegionalOfficeUser && $isApproved)
                            && !($isProvincialDilgUser && $isApproved);
                    @endphp
                    @if ($showApprovalButtons)
                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="button" onclick="openLpmcApprovalModal({{ $doc->id }}, 'approve')" style="flex: 1; padding: 8px 12px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                Approve
                            </button>
                            <button type="button" onclick="openLpmcApprovalModal({{ $doc->id }}, 'return')" style="flex: 1; padding: 8px 12px; background-color: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                Return
                            </button>
                        </div>
                    @endif
                </form>
            @endforeach
        </div>

        <div style="display: grid; gap: 12px;">
            @php
                $quarters = ['Q1' => 'Quarter 1', 'Q2' => 'Quarter 2', 'Q3' => 'Quarter 3', 'Q4' => 'Quarter 4'];
            @endphp
            @foreach ($quarters as $quarter => $label)
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="lpmc-accordion-toggle" data-target="lpmc-{{ $quarter }}" style="width: 100%; padding: 14px 16px; background-color: #002C76; color: white; border: none; text-align: left; cursor: pointer; font-weight: 600; font-size: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <span>{{ $label }}</span>
                        <i class="fas fa-chevron-down" style="transition: transform 0.3s;"></i>
                    </button>
                    <div id="lpmc-{{ $quarter }}" style="display: none; padding: 16px; background-color: #ffffff;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                            @php
                                $quarterDocs = [
                                    ['label' => 'Meetings Conducted', 'doc_type' => 'meetings'],
                                    ['label' => 'Monitoring Conducted', 'doc_type' => 'monitoring'],
                                    ['label' => 'Training Conducted', 'doc_type' => 'training'],
                                ];
                            @endphp
                            @foreach ($quarterDocs as $qDoc)
                                @php
                                    $docKey = $qDoc['doc_type'] . '||' . $quarter;
                                    $doc = $documentsByKey[$docKey] ?? null;
                                    $inputId = 'lpmc-q-input-' . $qDoc['doc_type'] . '-' . $quarter;
                                    $buttonId = 'lpmc-q-btn-' . $qDoc['doc_type'] . '-' . $quarter;
                                    $filenameId = 'lpmc-q-file-' . $qDoc['doc_type'] . '-' . $quarter;
                                    $hasFile = $doc && $doc->file_path;
                                    $isReturned = $doc && $doc->status === 'returned';
                                    $isApprovedRo = $doc && $doc->approved_at_dilg_ro;
                                    $isPendingRo = $doc && $doc->approved_at_dilg_po && !$doc->approved_at_dilg_ro;
                                    $statusLabel = 'Pending Upload';
                                    $statusColor = '#f59e0b';
                                    if ($hasFile) {
                                        $statusLabel = 'For DILG Provincial Office Validation';
                                        $statusColor = '#3b82f6';
                                    }
                                    if ($isPendingRo) {
                                        $statusLabel = 'For DILG Regional Office Validation';
                                        $statusColor = '#3b82f6';
                                    }
                                    if ($isApprovedRo) {
                                        $statusLabel = 'Approved';
                                        $statusColor = '#059669';
                                    }
                                    if ($isReturned) {
                                        $statusLabel = 'Returned';
                                        $statusColor = '#dc2626';
                                    }
                                    $uploader = $doc && $doc->uploaded_by && isset($usersById[$doc->uploaded_by]) ? $usersById[$doc->uploaded_by] : null;
                                    $poApprover = $doc && $doc->approved_by_dilg_po && isset($usersById[$doc->approved_by_dilg_po]) ? $usersById[$doc->approved_by_dilg_po] : null;
                                    $roApprover = $doc && $doc->approved_by_dilg_ro && isset($usersById[$doc->approved_by_dilg_ro]) ? $usersById[$doc->approved_by_dilg_ro] : null;
                                @endphp
                                <form method="POST" action="{{ route('local-project-monitoring-committee.upload', $officeName) }}" enctype="multipart/form-data" style="border: 1px dashed #cbd5f5; padding: 16px; border-radius: 8px; background-color: #f9fafb;">
                                    @csrf
                                    <input type="hidden" name="doc_type" value="{{ $qDoc['doc_type'] }}">
                                    <input type="hidden" name="quarter" value="{{ $quarter }}">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 6px;">
                                        <label style="display: block; color: #374151; font-weight: 600; font-size: 13px; margin: 0;">{{ $qDoc['label'] }}</label>
                                        <span style="display: inline-block; padding: 4px 10px; background-color: {{ $statusColor }}; color: white; border-radius: 20px; font-size: 10px; font-weight: 600;">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div style="font-size: 11px; color: #6b7280; margin-bottom: 8px;">
                                        @if ($doc && $doc->uploaded_at)
                                            Uploaded at: {{ $doc->uploaded_at->format('M d, Y H:i') }}
                                            @if ($uploader)
                                                by {{ trim($uploader->fname . ' ' . $uploader->lname) }}
                                            @endif
                                        @endif
                                        @if ($doc && $doc->status === 'returned' && $doc->approval_remarks)
                                            <div style="color: #dc2626; font-weight: 600;">
                                                Return remarks: {{ $doc->approval_remarks }}
                                            </div>
                                        @endif
                                        @if ($doc && $doc->approved_at_dilg_po)
                                            <div>
                                                DILG Provincial Validated at: {{ $doc->approved_at_dilg_po->format('M d, Y H:i') }}
                                                @if ($poApprover)
                                                    by {{ trim($poApprover->fname . ' ' . $poApprover->lname) }}
                                                @endif
                                            </div>
                                        @endif
                                        @if ($doc && $doc->approved_at_dilg_ro)
                                            <div>
                                                DILG Regional Validated at: {{ $doc->approved_at_dilg_ro->format('M d, Y H:i') }}
                                                @if ($roApprover)
                                                    by {{ trim($roApprover->fname . ' ' . $roApprover->lname) }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @if ($doc && $doc->file_path)
                                        <a href="{{ route('local-project-monitoring-committee.document', [$officeName, $doc->id]) }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin-bottom: 8px; color: #002C76; font-size: 12px; text-decoration: none;">
                                            <i class="fas fa-file"></i> View current file
                                        </a>
                                    @endif
                                    <input
                                        id="{{ $inputId }}"
                                        type="file"
                                        name="document"
                                        required
                                        style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; margin-bottom: 8px;"
                                        onchange="showLpmcSaveButton(this, '{{ $buttonId }}', '{{ $filenameId }}')"
                                    >
                                    <div id="{{ $filenameId }}" style="display: none; margin-bottom: 8px; font-size: 12px; color: #6b7280;"></div>
                                    <button
                                        type="submit"
                                        id="{{ $buttonId }}"
                                        style="width: 100%; padding: 8px 12px; background-color: #002C76; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px; opacity: 0; pointer-events: none; transition: all 0.3s ease;"
                                    >
                                        Upload
                                    </button>
                                    @php
                                        $isRegionalOfficeUser = Auth::user()->agency === 'DILG' && Auth::user()->province === 'Regional Office';
                                        $isProvincialDilgUser = Auth::user()->agency === 'DILG' && Auth::user()->province !== 'Regional Office';
                                        $isForRegionalValidation = $doc && $doc->approved_at_dilg_po && !$doc->approved_at_dilg_ro;
                                    $isApproved = $doc && $doc->status === 'approved';
                                    $showApprovalButtons = $doc
                                        && Auth::user()->agency === 'DILG'
                                        && !($isProvincialDilgUser && $isForRegionalValidation)
                                        && !($isRegionalOfficeUser && $isApproved)
                                        && !($isProvincialDilgUser && $isApproved);
                                    @endphp
                                    @if ($showApprovalButtons)
                                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                                            <button type="button" onclick="openLpmcApprovalModal({{ $doc->id }}, 'approve')" style="flex: 1; padding: 8px 12px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                                Approve
                                            </button>
                                            <button type="button" onclick="openLpmcApprovalModal({{ $doc->id }}, 'return')" style="flex: 1; padding: 8px 12px; background-color: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                                Return
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.querySelectorAll('.lpmc-accordion-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const panel = document.getElementById(targetId);
                if (!panel) return;

                const isOpen = panel.style.display === 'block';
                panel.style.display = isOpen ? 'none' : 'block';

                const icon = button.querySelector('i');
                if (icon) {
                    icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            });
        });
    </script>

    <script>
        function showLpmcSaveButton(fileInput, buttonId, filenameId) {
            const saveBtn = document.getElementById(buttonId);
            const filenameDiv = document.getElementById(filenameId);
            if (!saveBtn || !filenameDiv) return;

            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                saveBtn.style.opacity = '1';
                saveBtn.style.pointerEvents = 'auto';
                filenameDiv.textContent = `Selected: ${fileName}`;
                filenameDiv.style.display = 'block';
            } else {
                saveBtn.style.opacity = '0';
                saveBtn.style.pointerEvents = 'none';
                if (!filenameDiv.textContent.trim()) {
                    filenameDiv.style.display = 'none';
                }
            }
        }
    </script>

    <div id="lpmcApprovalModal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 24px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); max-width: 420px; width: 90%;">
            <h3 id="lpmcApprovalTitle" style="margin: 0 0 12px 0; color: #111827; font-size: 18px; font-weight: 600;">Approve Document</h3>
            <form id="lpmcApprovalForm" method="POST">
                @csrf
                <input type="hidden" name="action" id="lpmcApprovalAction">
                <textarea id="lpmcApprovalRemarks" name="remarks" placeholder="Enter remarks (required for return)..." style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 120px;"></textarea>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
                    <button type="button" onclick="closeLpmcApprovalModal()" style="padding: 10px 16px; background-color: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Cancel</button>
                    <button type="submit" id="lpmcApprovalSubmit" style="padding: 10px 16px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLpmcApprovalModal(docId, action) {
            const modal = document.getElementById('lpmcApprovalModal');
            const form = document.getElementById('lpmcApprovalForm');
            const title = document.getElementById('lpmcApprovalTitle');
            const actionInput = document.getElementById('lpmcApprovalAction');
            const remarks = document.getElementById('lpmcApprovalRemarks');
            const submitBtn = document.getElementById('lpmcApprovalSubmit');

            form.action = '{{ url("/local-project-monitoring-committee") }}/{{ $officeName }}/approve/' + docId;
            actionInput.value = action;
            remarks.value = '';

            if (action === 'return') {
                title.textContent = 'Return Document';
                submitBtn.style.backgroundColor = '#dc2626';
                remarks.required = true;
            } else {
                title.textContent = 'Approve Document';
                submitBtn.style.backgroundColor = '#10b981';
                remarks.required = false;
            }

            modal.style.display = 'block';
        }

        function closeLpmcApprovalModal() {
            document.getElementById('lpmcApprovalModal').style.display = 'none';
        }

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('lpmcApprovalModal');
            if (event.target === modal) {
                closeLpmcApprovalModal();
            }
        });
    </script>
@endsection
