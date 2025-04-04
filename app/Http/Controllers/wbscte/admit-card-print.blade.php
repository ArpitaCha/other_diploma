<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Print: {{ $nodal_name }}</title>

    <style>
        body {
            font-size: 14px;
            font-family: Cambria;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .center-margin {
            border-collapse: collapse;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
        }

        .rectangle-image {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            border: 1px solid #ccc;
        }

        .rectangle-sign {
            max-width: 100px;
            max-height: 20px;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 20px;
        }

        .small-image {
            max-width: 100px;
            max-height: 100px;
        }

        .small-sign {
            max-width: 100%;
            max-height: 100%;
        }

        table {
            width: 100%;
        }

        td {
            padding: 1px 4px;
            vertical-align: top;
            font-size: 11px;
        }

        .no-border {
            border: none;
        }

        .student-details {
            float: left;
            margin-right: 20px;
            font-size: 20px;
        }

        .student-details p,
        .image-sign p {
            font-weight: bold;
        }

        .student-details td {
            text-align: left;
            line-height: 15px;
        }

        .subject-combination {
            text-align: center;
            font-weight: bold;
        }

        tr td {
            text-align: center;
            font-weight: bold;
        }

        table.no-border tbody tr td {
            font-weight: bold;
        }

        .td-border {
            border: 1px solid #0505057f;
        }

        .td-bold {
            font-weight: bold;
        }

        .td-height {
            height: 4px;
        }

        .logo-container {
            width: 5px;
            text-align: left;
        }

        .logo-container img {
            width: 50px;
            display: block;
            margin: 0 auto;
        }

        .float-left {
            text-align: left;
        }

        .small-text {
            font-size: 9px;
            text-align: left;
        }

        .header-label {
            float: left;
            font-weight: bold;
        }

        .student-details-table td {
            width: 15%;
            font-size: 13px;
        }

        .student-details-table td:nth-child(2) {
            width: 25%;
        }

        .table-margin-top {
            margin-top: 130px;
            border-collapse: collapse;
            width: 100%;
            font-size: 16px;
        }

        .td-padding {
            padding: 5px 0;
        }

        .td-padding-0 {
            padding: 0px;
        }

        .td-vertical-align-top {
            vertical-align: top;
        }

        .td-text-center {
            text-align: center;
        }

        .td-border-0-5 {
            border: 0.5px solid #000000;
        }

        .main-container {
            height: 100%;
        }

        .td-width {
            width: 16%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 100px;
        }

        .image-sign {
            float: right;
            margin-right: -25px;
            margin-top: -20px;
        }

        .inst-code {
            float: right;
            font-weight: bold;
            margin-top: -10px;
            padding-right: 6px;
        }

        .serial-no {
            float: left;
            font-weight: bold;
            margin-top: 60px;
            margin-left: 5px;
        }

        .footer {
            margin-top: 10px;
        }

        .footer-left {
            font-weight: 500;
            float: left;
        }

        .footer-detail {
            font-size: 12px;
            text-align: left;
            line-height: 5px;
        }

        .footer-right {
            float: right;
            margin-right: 0px;
        }

        .footer-sign-name {
            margin-top: -5px;
        }
    </style>
</head>

<body>
    @foreach ($students as $student)
        @php
            $serial_no = $student['serial_no'];
            $student_type = $student['student_type'];
            $vtc_code = str_pad($student['vtc_code'], 4, '0', STR_PAD_LEFT);
            $exam_year = $student['exam_year'];
            $student_name = $student['student_name'];
            $center_code = str_pad($student['center_code'], 4, '0', STR_PAD_LEFT);
            $center_name = $student['center_name'];
            $center_address = $student['center_address'];
            $parent_name = $student['parent_name'];
            $reg_no = $student['reg_no'];
            $reg_year = $student['reg_year'];
            $roll = $student['roll'];
            $no = $student['no'];
            $discipline_name = $student['discipline_name'];
            $group_code = $student['group_code'];
            $image = $student['image'];
            $sign = $student['sign'];
            $theory_papers = $student['theory_papers'];
            $practical_papers = $student['practical_papers'];
        @endphp

        <div class="main-container">
            {{-- header --}}
            <div class="page-header">
                <label class="serial-no">Sl.No: {{ $serial_no }}
                    <span style="font-weight: normal; margin-left: 10px;">[{{ $student_type }}]</span>
                </label>
                <label class="inst-code">{{ $exam_year }} &nbsp;&nbsp;
                    <span style="margin-right: -10px;">[Inst Code: {{ $vtc_code }}]</span></label>
            </div>

            {{-- details --}}
            <div class="details-container">
                <div class="student-details">
                    <table class="no-border student-details-table">
                        <tbody>
                            <tr>
                                <td>Name</td>
                                <td>:&nbsp;&nbsp;{{ $student_name }}</td>
                            </tr>
                            <tr>
                                <td>Son&nbsp;/&nbsp;Daughter of</td>
                                <td>:&nbsp;&nbsp;{{ $parent_name }}</td>
                            </tr>
                            <tr>
                                <td>Registration No.</td>
                                <td>:&nbsp;&nbsp;{{ $reg_no }} of {{ $reg_year }}</td>
                            </tr>
                            <tr>
                                <td>Roll</td>
                                <td>:&nbsp;&nbsp;{{ $roll }} <span style="margin-left: 20px;">No.</span>
                                    {{ $no }}
                                </td>
                            </tr>
                            <tr>
                                <td>Discipline&nbsp;/&nbsp;Group</td>
                                <td>:&nbsp;&nbsp;{{ $discipline_name }}&nbsp;/&nbsp;&nbsp;{{ $group_code }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($image && $sign)
                    <div class="image-sign">
                        <div class="rectangle-image">
                            @if ($image)
                                <img src="{{ $image }}" alt="Student Image" class="small-image"
                                    style="min-height:100px; max-height:100px; min-width: 100px; max-width: 100px;">
                            @endif
                        </div>

                        <div class="rectangle-sign">
                            @if ($sign)
                                <img src="{{ $sign }}" alt="Student sign" class="small-sign"
                                    style="min-height:20px; max-height:20px; min-width: 100px; max-width: 100px;">
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- subjects --}}
            <table class="table-margin-top">
                <thead>
                    <tr>
                        <td class="td-border td-bold" colspan="9" style="letter-spacing: 3px; font-size: 13px;">
                            SUBJECT COMBINATION
                        </td>
                    </tr>
                    <tr>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center">Examination
                        </td>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center" colspan="2">
                            Language</td>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center" colspan="2">
                            Vocational paper</td>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center" colspan="2">
                            Academic Elective</td>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center">Optional
                        </td>
                        <td class="td-border td-bold td-padding-0 td-vertical-align-top td-text-center">Common</td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">Theory</td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p1', $theory_papers) ? $theory_papers['p1'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p2', $theory_papers) ? $theory_papers['p2'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p3', $theory_papers) ? $theory_papers['p3'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p4', $theory_papers) ? $theory_papers['p4'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p5', $theory_papers) ? $theory_papers['p5'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p6', $theory_papers) ? $theory_papers['p6'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p7', $theory_papers) ? $theory_papers['p7'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p8', $theory_papers) ? $theory_papers['p8'] : null }}
                        </td>
                    </tr>

                    <tr>
                        <td class="td-border-0-5 td-height" colspan="9">&nbsp;</td>
                    </tr>

                    <tr>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">Practical</td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p1', $practical_papers) ? $practical_papers['p1'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p2', $practical_papers) ? $practical_papers['p2'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p3', $practical_papers) ? $practical_papers['p3'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p4', $practical_papers) ? $practical_papers['p4'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p5', $practical_papers) ? $practical_papers['p5'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p6', $practical_papers) ? $practical_papers['p6'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p7', $practical_papers) ? $practical_papers['p7'] : null }}
                        </td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">
                            {{ array_key_exists('p8', $practical_papers) ? $practical_papers['p8'] : null }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- exam center --}}
            <table border="1" cellpadding="1" cellspacing="1" class="center-margin">
                <thead>
                    <tr class="td-border">
                        <th colspan="2" class="subject-combination">
                            <span style="letter-spacing: 3px; font-size: 13px;">EXAMINATION CENTRE</span>
                            ({{ $exam_type }})
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="td-border td-width" style="font-size: 15px; vertical-align: middle;">
                            {{ $center_code }}
                        </td>

                        <td class="td-border" style="height: 40px; vertical-align: middle;">
                            {{ $center_name }}<br />{{ $center_address }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- footer --}}
            <div class="footer">
                <div class="footer-left">
                    <p class="footer-detail">Dated: {!! $date !!}</p>
                    <p class="footer-detail">Place: {{ $place }}</p>
                </div>
                <div class="footer-right">
                    <img src="{{ public_path('sao-sign.jpg') }}" width="160px">
                    <p class="footer-sign-name">Senior Administrative Officer (TED)</p>
                </div>
            </div>
        </div>
    @endforeach
</body>

</html>
