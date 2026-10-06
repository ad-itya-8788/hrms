@extends('layouts.portal')

@section('hide_sidebar', '1')

@section('content')
    @php
        $nonce = function_exists('csp_nonce') ? csp_nonce() : null;
        $experienceRows = old('experience', $employee ? $employee->experiences->toArray() : [[]]);
        if (count($experienceRows) === 0) {
            $experienceRows = [[]];
        }
        $educationRows = old('education', ($employee && method_exists($employee, 'educations')) ? $employee->educations->toArray() : [[]]);
        if (count($educationRows) === 0) {
            $educationRows = [[]];
        }
        $requiredDocumentLabels = [
            'photo' => 'Photo',
            'aadhaar' => 'Aadhaar card',
            'bank_passbook' => 'Bank passbook',
        ];
        $documentAliases = [
            'photo' => ['photo', 'employee photo', 'photograph', 'profile photo'],
            'aadhaar' => ['aadhaar', 'aadhaar card', 'aadhar', 'aadhar card'],
            'bank_passbook' => ['bank passbook', 'passbook', 'bank passbook copy'],
        ];
        $existingDocumentTitles = $employee
            ? $employee->documents
                ->pluck('title')
                ->map(function ($title) {
                    return strtolower(trim($title));
                })
                ->all()
            : [];
        $existingRequiredDocuments = [];
        foreach ($documentAliases as $type => $aliases) {
            $existingRequiredDocuments[$type] = count(array_intersect($aliases, $existingDocumentTitles)) > 0;
        }
        $dateValue = function ($value) {
            return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
        };
        $formAction = $employee
            ? route('portal.data.employees.onboarding.update', $employee)
            : route('portal.data.employees.onboarding.store');
    @endphp

    <style @if ($nonce) nonce="{{ $nonce }}" @endif>
        :root {
            --ob-ink: #000;
            --ob-sub: #4b5563;
            --ob-border: #d5dae1;
            --ob-border-strong: #aeb6c2;
            --ob-soft: #f1f3f6;
            --ob-bg: #f6f7f9;
            --ob-accent: #2563eb;
            --ob-accent-dark: #1d4ed8;
            --ob-accent-soft: #e8f0fe;
            --ob-danger: #b91c1c;
            --ob-ok: #166534;
        }

        /* Remove any sidebar the layout might still render and use the full width */
        aside,
        .sidebar,
        .portal-sidebar,
        .app-sidebar,
        #sidebar,
        [data-sidebar] {
            display: none !important;
        }

        .main-content,
        .portal-main,
        .portal-content,
        main {
            margin-left: 0 !important;
            padding-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .onboarding-form,
        .onboarding-form * {
            box-sizing: border-box;
        }

        .onboarding-form {
            max-width: 1080px;
            margin: 0 auto;
            padding: 24px 16px 56px;
            color: var(--ob-ink);
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
        }

        /* Top bar */
        .ob-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 22px 26px;
            margin-bottom: 18px;
            background: #fff;
            border: 1px solid var(--ob-border);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05);
        }

        .ob-topbar h1 {
            margin: 0 0 4px;
            font-size: 24px;
            font-weight: 800;
            color: var(--ob-ink);
            line-height: 1.25;
        }

        .ob-topbar p {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--ob-sub);
        }

        .ob-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 1px solid var(--ob-border-strong);
            border-radius: 9px;
            background: #fff;
            color: var(--ob-ink) !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none !important;
        }

        .ob-back:hover {
            background: var(--ob-soft);
        }

        /* Sections */
        .ob-section {
            margin-bottom: 18px;
            padding: 26px;
            background: #fff;
            border: 1px solid var(--ob-border);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05);
        }

        .ob-section-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--ob-border);
        }

        .ob-step {
            display: grid;
            flex: 0 0 auto;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--ob-accent-soft);
            color: var(--ob-accent-dark);
            font-size: 15px;
            font-weight: 800;
        }

        .ob-section-head h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: var(--ob-ink);
        }

        .ob-section-head p {
            margin: 3px 0 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--ob-sub);
        }

        .ob-tag {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 10px;
            border-radius: 99px;
            background: var(--ob-soft);
            font-size: 11px;
            font-weight: 700;
            color: var(--ob-sub);
            vertical-align: middle;
        }

        /* Fields */
        .ob-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 24px;
        }

        .ob-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            min-width: 0;
            margin: 0;
        }

        .ob-field.wide {
            grid-column: 1 / -1;
        }

        .ob-label {
            font-size: 13px;
            font-weight: 800;
            color: var(--ob-ink);
        }

        .req {
            color: var(--ob-danger);
            margin-left: 3px;
        }

        .ob-field input,
        .ob-field select,
        .ob-field textarea {
            width: 100%;
            height: 44px;
            padding: 0 13px;
            border: 1px solid var(--ob-border-strong);
            border-radius: 9px;
            background: #fff;
            color: var(--ob-ink);
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 1px 1px rgba(16, 24, 40, .04);
        }

        .ob-field select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 38px;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .ob-field textarea {
            height: auto;
            min-height: 100px;
            padding: 11px 13px;
            resize: vertical;
            line-height: 1.5;
        }

        .ob-field input::placeholder,
        .ob-field textarea::placeholder {
            color: #8a93a1;
            font-weight: 500;
            opacity: 1;
        }

        .ob-field input:hover,
        .ob-field select:hover,
        .ob-field textarea:hover {
            border-color: #7c8696;
        }

        .ob-field input:focus,
        .ob-field select:focus,
        .ob-field textarea:focus {
            outline: 0;
            border-color: var(--ob-accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .16);
        }

        .ob-validation-summary {
            margin: 0 0 18px;
            padding: 16px 18px;
            border: 1px solid #fca5a5;
            border-left: 5px solid #dc2626;
            border-radius: 10px;
            background: #fff1f2;
            color: #7f1d1d;
        }

        .ob-validation-summary strong {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .ob-validation-summary ul {
            margin: 0;
            padding-left: 21px;
        }

        .ob-validation-summary li {
            margin-top: 4px;
            font-size: 13px;
        }

        .ob-validation-summary a {
            color: #991b1b;
            font-weight: 800;
            text-decoration: underline;
        }

        .ob-field.is-invalid input,
        .ob-field.is-invalid select,
        .ob-field.is-invalid textarea {
            border-color: #dc2626;
            background-color: #fff7f7;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
        }

        .ob-field-error {
            display: block;
            margin-top: 2px;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.4;
        }

        .ob-field input[type=file] {
            height: auto;
            padding: 7px;
            background: var(--ob-bg);
            border-style: dashed;
            cursor: pointer;
        }

        .ob-field input[type=file]::file-selector-button {
            margin-right: 12px;
            padding: 8px 14px;
            border: 1px solid var(--ob-border-strong);
            border-radius: 7px;
            background: #fff;
            color: var(--ob-ink);
            font-weight: 700;
            cursor: pointer;
        }

        .ob-field.document-upload {
            padding: 16px;
            border: 1px solid var(--upload-border, #bfdbfe);
            border-top: 4px solid var(--upload-accent, #2563eb);
            border-radius: 12px;
            background: var(--upload-bg, #eff6ff);
            box-shadow: 0 4px 14px rgba(31, 41, 55, .05);
        }

        .document-photo {
            --upload-accent: #7c3aed;
            --upload-border: #ddd6fe;
            --upload-bg: #f5f3ff;
        }

        .document-aadhaar {
            --upload-accent: #0f766e;
            --upload-border: #99f6e4;
            --upload-bg: #f0fdfa;
        }

        .document-bank_passbook {
            --upload-accent: #c2410c;
            --upload-border: #fed7aa;
            --upload-bg: #fff7ed;
        }

        .ob-upload-preview {
            display: none;
            align-items: center;
            gap: 14px;
            min-width: 0;
            padding: 11px;
            border: 1px solid var(--upload-border, #bfdbfe);
            border-radius: 10px;
            background: rgba(255, 255, 255, .82);
        }

        .ob-upload-preview.is-visible {
            display: flex;
        }

        .ob-upload-preview img {
            display: block;
            width: 88px;
            height: 72px;
            flex: 0 0 auto;
            border-radius: 7px;
            background: #e5e7eb;
            object-fit: cover;
        }

        .ob-upload-file-icon {
            display: grid;
            width: 58px;
            height: 62px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid #c7d2fe;
            border-radius: 9px;
            background: linear-gradient(145deg, #eef2ff, #dbeafe);
            color: #3730a3;
            font-size: 11px;
            font-weight: 900;
        }

        .ob-upload-preview-meta {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .ob-upload-preview-meta strong {
            overflow-wrap: anywhere;
            color: var(--ob-ink);
            font-size: 13px;
        }

        .ob-upload-preview-meta small {
            color: var(--ob-sub);
            font-size: 11px;
            font-weight: 600;
        }

        .ob-image-preview {
            display: block;
            max-width: min(100%, 440px);
            max-height: 320px;
            margin-top: 12px;
            border: 3px solid #fff;
            border-radius: 10px;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .2);
            object-fit: contain;
        }

        .ob-hint {
            font-size: 12px;
            font-weight: 600;
            color: var(--ob-sub);
        }

        .ob-badge-ok {
            margin-left: 8px;
            padding: 2px 9px;
            border-radius: 99px;
            background: #dcfce7;
            color: var(--ob-ok);
            font-size: 11px;
            font-weight: 800;
        }

        .ob-note {
            margin: 0 0 18px;
            padding: 11px 14px;
            border-radius: 9px;
            background: var(--ob-accent-soft);
            font-size: 13px;
            font-weight: 700;
            color: var(--ob-ink);
        }

        /* Repeaters */
        .ob-repeat-list {
            display: grid;
            gap: 14px;
            margin-bottom: 14px;
        }

        .ob-repeat-card {
            position: relative;
            padding: 20px;
            border: 1px solid var(--ob-border);
            border-radius: 12px;
            background: var(--ob-bg);
        }

        /* Education entry + table */
        .education-entry-panel {
            padding: 20px;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            margin-bottom: 20px;
        }
        .education-entry-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }
        .education-entry-title h3 { margin: 0; font-size: 15px; font-weight: 800; color: #111827; }
        .education-entry-title span { font-size: 12px; font-weight: 700; color: #64748b; }
        .education-add-btn { margin-top: 18px; background: #07833f; border-color: #07833f; color: #fff; font-weight: 800; padding: 12px 22px; }
        .education-add-btn:hover { background: #075d32; border-color: #075d32; color: #fff; }
        .education-table-wrap { margin-top: 20px; border: 1px solid #dbe3ef; border-radius: 14px; overflow: auto; background: #fff; }
        .education-table-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; background: linear-gradient(135deg, #075d32, #07833f); color: #fff; }
        .education-table-head h3 { margin: 0; font-size: 15px; font-weight: 800; }
        .education-count { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 8px; border-radius: 999px; background: rgba(255,255,255,.18); font-size: 12px; font-weight: 900; }
        .education-table { width: 100%; min-width: 920px; border-collapse: collapse; }
        .education-table th { padding: 12px 14px; background: #f1f5f9; color: #334155; border-bottom: 1px solid #dbe3ef; text-align: left; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
        .education-table td { padding: 12px 14px; border-bottom: 1px solid #e5e7eb; color: #1f2937; font-size: 13px; font-weight: 600; vertical-align: middle; }
        .education-table tbody tr:last-child td { border-bottom: 0; }
        .education-table tbody tr:hover { background: #f8fafc; }
        .education-level-badge { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; background: #e8f0fe; color: #1d4ed8; font-size: 11px; font-weight: 900; white-space: nowrap; }
        .education-file-preview { display: inline-flex; align-items: center; gap: 8px; max-width: 180px; color: #2563eb; font-size: 12px; font-weight: 800; }
        .education-file-preview img { width: 42px; height: 42px; object-fit: cover; border-radius: 7px; border: 1px solid #dbe3ef; }
        .education-file-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 7px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; font-size: 9px; font-weight: 900; }
        .education-row-actions { display: flex; gap: 7px; white-space: nowrap; }
        .education-action { border: 1px solid #d1d5db; background: #fff; border-radius: 7px; padding: 6px 9px; font-size: 11px; font-weight: 800; cursor: pointer; }
        .education-action.remove { color: #b91c1c; border-color: #fecaca; }
        .education-empty { padding: 28px 18px; text-align: center; color: #64748b; font-size: 13px; font-weight: 700; }
        .education-hidden-storage { display: none !important; }
        .education-hidden-row { display: none !important; }
        @media (max-width: 720px) {
            .education-entry-title { align-items: flex-start; flex-direction: column; }
        }

        .ob-repeat-card .ob-fields {
            padding-right: 0;
        }

        .ob-new-row {
            padding-top: 54px;
        }

        .ob-remove {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 6px 12px;
            border: 1px solid #f1b8b8;
            border-radius: 7px;
            background: #fff;
            color: var(--ob-danger);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .ob-remove:hover {
            background: #fef2f2;
        }

        .ob-existing-docs {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
            padding: 14px 16px;
            border: 1px solid var(--ob-border);
            border-radius: 10px;
            background: var(--ob-bg);
        }

        .ob-existing-docs h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
        }

        .ob-existing-docs a {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            color: var(--ob-accent-dark);
            font-size: 14px;
            font-weight: 700;
        }

        .ob-existing-docs small {
            color: var(--ob-sub);
            font-weight: 600;
        }

        /* Buttons */
        .ob-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 22px;
            border: 1px solid var(--ob-border-strong);
            border-radius: 9px;
            background: #fff;
            color: var(--ob-ink);
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .06);
        }

        .ob-btn:hover {
            background: var(--ob-soft);
        }

        .ob-btn.primary {
            border-color: var(--ob-accent);
            background: var(--ob-accent);
            color: #fff;
        }

        .ob-btn.primary:hover {
            background: var(--ob-accent-dark);
            border-color: var(--ob-accent-dark);
        }

        .ob-btn:focus-visible,
        .ob-back:focus-visible,
        .ob-remove:focus-visible {
            outline: 3px solid rgba(37, 99, 235, .45);
            outline-offset: 2px;
        }

        .ob-btn[disabled] {
            opacity: .6;
            cursor: wait;
        }

        .ob-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        /* Preview */
        .ob-preview[hidden],
        .ob-editable[hidden] {
            display: none;
        }

        .ob-sheet {
            padding: 38px;
            background: linear-gradient(180deg, #f8fbff 0, #fff 220px);
            border: 1px solid #bfdbfe;
            border-top: 8px solid #2563eb;
            border-radius: 14px;
            color: var(--ob-ink);
            box-shadow: 0 12px 32px rgba(37, 99, 235, .12);
        }

        .ob-sheet-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 24px;
            margin-bottom: 18px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(120deg, #0f766e, #2563eb 55%, #7c3aed);
            color: #fff;
        }

        .ob-sheet-head h1 {
            margin: 0 0 6px;
            font-size: 26px;
            font-weight: 800;
        }

        .ob-sheet-head p {
            margin: 0 0 3px;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255, 255, 255, .88);
        }

        .ob-sheet-photo {
            width: 108px;
            height: 132px;
            flex: 0 0 auto;
            border: 3px solid rgba(255, 255, 255, .9);
            border-radius: 10px;
            object-fit: cover;
        }

        .ob-sheet-photo-empty {
            display: grid;
            place-items: center;
            width: 108px;
            height: 132px;
            flex: 0 0 auto;
            border: 1px dashed rgba(255, 255, 255, .8);
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            text-align: center;
            padding: 8px;
        }

        .ob-pv-section {
            --preview-accent: #2563eb;
            margin-top: 18px;
            padding: 18px;
            border: 1px solid #bfdbfe;
            border-top: 5px solid var(--preview-accent);
            border-radius: 12px;
            background: #eff6ff;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .ob-pv-section:nth-child(6n + 2) {
            --preview-accent: #7c3aed;
            border-color: #ddd6fe;
            background: #f5f3ff;
        }

        .ob-pv-section:nth-child(6n + 3) {
            --preview-accent: #0f766e;
            border-color: #99f6e4;
            background: #f0fdfa;
        }

        .ob-pv-section:nth-child(6n + 4) {
            --preview-accent: #c2410c;
            border-color: #fed7aa;
            background: #fff7ed;
        }

        .ob-pv-section:nth-child(6n + 5) {
            --preview-accent: #be185d;
            border-color: #fbcfe8;
            background: #fdf2f8;
        }

        .ob-pv-section:nth-child(6n) {
            --preview-accent: #15803d;
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .ob-pv-section h2 {
            margin: 0 0 12px;
            padding: 0 0 0 12px;
            border-left: 4px solid var(--ob-accent);
            font-size: 16px;
            font-weight: 800;
            color: var(--ob-ink);
            line-height: 1.3;
        }

        .ob-pv-sub {
            margin: 14px 0 8px;
            font-size: 13px;
            font-weight: 800;
            color: var(--ob-sub);
        }

        .ob-pv-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1px;
            margin: 0;
            border: 1px solid color-mix(in srgb, var(--preview-accent) 28%, white);
            border-radius: 10px;
            background: color-mix(in srgb, var(--preview-accent) 22%, white);
            overflow: hidden;
        }

        .ob-pv-list>div {
            padding: 11px 14px;
            min-width: 0;
            background: color-mix(in srgb, var(--preview-accent) 5%, white);
        }

        .ob-pv-list>div:nth-child(even) {
            background: color-mix(in srgb, var(--preview-accent) 10%, white);
        }

        .ob-pv-list dt {
            margin: 0 0 3px;
            font-size: 12px;
            font-weight: 700;
            color: var(--ob-sub);
        }

        .ob-pv-list dd {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: var(--ob-ink);
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .ob-pv-list .full {
            grid-column: 1 / -1;
        }

        .ob-preview-bar {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: 20px;
            padding: 14px 0;
            background: linear-gradient(to top, #fff 70%, rgba(255, 255, 255, 0));
        }

        .ob-print-tip {
            margin: 6px 0 0;
            text-align: right;
            font-size: 12px;
            font-weight: 600;
            color: var(--ob-sub);
        }

        @media (max-width: 720px) {

            .ob-section,
            .ob-sheet {
                padding: 18px;
            }

            .ob-fields,
            .ob-pv-list {
                grid-template-columns: 1fr;
            }

            .ob-field.wide,
            .ob-pv-list .full {
                grid-column: auto;
            }

            .ob-topbar h1 {
                font-size: 21px;
            }

            .ob-actions .ob-btn {
                flex: 1 1 100%;
            }
        }

        @media (prefers-reduced-motion: no-preference) {

            .ob-field input,
            .ob-field select,
            .ob-field textarea,
            .ob-btn,
            .ob-back {
                transition: border-color .15s, box-shadow .15s, background .15s;
            }
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden !important;
            }

            #onboarding-sheet,
            #onboarding-sheet * {
                visibility: visible !important;
            }

            #onboarding-sheet {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                border: 0;
                box-shadow: none;
            }
        }
    </style>

    <form class="onboarding-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data"
        data-existing-bank="{{ !empty($bank['account_last_four']) ? '1' : '0' }}"
        data-employee-code-url="{{ route('portal.data.employees.code') }}">
        @csrf
        @if ($employee)
            @method('PUT')
        @endif

        {{-- ===================== EDIT MODE ===================== --}}
        <div class="ob-editable" id="onboarding-editable">
            @if ($errors->any())
                <div class="ob-validation-summary" role="alert" aria-labelledby="onboarding-error-title">
                    <strong id="onboarding-error-title">Please correct the following fields:</strong>
                    <ul>
                        @foreach ($errors->getMessages() as $field => $messages)
                            @foreach ($messages as $message)
                                <li>
                                    <a href="#ob-field-{{ trim(preg_replace('/[^a-z0-9]+/i', '-', $field), '-') }}">{{ ucwords(str_replace(['.', '_', '[', ']'], [' ', ' ', ' ', ''], $field)) }}</a>:
                                    {{ $message }}
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif
            <header class="ob-topbar">
                <div>
                    <h1>{{ $pageTitle }}</h1>
                    <p>{{ $employee ? 'Update employment, personal and onboarding details.' : 'Add an employee and capture their onboarding details.' }}
                    </p>
                </div>
                <a class="ob-back" href="{{ route('portal.employees.index') }}">&larr; Go back</a>
            </header>

            {{-- 01 Personal --}}
            <section class="ob-section" data-title="Personal details">
                <div class="ob-section-head">
                    <span class="ob-step">1</span>
                    <div>
                        <h2>Personal details</h2>
                        <p>Basic identity and contact information.</p>
                    </div>
                </div>
                <div class="ob-fields">
                    <label class="ob-field"><span class="ob-label">First name<span class="req">*</span></span>
                        <input name="first_name" maxlength="80" placeholder="e.g. Priya" autocomplete="off"
                            value="{{ old('first_name', data_get($employee, 'first_name')) }}" required>
                    </label>
                    <label class="ob-field"><span class="ob-label">Last name<span class="req">*</span></span>
                        <input name="last_name" maxlength="80" placeholder="e.g. Deshmukh" autocomplete="off"
                            value="{{ old('last_name', data_get($employee, 'last_name')) }}" required>
                    </label>
                    <label class="ob-field"><span class="ob-label">Work email<span class="req">*</span></span>
                        <input name="email" type="email" maxlength="190" placeholder="name@company.com"
                            autocomplete="off" value="{{ old('email', data_get($employee, 'email')) }}" required>
                    </label>
                    <label class="ob-field"><span class="ob-label">Phone</span>
                        <input name="phone" type="tel" maxlength="20" placeholder="e.g. +91 98765 43210"
                            inputmode="tel" autocomplete="tel" pattern="\+?[0-9][0-9 ().-]{5,18}[0-9]"
                            title="Enter a phone number with 7–15 digits. You may use +, spaces, parentheses, dots or hyphens."
                            value="{{ old('phone', data_get($employee, 'phone')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Gender</span>
                        <select name="gender">
                            <option value="">Select gender</option>
                            @foreach (['female' => 'Female', 'male' => 'Male', 'non_binary' => 'Non-binary', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('gender', data_get($employee, 'gender')) === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ob-field"><span class="ob-label">Date of birth</span>
                        <input name="date_of_birth" type="date"
                            max="{{ now()->toDateString() }}"
                            value="{{ old('date_of_birth', $dateValue(data_get($employee, 'date_of_birth'))) }}">
                    </label>
                    <label class="ob-field wide"><span class="ob-label">Home address</span>
                        <input name="address_line" maxlength="190" placeholder="Flat / house no., building, street, area"
                            value="{{ old('address_line', data_get($employee, 'address_line')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">City</span>
                        <input name="city" maxlength="80" placeholder="e.g. Pune"
                            value="{{ old('city', data_get($employee, 'city', 'Pune')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">State</span>
                        <input name="state" maxlength="80" placeholder="e.g. Maharashtra"
                            value="{{ old('state', data_get($employee, 'state', 'Maharashtra')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Postal code</span>
                        <input name="postal_code" type="number" min="100000" max="999999" step="1"
                            inputmode="numeric" title="Enter a valid 6-digit Indian PIN code."
                            placeholder="e.g. 411001"
                            value="{{ old('postal_code', data_get($employee, 'postal_code')) }}">
                    </label>
                </div>
            </section>

            {{-- 02 Employment --}}
            <section class="ob-section" data-title="Employment details">
                <div class="ob-section-head">
                    <span class="ob-step">2</span>
                    <div>
                        <h2>Employment details</h2>
                        <p>Employee ID, role and reporting information.</p>
                    </div>
                </div>
                <div class="ob-fields">
                    <label class="ob-field"><span class="ob-label">Department<span class="req">*</span></span>
                        <select name="department_id" id="onboarding-department"
                            data-original-department-id="{{ data_get($employee, 'department_id') }}" required>
                            <option value="">Select department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ (string) old('department_id', data_get($employee, 'department_id')) === (string) $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ob-field" id="employee-code-field">
                        <span class="ob-label">Employee ID<span class="req">*</span></span>
                        <input name="employee_code" id="onboarding-employee-code" maxlength="20"
                            placeholder="Select a department first"
                            value="{{ old('employee_code', data_get($employee, 'employee_code')) }}"
                            aria-describedby="employee-code-help" readonly required>
                        <span class="ob-hint" id="employee-code-help" role="status">
                            {{ $employee ? 'Generated from the selected department. It cannot be edited.' : 'Select a department to generate this ID automatically.' }}
                        </span>
                    </label>
                    <label class="ob-field"><span class="ob-label">Joining date<span class="req">*</span></span>
                        <input name="joining_date" type="date"
                            value="{{ old('joining_date', $dateValue(data_get($employee, 'joining_date'))) }}" required>
                    </label>
                    <label class="ob-field"><span class="ob-label">Employee type<span class="req">*</span></span>
                        <select name="employee_type_id" required>
                            <option value="">Select type</option>
                            @foreach ($employeeTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ (string) old('employee_type_id', data_get($employee, 'employee_type_id')) === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ob-field"><span class="ob-label">Employee role<span class="req">*</span></span>
                        <select name="employee_role_id" required>
                            <option value="">Select role</option>
                            @foreach ($employeeRoles as $role)
                                <option value="{{ $role->id }}"
                                    {{ (string) old('employee_role_id', data_get($employee, 'employee_role_id')) === (string) $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ob-field"><span class="ob-label">Manager</span>
                        <select name="manager_id">
                            <option value="">No manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ (string) old('manager_id', data_get($employee, 'manager_id')) === (string) $manager->id ? 'selected' : '' }}>
                                    {{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ob-field"><span class="ob-label">Employment status<span class="req">*</span></span>
                        <select name="employment_status" required>
                            @foreach (['active' => 'Active', 'on_leave' => 'On leave', 'notice_period' => 'Notice period', 'inactive' => 'Inactive'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('employment_status', data_get($employee, 'employment_status', 'active')) === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            {{-- 03 Emergency --}}
            <section class="ob-section" data-title="Emergency contact">
                <div class="ob-section-head">
                    <span class="ob-step">3</span>
                    <div>
                        <h2>Emergency contact <span class="ob-tag">Optional</span></h2>
                        <p>Who we should call in an emergency.</p>
                    </div>
                </div>
                <div class="ob-fields">
                    <label class="ob-field"><span class="ob-label">Contact name</span>
                        <input name="emergency_contact_name" maxlength="120" placeholder="Full name"
                            value="{{ old('emergency_contact_name', data_get($employee, 'emergency_contact_name')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Relationship</span>
                        <input name="emergency_contact_relationship" maxlength="60"
                            placeholder="e.g. Spouse, Father, Sibling"
                            value="{{ old('emergency_contact_relationship', data_get($employee, 'emergency_contact_relationship')) }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Contact phone</span>
                        <input name="emergency_contact_phone" type="tel" maxlength="20"
                            inputmode="tel" autocomplete="tel" pattern="\+?[0-9][0-9 ().-]{5,18}[0-9]"
                            title="Enter a phone number with 7–15 digits. You may use +, spaces, parentheses, dots or hyphens."
                            placeholder="e.g. +91 98765 43210"
                            value="{{ old('emergency_contact_phone', data_get($employee, 'emergency_contact_phone')) }}">
                    </label>
                </div>
            </section>

            {{-- 04 Bank --}}
            <section class="ob-section" data-title="Bank details">
                <div class="ob-section-head">
                    <span class="ob-step">4</span>
                    <div>
                        <h2>Bank details <span class="ob-tag">Optional</span></h2>
                        <p>Stored securely and used only for payroll.</p>
                    </div>
                </div>
                @if (!empty($bank['account_last_four']))
                    <p class="ob-note">Existing account ending in {{ $bank['account_last_four'] }}. Leave the account
                        number blank to keep it unchanged.</p>
                @endif
                <div class="ob-fields">
                    <label class="ob-field"><span class="ob-label">Account holder</span>
                        <input name="bank[account_holder]" maxlength="120" placeholder="Name as on bank account"
                            data-bank-core="account_holder" data-bank-info
                            autocomplete="off"
                            value="{{ old('bank.account_holder', $bank['account_holder'] ?? '') }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Account number</span>
                        <input name="bank[account_number]" type="text" minlength="9" maxlength="18"
                            pattern="[0-9]{9,18}" inputmode="numeric" autocomplete="off"
                            data-bank-account-number
                            title="Enter 9–18 digits without spaces or separators." placeholder="Enter account number">
                    </label>
                    <label class="ob-field"><span class="ob-label">Bank name</span>
                        <input name="bank[bank_name]" maxlength="120" placeholder="e.g. State Bank of India"
                            data-bank-core="bank_name" data-bank-info
                            value="{{ old('bank.bank_name', $bank['bank_name'] ?? '') }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">IFSC code</span>
                        <input name="bank[ifsc_code]" minlength="11" maxlength="11"
                            pattern="[A-Za-z]{4}0[A-Za-z0-9]{6}" autocapitalize="characters"
                            data-bank-core="ifsc_code" data-bank-info
                            title="Enter a valid 11-character IFSC code: 4 letters, 0, then 6 letters or numbers."
                            placeholder="e.g. SBIN0001234" style="text-transform:uppercase"
                            value="{{ old('bank.ifsc_code', $bank['ifsc_code'] ?? '') }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Branch</span>
                        <input name="bank[branch]" maxlength="120" placeholder="e.g. Shivajinagar, Pune"
                            data-bank-info
                            value="{{ old('bank.branch', $bank['branch'] ?? '') }}">
                    </label>
                    <label class="ob-field"><span class="ob-label">Account type</span>
                        <select name="bank[account_type]" data-bank-info>
                            <option value="">Select account type</option>
                            @foreach (['savings' => 'Savings', 'current' => 'Current', 'salary' => 'Salary', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('bank.account_type', $bank['account_type'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            {{-- 05 Education --}}
            <section class="ob-section" data-title="Education details">
                <div class="ob-section-head">
                    <span class="ob-step">5</span>
                    <div>
                        <h2>Education details</h2>
                        <p>Add one qualification at a time. After you click <strong>Add qualification</strong>, it will appear in the table below.</p>
                    </div>
                </div>

                <div class="education-entry-panel" id="education-entry-panel">
                    <div class="education-entry-title">
                        <h3>Add education qualification</h3>
                        <span>Fill all required fields, choose the certificate/marksheet, then click Add.</span>
                    </div>

                    <div class="ob-fields">
                        <label class="ob-field">
                            <span class="ob-label">Level<span class="req">*</span></span>
                            <select id="education-entry-level">
                                <option value="">Select level</option>
                                <option value="10th">10th (SSC)</option>
                                <option value="12th">12th (HSC)</option>
                                <option value="diploma">Diploma</option>
                                <option value="bachelors">Bachelor's degree</option>
                                <option value="masters">Master's / Post Graduation</option>
                                <option value="doctorate">Doctorate / PhD</option>
                                <option value="other">Other</option>
                            </select>
                        </label>

                        <label class="ob-field">
                            <span class="ob-label">Course / degree<span class="req">*</span></span>
                            <input id="education-entry-degree" type="text" maxlength="120" placeholder="e.g. BCA, MCA, B.E. Computer Engineering">
                        </label>

                        <label class="ob-field">
                            <span class="ob-label">School / college<span class="req">*</span></span>
                            <input id="education-entry-institution" type="text" maxlength="150" placeholder="e.g. MES IMCC, Pune">
                        </label>

                        <label class="ob-field">
                            <span class="ob-label">University / board</span>
                            <input id="education-entry-board" type="text" maxlength="150" placeholder="e.g. Savitribai Phule Pune University">
                        </label>

                        <label class="ob-field">
                            <span class="ob-label">Year of passing<span class="req">*</span></span>
                            <input id="education-entry-year" type="number" min="1950" max="{{ now()->year }}" step="1" placeholder="e.g. 2026">
                        </label>

                        <label class="ob-field">
                            <span class="ob-label">Percentage / CGPA</span>
                            <input id="education-entry-grade" type="text" maxlength="20" placeholder="e.g. 82.5% or 8.4 CGPA">
                        </label>

                        <label class="ob-field wide document-upload">
                            <span class="ob-label">Certificate / marksheet<span class="req">*</span></span>
                            <input id="education-entry-certificate" type="file"
                                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                data-max-size="10485760">
                            <div class="ob-upload-preview" data-upload-preview></div>
                            <span class="ob-hint">PDF, JPG or PNG, maximum 10 MB.</span>
                        </label>
                    </div>

                    <button class="ob-btn education-add-btn" type="button" id="add-education-btn">
                        + Add qualification
                    </button>
                </div>

                <div class="education-table-wrap">
                    <div class="education-table-head">
                        <h3>Added education qualifications</h3>
                        <span class="education-count" id="education-count">0</span>
                    </div>
                    <table class="education-table">
                        <thead>
                            <tr>
                                <th>Qualification</th>
                                <th>Course / Degree</th>
                                <th>School / College</th>
                                <th>Board / University</th>
                                <th>Year</th>
                                <th>Grade</th>
                                <th>Certificate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="education-table-body"></tbody>
                    </table>
                    <div class="education-empty" id="education-empty">
                        No education qualification added yet. Fill the form above and click <strong>Add qualification</strong>.
                    </div>
                </div>

                {{-- These hidden rows are the actual education fields submitted with the main onboarding form. --}}
                <input type="hidden" name="education_submitted" value="1">
                <div class="education-hidden-storage" id="education-hidden-storage"></div>
            </section>

            {{-- 06 Experience --}}
            <section class="ob-section" data-title="Previous experience">
                <div class="ob-section-head">
                    <span class="ob-step">6</span>
                    <div>
                        <h2>Previous experience <span class="ob-tag">Optional</span></h2>
                        <p>Add earlier roles that are part of the employee's work history.</p>
                    </div>
                </div>
                <div class="ob-repeat-list" data-repeat-list="experience"
                    data-next-index="{{ count($experienceRows) }}">
                    @foreach ($experienceRows as $index => $experience)
                        <div class="ob-repeat-card">
                            @if (!empty($experience['id']))
                                <input type="hidden" name="experience[{{ $index }}][id]"
                                    value="{{ $experience['id'] }}">
                            @endif
                            <div class="ob-fields">
                                <label class="ob-field"><span class="ob-label">Company name</span>
                                    <input name="experience[{{ $index }}][company_name]" maxlength="120"
                                        placeholder="e.g. Infosys Ltd."
                                        value="{{ old('experience.' . $index . '.company_name', $experience['company_name'] ?? '') }}">
                                </label>
                                <label class="ob-field"><span class="ob-label">Job title</span>
                                    <input name="experience[{{ $index }}][job_title]" maxlength="120"
                                        placeholder="e.g. Software Engineer"
                                        value="{{ old('experience.' . $index . '.job_title', $experience['job_title'] ?? '') }}">
                                </label>
                                <label class="ob-field"><span class="ob-label">Start date</span>
                                    <input name="experience[{{ $index }}][start_date]" type="date"
                                        max="{{ now()->toDateString() }}" data-experience-start
                                        value="{{ old('experience.' . $index . '.start_date', $dateValue($experience['start_date'] ?? null)) }}">
                                </label>
                                <label class="ob-field"><span class="ob-label">End date</span>
                                    <input name="experience[{{ $index }}][end_date]" type="date"
                                        max="{{ now()->toDateString() }}" data-experience-end
                                        value="{{ old('experience.' . $index . '.end_date', $dateValue($experience['end_date'] ?? null)) }}">
                                </label>
                                <label class="ob-field wide"><span class="ob-label">Summary</span>
                                    <textarea name="experience[{{ $index }}][summary]" maxlength="2000"
                                        placeholder="Key responsibilities and achievements in this role">{{ old('experience.' . $index . '.summary', $experience['summary'] ?? '') }}</textarea>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="ob-btn" type="button" data-repeat-add="experience">+ Add previous role</button>
            </section>

            {{-- 07 Required docs --}}
            <section class="ob-section" data-title="Required documents">
                <div class="ob-section-head">
                    <span class="ob-step">7</span>
                    <div>
                        <h2>Required documents</h2>
                        <p>Upload a photo, Aadhaar card and bank passbook. Each file can be up to 10 MB.</p>
                    </div>
                </div>
                <div class="ob-fields">
                    @foreach ($requiredDocumentLabels as $type => $label)
                        <label class="ob-field document-upload document-{{ $type }}">
                            <span class="ob-label">{{ $label }}@if ($existingRequiredDocuments[$type])
                                <span class="ob-badge-ok">Uploaded</span>@else<span class="req">*</span>
                                @endif
                            </span>
                            <input name="required_documents[{{ $type }}]" type="file"
                                accept="{{ $type === 'photo' ? '.jpg,.jpeg,.png,image/jpeg,image/png' : '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png' }}"
                                data-max-size="10485760"
                                {{ $existingRequiredDocuments[$type] ? 'data-existing-upload=1' : 'required' }}>
                            <div class="ob-upload-preview" data-upload-preview></div>
                            <span class="ob-hint">
                                @if ($existingRequiredDocuments[$type])
                                    Existing file is kept unless you upload a replacement.
                                @else
                                    {{ $type === 'photo' ? 'JPG or PNG image, maximum 10 MB.' : 'PDF, JPG or PNG, maximum 10 MB.' }}
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 08 Additional docs --}}
            <section class="ob-section" data-title="Additional documents">
                <div class="ob-section-head">
                    <span class="ob-step">8</span>
                    <div>
                        <h2>Additional documents <span class="ob-tag">Optional</span></h2>
                        <p>Any other documents. PDF, JPG, PNG or Office files up to 10 MB each.</p>
                    </div>
                </div>
                @if ($employee && $employee->documents->isNotEmpty())
                    <div class="ob-existing-docs">
                        <h3>Already uploaded</h3>
                        @foreach ($employee->documents as $document)
                            <a href="{{ route('portal.employee-documents.download', $document) }}">{{ $document->title }}
                                <small>{{ $document->original_name }}</small></a>
                        @endforeach
                    </div>
                @endif
                <div class="ob-repeat-list" data-repeat-list="documents" data-next-index="0"></div>
                <button class="ob-btn" type="button" data-repeat-add="documents">+ Add a document</button>
            </section>

            <div class="ob-actions">
                <button class="ob-btn primary" type="button" data-show-preview>Review employee details</button>
            </div>
        </div>

        {{-- ===================== PREVIEW MODE ===================== --}}
        <section class="ob-preview" id="onboarding-preview" hidden aria-live="polite">
            <div class="ob-sheet" id="onboarding-sheet">
                <div class="ob-sheet-head">
                    <div>
                        <h1 id="pv-name">Employee onboarding</h1>
                        <p id="pv-meta"></p>
                        <p id="pv-date"></p>
                    </div>
                    <div id="pv-photo-slot"></div>
                </div>
                <div id="onboarding-preview-content"></div>
                @if ($employee && $employee->documents->isNotEmpty())
                    <div class="ob-pv-section">
                        <h2>Previously uploaded documents</h2>
                        <dl class="ob-pv-list">
                            @foreach ($employee->documents as $document)
                                <div>
                                    <dt>{{ $document->title }}</dt>
                                    <dd>{{ $document->original_name }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            <div class="ob-preview-bar" data-html2canvas-ignore="true">
                <div class="ob-actions">
                    <button class="ob-btn" type="button" data-edit-details>&larr; Edit details</button>
                    <button class="ob-btn" type="button" data-download-preview>Download PDF</button>
                    <button class="ob-btn primary"
                        type="submit">{{ $employee ? 'Save employee changes' : 'Save onboarding details' }}</button>
                </div>
                <p class="ob-print-tip">If the PDF does not download, the print dialog opens: choose "Save as PDF".</p>
            </div>
        </section>
    </form>

    <template id="experience-row-template">
        <div class="ob-repeat-card ob-new-row">
            <button class="ob-remove" type="button" data-repeat-remove>Remove</button>
            <div class="ob-fields">
                <label class="ob-field"><span class="ob-label">Company name</span><input
                        name="experience[__INDEX__][company_name]" maxlength="120"
                        placeholder="e.g. Infosys Ltd."></label>
                <label class="ob-field"><span class="ob-label">Job title</span><input
                        name="experience[__INDEX__][job_title]" maxlength="120"
                        placeholder="e.g. Software Engineer"></label>
                <label class="ob-field"><span class="ob-label">Start date</span><input
                        name="experience[__INDEX__][start_date]" type="date" max="{{ now()->toDateString() }}"
                        data-experience-start></label>
                <label class="ob-field"><span class="ob-label">End date</span><input
                        name="experience[__INDEX__][end_date]" type="date" max="{{ now()->toDateString() }}"
                        data-experience-end></label>
                <label class="ob-field wide"><span class="ob-label">Summary</span>
                    <textarea name="experience[__INDEX__][summary]" maxlength="2000"
                        placeholder="Key responsibilities and achievements in this role"></textarea>
                </label>
            </div>
        </div>
    </template>
    <template id="document-row-template">
        <div class="ob-repeat-card ob-new-row">
            <button class="ob-remove" type="button" data-repeat-remove>Remove</button>
            <div class="ob-fields">
                <label class="ob-field"><span class="ob-label">Document title<span class="req">*</span></span><input
                        name="documents[__INDEX__][title]" maxlength="100"
                        placeholder="e.g. PAN card, Degree certificate" required></label>
                <label class="ob-field document-upload"><span class="ob-label">Choose file<span class="req">*</span></span><input
                        name="documents[__INDEX__][file]" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        data-max-size="10485760"
                        required>
                    <div class="ob-upload-preview" data-upload-preview></div>
                </label>
            </div>
        </div>
    </template>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        @if ($nonce) nonce="{{ $nonce }}" @endif></script>
    <script @if ($nonce) nonce="{{ $nonce }}" @endif>
        (function() {
            var form = document.querySelector('.onboarding-form');
            var editable = document.getElementById('onboarding-editable');
            var preview = document.getElementById('onboarding-preview');
            var content = document.getElementById('onboarding-preview-content');
            var objectUrls = [];
            var formPreviewUrls = [];
            var departmentSelect = document.getElementById('onboarding-department');
            var employeeCodeInput = document.getElementById('onboarding-employee-code');
            var employeeCodeField = document.getElementById('employee-code-field');
            var employeeCodeHelp = document.getElementById('employee-code-help');
            var employeeCodeRequest = 0;
            var serverValidationErrors = @json($errors->getMessages());

            function normalizedFieldName(name) {
                return name.replace(/\[([^\]]+)\]/g, '.$1');
            }

            function fieldErrorId(name) {
                return 'ob-field-' + normalizedFieldName(name).replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
            }

            function showServerValidationErrors() {
                var errorFields = {};
                Object.keys(serverValidationErrors).forEach(function(name) {
                    errorFields[name] = Array.isArray(serverValidationErrors[name])
                        ? serverValidationErrors[name]
                        : [serverValidationErrors[name]];
                });

                form.querySelectorAll('[name]').forEach(function(control) {
                    var key = normalizedFieldName(control.name);
                    var messages = errorFields[key];
                    if (!messages || !messages.length) return;

                    var field = control.closest('.ob-field');
                    if (!field) return;
                    field.id = fieldErrorId(control.name);
                    field.setAttribute('tabindex', '-1');
                    field.classList.add('is-invalid');
                    control.setAttribute('aria-invalid', 'true');

                    var error = document.createElement('small');
                    error.className = 'ob-field-error';
                    error.setAttribute('role', 'alert');
                    error.textContent = messages.join(' ');
                    field.appendChild(error);
                });
            }

            showServerValidationErrors();

            form.addEventListener('input', clearFieldError);
            form.addEventListener('change', clearFieldError);

            function clearFieldError(event) {
                var control = event.target;
                if (!control.name) return;
                var field = control.closest('.ob-field');
                if (!field) return;
                field.classList.remove('is-invalid');
                control.removeAttribute('aria-invalid');
                field.querySelectorAll('.ob-field-error').forEach(function(error) {
                    error.remove();
                });
            }

            function showEmployeeCodeError(message) {
                employeeCodeField.classList.add('is-invalid');
                employeeCodeInput.setCustomValidity(message);
                employeeCodeInput.setAttribute('aria-invalid', 'true');
                employeeCodeHelp.textContent = message;
                employeeCodeHelp.classList.add('ob-field-error');
            }

            function clearEmployeeCodeError() {
                employeeCodeField.classList.remove('is-invalid');
                employeeCodeInput.setCustomValidity('');
                employeeCodeInput.removeAttribute('aria-invalid');
                employeeCodeHelp.classList.remove('ob-field-error');
                employeeCodeHelp.textContent = 'Generated from the selected department. It cannot be edited.';
            }

            function loadEmployeeCode() {
                var requestNumber = ++employeeCodeRequest;
                var departmentId = departmentSelect.value;

                if (!departmentId) {
                    employeeCodeInput.value = '';
                    employeeCodeInput.placeholder = 'Select a department first';
                    employeeCodeInput.disabled = true;
                    employeeCodeHelp.textContent = 'Select a department to generate this ID automatically.';
                    return;
                }

                clearEmployeeCodeError();
                employeeCodeInput.value = '';
                employeeCodeInput.placeholder = 'Generating employee ID…';
                employeeCodeInput.disabled = false;
                employeeCodeHelp.textContent = 'Generating ID from the department employee count…';

                fetch(form.dataset.employeeCodeUrl + '?department_id=' + encodeURIComponent(departmentId), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                }).then(function(response) {
                    if (!response.ok) {
                        return response.json().then(function(payload) {
                            throw new Error(payload.message || 'Could not generate an employee ID.');
                        });
                    }
                    return response.json();
                }).then(function(payload) {
                    if (requestNumber !== employeeCodeRequest) return;
                    if (!payload.data || !payload.data.employee_code) {
                        throw new Error('The selected department did not return an employee ID.');
                    }
                    employeeCodeInput.value = payload.data.employee_code;
                    employeeCodeInput.placeholder = '';
                    employeeCodeInput.disabled = false;
                    clearEmployeeCodeError();
                }).catch(function(error) {
                    if (requestNumber !== employeeCodeRequest) return;
                    employeeCodeInput.disabled = false;
                    employeeCodeInput.placeholder = 'ID could not be generated';
                    showEmployeeCodeError(error.message || 'Could not generate an employee ID. Please retry.');
                });
            }

            departmentSelect.addEventListener('change', loadEmployeeCode);
            var originalDepartmentId = departmentSelect.getAttribute('data-original-department-id');
            if (departmentSelect.value && (!originalDepartmentId || departmentSelect.value !== originalDepartmentId)) {
                loadEmployeeCode();
            }

            function val(name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (!el) return '';
                if (el.tagName === 'SELECT') return el.selectedIndex > -1 && el.value ? el.options[el.selectedIndex]
                    .text : '';
                return el.value.trim();
            }

            function validatePhone(input) {
                var digits = input.value.replace(/\D/g, '');
                input.setCustomValidity(input.value && (digits.length < 7 || digits.length > 15)
                    ? 'Enter a phone number with 7 to 15 digits.'
                    : '');
            }

            var personNamePattern = /^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u;
            var organizationNamePattern = /^[\p{L}\p{M}0-9][\p{L}\p{M}0-9 .,&'()-]*$/u;

            function validateTextPattern(input, pattern, message) {
                input.setCustomValidity(input.value && !pattern.test(input.value)
                    ? message
                    : '');
            }

            function validateNamedFields(root) {
                root.querySelectorAll('[name="first_name"], [name="last_name"], [name="city"], [name="state"], [name="emergency_contact_name"], [name="emergency_contact_relationship"], [name="bank[account_holder]"]').forEach(function(input) {
                    validateTextPattern(input, personNamePattern, 'Use letters, spaces, apostrophes, periods or hyphens only.');
                });
                root.querySelectorAll('[name="bank[bank_name]"], [name="bank[branch]"], [name^="experience["][name$="[company_name]"], [name^="experience["][name$="[job_title]"]').forEach(function(input) {
                    validateTextPattern(input, organizationNamePattern, 'Use letters, numbers, spaces, periods, commas, ampersands, apostrophes, parentheses or hyphens only.');
                });
                root.querySelectorAll('[name^="documents["][name$="[title]"]').forEach(function(input) {
                    validateTextPattern(input, organizationNamePattern, 'Use letters, numbers, spaces, periods, commas, ampersands, apostrophes, parentheses or hyphens only.');
                });
            }

            function validateFileSize(input) {
                var file = input.files && input.files[0];
                var maxSize = Number(input.dataset.maxSize || 0);
                input.setCustomValidity(file && maxSize && file.size > maxSize
                    ? 'Each uploaded file must be 10 MB or smaller.'
                    : '');
            }

            function isImageFile(file) {
                return file && (file.type.indexOf('image/') === 0 || /\.(jpe?g|png|gif|webp|bmp)$/i.test(file.name));
            }

            function renderFilePreview(input) {
                var previewBox = input.closest('.ob-field').querySelector('[data-upload-preview]');
                if (!previewBox) return;

                var previousUrl = input.dataset.previewUrl;
                if (previousUrl) {
                    URL.revokeObjectURL(previousUrl);
                    formPreviewUrls = formPreviewUrls.filter(function(url) {
                        return url !== previousUrl;
                    });
                    delete input.dataset.previewUrl;
                }

                previewBox.textContent = '';
                previewBox.classList.remove('is-visible');
                var file = input.files && input.files[0];
                if (!file) return;

                var meta = document.createElement('span');
                meta.className = 'ob-upload-preview-meta';
                var name = document.createElement('strong');
                name.textContent = file.name;
                var details = document.createElement('small');
                details.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                if (isImageFile(file)) {
                    var image = document.createElement('img');
                    image.alt = input.closest('.ob-field').querySelector('.ob-label').textContent.trim() + ' preview';
                    image.src = URL.createObjectURL(file);
                    input.dataset.previewUrl = image.src;
                    formPreviewUrls.push(image.src);
                    previewBox.appendChild(image);
                    details.textContent += ' · Image preview';
                } else {
                    var icon = document.createElement('span');
                    icon.className = 'ob-upload-file-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.textContent = file.name.split('.').pop().toUpperCase();
                    previewBox.appendChild(icon);
                }

                meta.appendChild(name);
                meta.appendChild(details);
                previewBox.appendChild(meta);
                previewBox.classList.add('is-visible');
            }

            function syncExperienceDateRange(card) {
                var start = card.querySelector('[data-experience-start]');
                var end = card.querySelector('[data-experience-end]');
                if (!start || !end) return;
                end.min = start.value || '';
                end.setCustomValidity(start.value && end.value && end.value < start.value
                    ? 'The end date must be on or after the start date.'
                    : '');
            }

            function syncBankRequirements() {
                var accountNumber = form.querySelector('[data-bank-account-number]');
                var coreFields = form.querySelectorAll('[data-bank-core]');
                var hasExisting = form.dataset.existingBank === '1';
                var anyCoreValue = Array.prototype.some.call(form.querySelectorAll('[data-bank-info]'), function(input) {
                    return input.value.trim() !== '';
                });
                accountNumber.required = !hasExisting && anyCoreValue;
                coreFields.forEach(function(input) {
                    input.required = accountNumber.value !== '';
                });
            }

            validateNamedFields(form);
            form.querySelectorAll('[name="phone"], [name="emergency_contact_phone"]').forEach(function(input) {
                validatePhone(input);
            });

            form.addEventListener('input', function(event) {
                var input = event.target;
                if (input.matches('[name="bank[ifsc_code]"]')) {
                    var cursor = input.selectionStart;
                    input.value = input.value.toUpperCase();
                    input.setSelectionRange(cursor, cursor);
                }
                if (input.matches('[name="phone"], [name="emergency_contact_phone"]')) {
                    validatePhone(input);
                } else if (input.matches('[name="first_name"], [name="last_name"], [name="city"], [name="state"], [name="emergency_contact_name"], [name="emergency_contact_relationship"], [name="bank[account_holder]"]')) {
                    validateTextPattern(input, personNamePattern, 'Use letters, spaces, apostrophes, periods or hyphens only.');
                } else if (input.matches('[name="bank[bank_name]"], [name="bank[branch]"], [name^="experience["][name$="[company_name]"], [name^="experience["][name$="[job_title]"], [name^="documents["][name$="[title]"]')) {
                    validateTextPattern(input, organizationNamePattern, 'Use letters, numbers, spaces, periods, commas, ampersands, apostrophes, parentheses or hyphens only.');
                }
            });

            form.querySelectorAll('[data-max-size]').forEach(function(input) {
                input.addEventListener('change', function() {
                    validateFileSize(input);
                    renderFilePreview(input);
                });
            });

            editable.querySelectorAll('.ob-repeat-card').forEach(syncExperienceDateRange);
            editable.addEventListener('input', function(event) {
                if (event.target.matches('[data-experience-start], [data-experience-end]')) {
                    syncExperienceDateRange(event.target.closest('.ob-repeat-card'));
                }
                if (event.target.matches('[data-bank-core], [data-bank-account-number]')) {
                    syncBankRequirements();
                }
            });
            editable.addEventListener('change', function(event) {
                if (event.target.matches('[data-experience-start], [data-experience-end]')) {
                    syncExperienceDateRange(event.target.closest('.ob-repeat-card'));
                }
                if (event.target.matches('[data-bank-core], [data-bank-account-number]')) {
                    syncBankRequirements();
                }
                if (event.target.matches('[data-max-size]')) {
                    validateFileSize(event.target);
                }
            });
            syncBankRequirements();

            /* ---------- education: add one entry -> show it in table -> keep it in form submission ---------- */
            var educationEntry = {
                level: document.getElementById('education-entry-level'),
                degree: document.getElementById('education-entry-degree'),
                institution: document.getElementById('education-entry-institution'),
                board: document.getElementById('education-entry-board'),
                year: document.getElementById('education-entry-year'),
                grade: document.getElementById('education-entry-grade'),
                certificate: document.getElementById('education-entry-certificate')
            };
            var educationTableBody = document.getElementById('education-table-body');
            var educationEmpty = document.getElementById('education-empty');
            var educationCount = document.getElementById('education-count');
            var educationStorage = document.getElementById('education-hidden-storage');
            var educationNextIndex = 0;
            var educationRowsFromServer = @json($educationRows);
            var educationFileUrls = [];
            var educationCertificateUrlTemplate = @json(route('portal.employee-education-certificates.download', '__education__'));
            var educationLabels = {
                '10th': '10th (SSC)',
                '12th': '12th (HSC)',
                'diploma': 'Diploma',
                'bachelors': "Bachelor's degree",
                'masters': "Master's / Post Graduation",
                'doctorate': 'Doctorate / PhD',
                'other': 'Other'
            };

            function educationIsImage(fileName, mime) {
                return (mime && mime.indexOf('image/') === 0) || /\.(jpe?g|png|gif|webp|bmp)$/i.test(fileName || '');
            }

            function educationFileType(fileName) {
                var parts = String(fileName || '').split('.');
                return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
            }

            function educationUpdateEmptyState() {
                var count = educationTableBody.querySelectorAll('tr').length;
                educationCount.textContent = count;
                educationEmpty.style.display = count ? 'none' : 'block';
            }

            function educationRenderCertificate(cell, fileName, file, educationId) {
                cell.textContent = '';
                if (!fileName && !file) {
                    cell.textContent = '-';
                    return;
                }

                var wrap = document.createElement('span');
                wrap.className = 'education-file-preview';

                if (file && educationIsImage(file.name, file.type)) {
                    var img = document.createElement('img');
                    img.alt = 'Certificate preview';
                    var url = URL.createObjectURL(file);
                    educationFileUrls.push(url);
                    img.src = url;
                    wrap.appendChild(img);
                } else {
                    var icon = document.createElement('span');
                    icon.className = 'education-file-icon';
                    icon.textContent = educationFileType(fileName || 'file');
                    wrap.appendChild(icon);
                }

                var name = educationId && !file
                    ? document.createElement('a')
                    : document.createElement('span');
                name.textContent = fileName || 'Certificate';
                if (educationId && !file) {
                    name.href = educationCertificateUrlTemplate.replace('__education__', encodeURIComponent(educationId));
                    name.target = '_blank';
                    name.rel = 'noopener';
                }
                wrap.appendChild(name);
                cell.appendChild(wrap);
            }

            function educationHiddenInput(card, index, name, value) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'education[' + index + '][' + name + ']';
                input.value = value == null ? '' : value;
                card.appendChild(input);
                return input;
            }

            function educationAddTableRow(data, card) {
                var tr = document.createElement('tr');
                tr.dataset.educationIndex = card.dataset.educationIndex;

                [
                    educationLabels[data.level] || data.level,
                    data.degree,
                    data.institution,
                    data.board_university,
                    data.year_of_passing,
                    data.grade
                ].forEach(function(value, i) {
                    var td = document.createElement('td');
                    if (i === 0) {
                        var badge = document.createElement('span');
                        badge.className = 'education-level-badge';
                        badge.textContent = value || '-';
                        td.appendChild(badge);
                    } else {
                        td.textContent = value || '-';
                    }
                    tr.appendChild(td);
                });

                var fileCell = document.createElement('td');
                var fileInput = card.querySelector('input[type="file"]');
                var fileName = fileInput && fileInput.files.length
                    ? fileInput.files[0].name
                    : (data.certificate_original_name || data.certificate_name || '');
                educationRenderCertificate(fileCell, fileName, fileInput && fileInput.files[0], data.id);
                tr.appendChild(fileCell);

                var actionCell = document.createElement('td');
                var actions = document.createElement('div');
                actions.className = 'education-row-actions';
                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'education-action remove';
                remove.dataset.educationRemove = '1';
                remove.textContent = 'Remove';
                actions.appendChild(remove);
                actionCell.appendChild(actions);
                tr.appendChild(actionCell);

                educationTableBody.appendChild(tr);
            }

            function educationCreateExistingRow(data) {
                var index = educationNextIndex++;
                var card = document.createElement('div');
                card.className = 'education-hidden-row';
                card.dataset.educationIndex = index;
                card.dataset.educationId = data.id || '';

                educationHiddenInput(card, index, 'id', data.id || '');
                educationHiddenInput(card, index, 'level', data.level || '');
                educationHiddenInput(card, index, 'degree', data.degree || '');
                educationHiddenInput(card, index, 'institution', data.institution || '');
                educationHiddenInput(card, index, 'board_university', data.board_university || '');
                educationHiddenInput(card, index, 'year_of_passing', data.year_of_passing || '');
                educationHiddenInput(card, index, 'grade', data.grade || '');
                educationHiddenInput(card, index, 'certificate_original_name', data.certificate_original_name || '');

                educationStorage.appendChild(card);
                educationAddTableRow(data, card);
            }

            function educationValidateEntry() {
                var required = [educationEntry.level, educationEntry.degree, educationEntry.institution, educationEntry.year, educationEntry.certificate];
                var valid = true;
                required.forEach(function(el) {
                    var missing = el.type === 'file' ? !el.files.length : !el.value.trim();
                    el.setCustomValidity(missing ? 'This field is required.' : '');
                    if (missing) valid = false;
                });

                var year = Number(educationEntry.year.value);
                if (educationEntry.year.value && (year < 1950 || year > new Date().getFullYear())) {
                    educationEntry.year.setCustomValidity('Enter a valid passing year.');
                    valid = false;
                }

                var file = educationEntry.certificate.files[0];
                if (file && file.size > 10485760) {
                    educationEntry.certificate.setCustomValidity('Certificate must be 10 MB or smaller.');
                    valid = false;
                }
                return valid;
            }

            function educationResetEntry() {
                var oldFileInput = educationEntry.certificate;
                var label = document.querySelector('#education-entry-panel .document-upload');
                var previewBox = label ? label.querySelector('[data-upload-preview]') : null;

                var newFileInput = document.createElement('input');
                newFileInput.id = 'education-entry-certificate';
                newFileInput.type = 'file';
                newFileInput.accept = '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png';
                newFileInput.dataset.maxSize = '10485760';
                label.insertBefore(newFileInput, previewBox);
                educationEntry.certificate = newFileInput;

                newFileInput.addEventListener('change', function() {
                    validateFileSize(this);
                    renderFilePreview(this);
                });

                educationEntry.level.value = '';
                educationEntry.degree.value = '';
                educationEntry.institution.value = '';
                educationEntry.board.value = '';
                educationEntry.year.value = '';
                educationEntry.grade.value = '';
                educationEntry.level.setCustomValidity('');
                educationEntry.degree.setCustomValidity('');
                educationEntry.institution.setCustomValidity('');
                educationEntry.year.setCustomValidity('');
                newFileInput.setCustomValidity('');
                if (previewBox) {
                    previewBox.textContent = '';
                    previewBox.classList.remove('is-visible');
                }
            }

            document.getElementById('add-education-btn').addEventListener('click', function() {
                if (!educationValidateEntry()) {
                    var firstInvalid = [educationEntry.level, educationEntry.degree, educationEntry.institution, educationEntry.year, educationEntry.certificate].find(function(el) {
                        return !el.checkValidity();
                    });
                    if (firstInvalid) firstInvalid.reportValidity();
                    return;
                }

                var index = educationNextIndex++;
                var data = {
                    level: educationEntry.level.value,
                    degree: educationEntry.degree.value.trim(),
                    institution: educationEntry.institution.value.trim(),
                    board_university: educationEntry.board.value.trim(),
                    year_of_passing: educationEntry.year.value,
                    grade: educationEntry.grade.value.trim()
                };

                var card = document.createElement('div');
                card.className = 'education-hidden-row';
                card.dataset.educationIndex = index;

                educationHiddenInput(card, index, 'level', data.level);
                educationHiddenInput(card, index, 'degree', data.degree);
                educationHiddenInput(card, index, 'institution', data.institution);
                educationHiddenInput(card, index, 'board_university', data.board_university);
                educationHiddenInput(card, index, 'year_of_passing', data.year_of_passing);
                educationHiddenInput(card, index, 'grade', data.grade);

                // Move the actual file input into the hidden submitted row so the file is not lost.
                var fileInput = educationEntry.certificate;
                fileInput.name = 'education[' + index + '][certificate]';
                fileInput.removeAttribute('id');
                fileInput.style.display = 'none';
                card.appendChild(fileInput);
                educationStorage.appendChild(card);

                educationAddTableRow(data, card);
                educationUpdateEmptyState();
                educationResetEntry();
                educationEntry.level.focus();
            });

            educationTableBody.addEventListener('click', function(event) {
                var removeButton = event.target.closest('[data-education-remove]');
                if (!removeButton) return;
                var row = removeButton.closest('tr');
                var index = row ? row.dataset.educationIndex : null;
                var card = index !== null
                    ? educationStorage.querySelector('[data-education-index="' + index + '"]')
                    : null;
                if (card) card.remove();
                if (row) row.remove();
                educationUpdateEmptyState();
            });

            educationEntry.certificate.addEventListener('change', function() {
                validateFileSize(this);
                renderFilePreview(this);
            });

            educationRowsFromServer.forEach(function(row) {
                if (row && row.level) educationCreateExistingRow(row);
            });
            educationUpdateEmptyState();

            /* ---------- add / remove repeat rows ---------- */
            document.querySelectorAll('[data-repeat-add="experience"], [data-repeat-add="documents"]').forEach(function(button) {
                button.addEventListener('click', function() {
                    var name = button.dataset.repeatAdd;
                    var list = document.querySelector('[data-repeat-list="' + name + '"]');
                    var tpl = document.getElementById({
                        experience: 'experience-row-template',
                        education: 'education-row-template'
                    }[name] || 'document-row-template');
                    var index = Number(list.dataset.nextIndex || 0);
                    if (name === 'documents' && list.children.length >= 10) {
                        button.disabled = true;
                        return;
                    }
                    var row = tpl.content.cloneNode(true);
                    row.querySelectorAll('[name]').forEach(function(input) {
                        input.name = input.name.replace(/__INDEX__/g, index);
                    });
                    list.appendChild(row);
                    syncExperienceDateRange(list.lastElementChild);
                    validateNamedFields(list.lastElementChild);
                    list.dataset.nextIndex = String(index + 1);
                    if (name === 'documents' && list.children.length >= 10) button.disabled = true;
                    var first = list.lastElementChild.querySelector('input, select');
                    if (first) first.focus();
                });
            });
            document.addEventListener('click', function(e) {
                var rm = e.target.closest('[data-repeat-remove]');
                if (rm) {
                    var list = rm.closest('[data-repeat-list]');
                    rm.closest('.ob-new-row').remove();
                    var addButton = document.querySelector('[data-repeat-add="' + list.dataset.repeatList + '"]');
                    if (addButton) addButton.disabled = false;
                }
            });

            /* ---------- preview helpers ---------- */
            function labelText(label) {
                var span = label.querySelector('.ob-label');
                if (!span) return '';
                var c = span.cloneNode(true);
                c.querySelectorAll('.req, .ob-badge-ok, .ob-tag').forEach(function(n) {
                    n.remove();
                });
                return c.textContent.trim();
            }

            function fieldValue(control) {
                if (control.type === 'file') {
                    if (control.files.length) {
                        return Array.prototype.map.call(control.files, function(f) {
                            return f.name;
                        }).join(', ');
                    }
                    return control.dataset.existingUpload ? 'Existing file retained' : 'No file selected';
                }
                if (control.name === 'bank[account_number]') {
                    return control.value ? '\u2022\u2022\u2022\u2022' + control.value.slice(-4) : '';
                }
                if (control.tagName === 'SELECT') return control.value && control.selectedIndex > -1 ? control.options[
                    control.selectedIndex].text : '';
                return control.value.trim();
            }

            function buildList(block) {
                var dl = document.createElement('dl');
                dl.className = 'ob-pv-list';
                block.querySelectorAll('.ob-field').forEach(function(label) {
                    var control = label.querySelector('input:not([type=hidden]), select, textarea');
                    if (!control) return;
                    var value = fieldValue(control);
                    if (!value) return;
                    var entry = document.createElement('div');
                    if (control.tagName === 'TEXTAREA') entry.className = 'full';
                    var dt = document.createElement('dt');
                    dt.textContent = labelText(label);
                    var dd = document.createElement('dd');
                    dd.textContent = value;
                    if (control.type === 'file' && isImageFile(control.files[0])) {
                        var image = document.createElement('img');
                        image.className = 'ob-image-preview';
                        image.alt = labelText(label) + ' image preview';
                        image.src = URL.createObjectURL(control.files[0]);
                        objectUrls.push(image.src);
                        dd.appendChild(image);
                    }
                    entry.appendChild(dt);
                    entry.appendChild(dd);
                    dl.appendChild(entry);
                });
                return dl;
            }

            function renderPreview() {
                content.textContent = '';
                objectUrls.forEach(function(u) {
                    URL.revokeObjectURL(u);
                });
                objectUrls = [];

                editable.querySelectorAll('.ob-section').forEach(function(section) {
                    var wrap = document.createElement('div');
                    wrap.className = 'ob-pv-section';
                    var h2 = document.createElement('h2');
                    h2.textContent = section.dataset.title || section.querySelector('h2').textContent.trim();
                    wrap.appendChild(h2);

                    var cards = section.querySelectorAll('.ob-repeat-card');
                    var blocks = cards.length ? Array.prototype.slice.call(cards) : [section];
                    var added = 0;
                    blocks.forEach(function(block, i) {
                        var dl = buildList(block);
                        if (!dl.children.length) return;
                        if (cards.length > 1) {
                            var sub = document.createElement('p');
                            sub.className = 'ob-pv-sub';
                            sub.textContent = ({
                                'Previous experience': 'Role ',
                                'Education details': 'Qualification '
                            }[section.dataset.title] || 'Document ') + (i + 1);
                            wrap.appendChild(sub);
                        }
                        wrap.appendChild(dl);
                        added++;
                    });
                    if (!added) {
                        var empty = document.createElement('p');
                        empty.style.cssText = 'margin:0;font-size:13px;font-weight:700;color:#374151';
                        empty.textContent = 'Nothing added.';
                        wrap.appendChild(empty);
                    }
                    content.appendChild(wrap);
                });

                // Header summary
                var name = (val('first_name') + ' ' + val('last_name')).trim();
                document.getElementById('pv-name').textContent = name || 'Employee onboarding';
                document.getElementById('pv-meta').textContent = [val('employee_code'), val('employee_role_id'), val(
                    'department_id')].filter(Boolean).join('  |  ');
                document.getElementById('pv-date').textContent = 'Generated on ' + new Date().toLocaleDateString(
                    'en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

                // Photo
                var slot = document.getElementById('pv-photo-slot');
                slot.textContent = '';
                var photo = form.querySelector('[name="required_documents[photo]"]');
                if (photo && photo.files[0] && isImageFile(photo.files[0])) {
                    var img = document.createElement('img');
                    img.className = 'ob-sheet-photo';
                    img.alt = 'Employee photo';
                    img.src = URL.createObjectURL(photo.files[0]);
                    objectUrls.push(img.src);
                    slot.appendChild(img);
                } else {
                    var ph = document.createElement('div');
                    ph.className = 'ob-sheet-photo-empty';
                    ph.textContent = photo && photo.dataset.existingUpload ? 'Existing photo on file' : 'No photo';
                    slot.appendChild(ph);
                }
            }

            function show(mode) {
                editable.hidden = mode !== 'edit';
                preview.hidden = mode !== 'preview';
                window.scrollTo({
                    top: 0
                });
            }

            document.querySelector('[data-show-preview]').addEventListener('click', function() {
                if (!form.reportValidity()) return;
                renderPreview();
                show('preview');
            });
            document.querySelector('[data-edit-details]').addEventListener('click', function() {
                show('edit');
            });

            /* ---------- download PDF ---------- */
            document.querySelector('[data-download-preview]').addEventListener('click', function() {
                var btn = this;
                var sheet = document.getElementById('onboarding-sheet');
                var code = val('employee_code') || 'employee';
                var fileName = 'onboarding-' + code.replace(/[^a-z0-9_-]+/gi, '-').toLowerCase() + '.pdf';

                if (typeof window.html2pdf !== 'function') {
                    window.print();
                    return;
                }

                btn.disabled = true;
                var original = btn.textContent;
                btn.textContent = 'Preparing PDF...';
                window.html2pdf().set({
                    margin: [10, 10, 10, 10],
                    filename: fileName,
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    },
                    pagebreak: {
                        mode: ['css', 'legacy'],
                        avoid: '.ob-pv-section'
                    }
                }).from(sheet).save().catch(function() {
                    window.print();
                }).then(function() {
                    btn.disabled = false;
                    btn.textContent = original;
                });
            });

            window.addEventListener('beforeunload', function() {
                objectUrls.concat(formPreviewUrls).forEach(function(url) {
                    URL.revokeObjectURL(url);
                });
            });
        })();
    </script>
@endsection
