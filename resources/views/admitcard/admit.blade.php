<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Print:</title>

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
    
        @php
           
            $student_name = $student['student_name'];
       
            $parent_name = $student['parent_name'];
            $reg_no = $student['reg_no'];
            $reg_year = $student['reg_year'];
            $roll = $student['roll_no'];
            $course = $student['course_name'];
            $inst_name = $student['inst_name'];
           
            $paper = $student['paper'];
           
        @endphp

        <div class="main-container">
            {{-- header --}}
            

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
                                
                                </td>
                            </tr>
                            <tr>
                                <td>Institute name&nbsp;/&nbsp;</td>
                                <td>:&nbsp;&nbsp;{{ $inst_name }}&nbsp;/&nbsp;&nbsp;</td>
                            </tr>
                            <tr>
                                <td>Course name&nbsp;/&nbsp;</td>
                                <td>:&nbsp;&nbsp;{{ $course }}&nbsp;/&nbsp;&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                
            </div>

            {{-- subjects --}}
            <table class="table-margin-top">
                <thead>
                    <tr>
                        <td class="td-border td-bold" colspan="9" style="letter-spacing: 3px; font-size: 13px;">
                            SUBJECT COMBINATION
                        </td>
                    </tr>
                    
                </thead>

                <tbody>
                    <tr>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">Theory</td>
                        <td class="td-border td-bold td-padding td-vertical-align-top td-text-center">Practical</td>

                        
                        
                    </tr>
                    <tr>
                    <td class="td-border td-padding" colspan="8">
                        @foreach ($paper as $p)
                            {{ $p->paper_name }} ({{ $p->paper_code }})<br>
                        @endforeach
                    </td>
                </tr>

                    <tr>
                        <td class="td-border-0-5 td-height" colspan="9">&nbsp;</td>
                    </tr>

                   
                </tbody>
            </table>

            {{-- exam center --}}
           

            {{-- footer --}}
            <div class="footer">
                <div class="footer-left">
                    <p class="footer-detail">Dated: </p>
                    <p class="footer-detail">Place: </p>
                </div>
                
            </div>
        </div>
    
</body>

</html>