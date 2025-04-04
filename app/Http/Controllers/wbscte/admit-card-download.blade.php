<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card</title>

    <style>
        body {
            font-size: 14px;
            font-family: Cambria;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .container {
            margin-top: 10px;
        }

        .center-margin {
            border-collapse: collapse;
            font-weight: bold;
            text-align: center;
            margin-top: 7px;
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

        .footer {
            margin-top: 10px;
            clear: both;
        }

        .footer p {
            font-size: 12px;
        }

        .student-details {
            float: left;
            margin-right: 20px;
            font-size: 20px;
        }

        .image-sign {
            float: right;
            /* margin-right: -10px; */
            margin-top: -10px;
        }

        .footer-right {
            float: right;
            margin-right: 0px;
            margin-top: -60px;
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
            height: 26.4px;
        }

        .sio-image {
            margin-bottom: 0;
        }

        .header {
            text-align: center;
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
            $parent_name = $student['parent_name'];
            $reg_no = $student['reg_no'];
            $reg_year = $student['reg_year'];
            $roll = $student['roll'];
            $no = $student['no'];
            $discipline_name = $student['discipline_name'];
            $group_code = $student['group_code'];
            $theory_papers = $student['theory_papers'];
            $practical_papers = $student['practical_papers'];
            $center_code = $student['center_code'];
            $center_name = $student['center_name'];
            $center_address = $student['center_address'];

            $image = $student['image'];
            $sign = $student['sign'];
        @endphp

        <div>
            <div style="border: 1px solid #000; padding: 10px;">
                <div class="header">
                    <div style="margin-bottom: 10px;">
                        <label style="float:left; font-weight: bold;">
                            Sl.No: {{ $serial_no }}
                            <span style="font-weight: normal; margin-left: 10px;">[{{ $student_type }}]</span>
                        </label>
                        <label style="float:right; font-weight: bold;">Inst Code: {{ $vtc_code }}</label>
                    </div>

                    <div class="logo-container" style="position:absolute;margin-top:30px;left:10px;">
                        <img src="{{ public_path('logo.png') }}" alt="Left Logo">
                    </div>

                    <div style="font-size: 13px;line-height: 5px; width: 100%;">
                        <p style="text-align:center;margin-top:20px;">
                            <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL AND VOCATIONAL EDUCATION &amp; SKILL
                                DEVELOPMENT</strong>
                        </p>

                        <p style="text-align:center; font-size: 16px;">
                            (X+2)
                            Level Higher Secondary (Vocational) Examination, {{ $exam_year }}
                        </p>

                        <h4 style="margin-top: 8px;">ADMIT CARD</h4>
                    </div>
                </div>

                <div class="container" style="margin-bottom: 70px;">
                    <div class="student-details">
                        <table class="no-border">
                            <tbody>
                                <tr>
                                    <td style="width: 15%;font-size:13px;">Name</td>
                                    <td style="width: 25%;font-size:13px;">{{ $student_name }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 15%;font-size:13px;">Son/Daughter of</td>
                                    <td style="width: 25%;font-size:13px;">{{ $parent_name }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 15%;font-size:13px;">Registration No.</td>
                                    <td style="width: 25%;font-size:13px;">{{ $reg_no }} of {{ $reg_year }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%;font-size:13px;">Roll:</td>
                                    <td style="width: 25%;font-size:13px;">{{ $roll }} of {{ $no }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%;font-size:13px;">Discipline/Group:</td>
                                    <td style="width: 25%;font-size:13px;">{{ $discipline_name }}/
                                        {{ $group_code }}
                                    </td>
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

                <table style="margin-top:150px;border-collapse:collapse;width:100%;font-size: 16px;">
                    <thead>
                        <tr>
                            <td class="td-border td-bold" colspan="9"
                                style="text-align: center; letter-spacing: 1px; padding:5px 0;">SUBJECT
                                COMBINATION</td>
                        </tr>

                        <tr>
                            <td class="td-border td-bold td-height"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Examination</td>
                            <td class="td-border td-bold td-height" colspan="2" rowspan="1"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Language</td>
                            <td class="td-border td-bold td-height" colspan="2" rowspan="1"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Vocational paper</td>
                            <td class="td-border td-bold td-height" colspan="2" rowspan="1"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Academic Elective</td>
                            <td class="td-border td-bold td-height"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Optional</td>
                            <td class="td-border td-bold td-height"
                                style="padding:0px;vertical-align:top;text-align:center;">
                                Common</td>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                Theory</td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p1', $theory_papers) ? $theory_papers['p1'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p2', $theory_papers) ? $theory_papers['p2'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p3', $theory_papers) ? $theory_papers['p3'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p4', $theory_papers) ? $theory_papers['p4'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p5', $theory_papers) ? $theory_papers['p5'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p6', $theory_papers) ? $theory_papers['p6'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p7', $theory_papers) ? $theory_papers['p7'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p8', $theory_papers) ? $theory_papers['p8'] : null }}
                            </td>
                        </tr>

                        <tr>
                            <td class="td-border td-bold" colspan="9"
                                style="border:0.5px solid #000000; height:5px; padding:0px; vertical-align:top;">
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                Practical</td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p1', $practical_papers) ? $practical_papers['p1'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p2', $practical_papers) ? $practical_papers['p2'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p3', $practical_papers) ? $practical_papers['p3'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p4', $practical_papers) ? $practical_papers['p4'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p5', $practical_papers) ? $practical_papers['p5'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p6', $practical_papers) ? $practical_papers['p6'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p7', $practical_papers) ? $practical_papers['p7'] : null }}
                            </td>
                            <td class="td-border td-bold" style="padding:5px 0;vertical-align:top;text-align:center;">
                                {{ array_key_exists('p8', $practical_papers) ? $practical_papers['p8'] : null }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if ($center_code)
                    <table border="1" cellpadding="1" cellspacing="1" class="center-margin">
                        <thead>
                            <tr class="td-border td-size">
                                <th colspan="2" class="subject-combination">EXAMINATION CENTRE
                                    ({{ $exam_type }})
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="td-border" style="padding: 10px; font-size: 16px;">{{ $center_code }}
                                </td>
                                <td class="td-border" style="padding: 10px;">{{ $center_name }}<br />
                                    {{ $center_address }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <div class="footer">
                    <div class="float-left" style="font-weight: 500;">
                        <p>Dated: {!! $date !!}</p>
                        <p>Place: {{ $place }}</p>

                    </div>
                    <div class="footer-right">
                        <img src="{{ public_path('sao-sign.jpg') }}" alt="Senior Administrative Officer"
                            class="sio-image" width="200px">
                        <p style="margin-top: 0;">Senior Administrative Officer (TED)</p>
                    </div>
                </div>
            </div>

            @if ($center_code)
                <h4 style="text-decoration: underline; margin-top: 35px; margin-bottom:0;">
                    Rules for the Guidance of the Candidates
                </h4>
                <ol class="small-text">
                    <li>The examination will be held according to the programme previously notified.</li>
                    <li>The doors of the examination hall will be opened on the morning of the first day one hour
                        earlier and in the afternoon and on the other days 20 minutes earlier than the time schedule of
                        the examination after which no candidates will be allowed without the special permission of the
                        Center-in-Charge. In no case will a candidate be allowed or given a question paper more than 20
                        minutes after the examination has commenced.</li>
                    <li>Candidates are required to find their own allotted seats. They shall take their seats at least 5
                        minutes before the time schedule of commencement of the examination.</li>
                    <li>No candidate will be allowed to leave the Examination Hall until an hour has elapsed from the
                        time when the question papers are given out. Except as hereinafter provided, no candidate will
                        be allowed to re-enter the Examination Hall during the hours of examination after once leaving
                        it, nor to leave the Examination Hall without giving up his / her answer scripts.
                        <ul>
                            <li>A candidate may with the special permission of the Center-in-Charge / Invigilator leave
                                the Examination Hall temporarily for necessary purpose.</li>
                            <li>A candidate having completed his / her answer scripts must hand it over even if blank to
                                the Invigilator before leaving the Examination Hall. The answer script must on no
                                account be left on the desk. No candidate will be allowed to remain in the Examination
                                Hall after the close of the examination except to allow his / her answer script to be
                                collected by the Invigilator.</li>
                        </ul>
                    </li>
                    <li>Candidates are required to provide themselves with their own pens, pencils, eraser, drawing
                        instruments etc.
                        <ul>
                            <li>The Council will supply stitched blank booklets in which to write the question answer.
                                On no account should any paper / page be torn from the booklet.</li>
                        </ul>
                    </li>
                    <li>Each candidate shall write on the cover of his / her answer booklet in the appropriate columns
                        the information asked for.</li>
                    <li>Candidates are forbidden to carry into the Examination Hall or have in their possession while
                        under examination any books, notes paper writing or any other materials except their Admit Card,
                        Council Registration Certificates and any other writing requisites or drawing implements. Any
                        other article carried into the Examination Hall if found into possession of a candidate in
                        contravention of this rule, shall be liable to be seized by the Center-in-Charge and the
                        candidates shall be liable to expulsion.</li>
                    <li>A candidate while under examination shall not help or try to help any other candidate nor obtain
                        or try to obtain any help from other candidates or other persons. Communication of any sort or
                        in any form is strictly forbidden between a candidate and any other person whether inside or
                        outside the Examination Hall.
                        <ul>
                            <li>Smoking and the use of mobile phones and any other means of communication in the
                                Examination Hall is strictly forbidden.</li>
                            <li>A candidate requiring an additional loose answer sheet (supplied by the Council) or
                                desiring permission to leave the room for necessary purpose or desiring to give up his /
                                her answer scripts may call the attention of the Invigilator by rising from his / her
                                seat and without making any noise or disturbance. On no account is a candidate permitted
                                to speak with any Invigilator on any other matter with reference to any question or
                                answer.</li>
                        </ul>
                    </li>
                    <li>A candidate must not write any objectionable or improper remarks in the answer scripts or
                        attempt in any way to render identification of the answer script impossible by giving a false
                        Roll Number or intentionally omitting to state the Correct Roll Number. A candidate must not
                        write anything on the question paper or other paper or carry away any writing scribbled from the
                        Examination Hall.</li>
                    <li>A candidate is required to produce this Admit Card, Registration Certificate and to sign his /
                        her name as and when directed by the Center-in-Charge.</li>
                    <li>A candidate is required to observe strictly the rules laid down by the Council for the conduct
                        of the examination.</li>
                    <li>A candidate is warned that any attempt to use any unfair means at the examination or any breach
                        or attempted breach of any of these or other examination rules will render them liable to
                        expulsion by the Center-in-Charge from the examination of any part thereof and to such further
                        penalties as the Council may determine.</li>
                    <li>If during and / or after the examination if it is found that a candidate does not fulfill all or
                        any of the rules and regulations governing the examination, his / her examination is liable to
                        be cancelled.</li>
                    <li>Notwithstanding the issue of the Admit Card, the Council shall have the right for any reason
                        which may appear to them sufficient to cancel the admission of any candidate to any examination
                        whether before, during or after the examination. The Council may also debar a candidate from
                        appearing in any subsequent Council examination of the examinations. The decision of the Council
                        in all such cases shall be final.</li>
                    <li>For migrating candidates, this Admit Card is being provisionally issued subject to sanction by
                        the Council authority.</li>
                    <li>Council may change the Date of Examination depending upon the situation.</li>
                </ol>
            @endif
        </div>
    @endforeach
</body>

</html>
