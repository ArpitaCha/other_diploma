<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;">
    <title>Enrollment Sheet</title>
    <style>
        @page {
            margin: 160px 20px 100px 20px;
        }

        #header {
            /* background-color: orange; */
            position: fixed;
            left: 0px;
            top: -160px;
            right: 0px;
            height: 160px;
        }

        #footer {
            /* background-color: rgb(221, 5, 237); */
            position: fixed;
            left: 0px;
            bottom: -100px;
            right: 0px;
            height: 100px;
        }

        #content {
            /* background-color: rgb(7, 120, 7); */
        }

        .logo {
            position: absolute;
            left: 10;
            top: 40;
            right: 0;
            bottom: 0;
        }

        .heading-text {
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            /* margin-top: 20px; */
        }

        .footer-left {
            text-align: center;
            position: absolute;
            left: 20;
            bottom: 20;
        }

        .footer-right {
            text-align: center;
            position: absolute;
            right: 20;
            bottom: 10;
        }

        .qr-code {
            width: 60px;
            height: 60px;
            border: 1px solid black;
            float: right;
            margin-top: -40px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            border: 1px solid black;
            text-align: center;
            font-size: 14px;
            /* padding: 5px 0; */
        }

        td {
            border: 1px solid black;
            text-align: center;
            font-size: 12px;
            /* padding: 5px 0; */
        }

        .no-border {
            border: none;
        }

        .bold {
            font-weight: bold;
        }

        .payment-details {
            width: 100%;
            margin-top: 5px;
        }

        .payment-data {
            border: 1px dashed rgb(149, 148, 148);
            padding: 10px 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            font-size: 15px;
            padding-bottom: 2px;
        }

        .student-image {
            max-width: 80px;
            max-height: 80px;
        }
    </style>
</head>

<body>
    <div id="header">
        <div style="text-align:right; margin: 5px 0; font-size:14px;">
            Download Date: {{ date('jS F Y') }}
        </div>
        <div class="heading-text">
            <img class="logo" src="images/logo.png" alt="logo" width="50px">
            <p style="margin:0; padding:0;">WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND
                SKILL
                DEVELOPMENT</p>
            <p style="margin:0; padding:0; font-size: 16px; margin: 5px 0;">Examination Enrollment Form,
                {{ $academic_year }}</p>
            <p style="margin:0; padding:0;">SEMESTER-{{ $semester }}
            </p>



            <p style="font-size: 16px; padding: 0;margin:0;margin-top:20px;">
                <strong style="color: rgb(4, 4, 112)">{{ $inst_name }}</strong>
            </p>
            <p style="font-size: 16px; padding: 0;margin:0;margin-top:3px;">
                Course: <strong style="color: rgb(4, 4, 112)">{{ $course_name }}[({{ $course_code }})]</strong>,

            </p>
        </div>
    </div>

    <div id="footer">
        <div class="footer-left">
            Date :
            <hr style="width: 500%; text-align: left; margin-left: 0; border: 1px solid black;">
        </div>

        <div class="footer-right">
            <hr style="width: 100%; text-align: right; margin-right: 0; border: 1px solid black;">
            Head of the institution <br /> (Signature with Seal)
        </div>
    </div>

    <div id="content">
        <table>
            <thead>
                <tr>
                    <th>Sl No</th>
                    <th style="text-align: left;">
                        <ol type="a">
                            <li>Name of the Candidate</li>
                            <li>Reg.No, Session Year</li>
                            <li>Father/Gurdian's Name</li>
                            {{-- <li>Signautre of candidate</li> --}}

                        </ol>
                        
                    </th>
                    <th style="text-align: center;">Signature of Candidate </th>

                </tr>
            </thead>

            <tbody>
                @foreach ($students as $key => $student)
             
                    <tr>
                        <td style="text-align: center;">{{ $key + 1 }}</td>
                        <td style="text-align: left;">
                            <ol type="a">
                                <li>
                                    {{ $student['stu_name'] }}
                                </li>
                                <li>
                                    {{ $student['stu_reg_no'] }} of {{ $student['stu_reg_year'] }}</li>
                                </li>
                                <li>
                                    {{ $student['guardian'] }}
                                </li>
                                
                            </ol>
                        </td>
                        <td style="text-align: center;width: 100px;padding: 10px;">
                            @if (!is_null($student['signature']))
                                <img src="{{ $student['signature'] }}" alt="Student Image" class="student-image">
                            @endif
                        </td>




                    </tr>
                @endforeach
            </tbody>
        </table>

        <div>
            <ol type="1">
                <li>
                    Total Amount payable for each candidate is as follows:
                    <ol type="a">
                        <li>Regular/Casual Candidate for Diploma Rs. {{ $exam_fees }}/- for Examination fee for
                            processing of Forms.</li>
                    </ol>
                </li>
                <li>Regarding attendance and percentage of internal marks secured please follow the Examination
                    Regulation of WBSCTVESD(VED)
                </li>
                <li>
                    CR - Correction pending approval of council.
                </li>
            </ol>
        </div>
    </div>
</body>

</html>
