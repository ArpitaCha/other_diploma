<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> APPLICATION </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;

        }


        .container {
            max-width: 1200px;
            margin: 0 auto;
            overflow: hidden;
            /* Clearfix */
        }

        .left {
            float: left;
            margin-right: 20px;
        }

        .middle {

            float: left;
            width: 80%;
            margin-right: 5px;

        }

        .right {
            float: right;
            width: 300px;
            /* Adjust width as needed */
        }

        .category-class {
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
        }

        .category-class th,
        .category-class td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: center;
        }

        .category-class th {
            background-color: #f2f2f2;
        }

        .allotment_details td {
            padding: 0px;
            border: 1px solid #000000;

        }

        .allotment_details tr {
            text-align: center;
            border-bottom: 1px solid #000000;
        }

        .footer p {
            font-size: 11px;
        }

        .header {
            position: fixed;
            top: 1px;
            text-align: center;

        }

        .no-border {
            border: none;
            float: left;
        }
    </style>



</head>

<body>

    <div class="header">
        <p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
            <span style="color:#2d0660;font-family:Cambria;font-size:14px;">
                <span style="font-stretch:115%;">
                    <strong>WEST BENGAL
                        STATE COUNCIL OF TECHNICAL AND VOCATIONAL EDUCATION &amp; SKILL DEVELOPMENT</strong>
                </span>
            </span>
        </p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="color:#2d0660;font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                (Under West Bengal Act XXVI of 2013)[Vocational Education Division]
            </span>
        </p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="color:#2d0660;font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                Karigori Bhavan,5thFloor,PlotNo.B/7,Action Area-III,NewTown,Rajarhat,Kolkata-700160
            </span>
        </p>

    </div>
    <div class="content">

        <p style="text-align: center; font-size: 22px; font-weight: bold;padding-top:15px;">Student Details</p>
        <div class="left">
            {{-- <img src="\images\candidate.jpg" alt="Candidate Image"> --}}
        </div>
        <div class="middle">
            <table class="no-border">
                <tbody>

                    <tr>
                        <td style="width: 15%;font-size:12px;">Application Form Number:</td>
                        <td style="width: 15%;font-size:12px;">{{ $students->student_form_num }}</td>
                    </tr>
                    <tr>
                        <td style="width: 15%;font-size:12px;">Name Of The Candidate</td>
                        <td style="width: 15%;font-size:12px;">{{ $students->student_fullname }}</td>
                    </tr>
                    <tr>
                        <td style="width: 15%;font-size:12px;">Gender:</td>
                        <td style="width: 15%;font-size:12px;">{{ $students->student_gender }}</td>
                    </tr>
                    <tr>
                        <td style="width: 15%;font-size:12px;">Religion:</td>
                        <td style="width: 15%;font-size:12px;">{{ $students->student_religion }}</td>
                    </tr>
                    <tr>
                        <td style="width: 15%;font-size:12px;">Physically Challenged:</td>
                        <td style="width: 20%;font-size:12px;">{{ $students->student_pwd }}</td>
                    </tr>
                    <tr>
                        <td style="width: 15%;font-size:12px;">Address:</td>
                        <td style="width: 15%;font-size:12px;">{{ $students->student_address }}</td>
                    </tr>

                </tbody>
            </table>
        </div>




    </div>
    <div class="container"style="padding-top:200px;">
        <label style="font-size: 18px; font-weight: bold;">Registration Details</label>
        <table
            class="allotment_details"style="border-collapse:collapse;border:1px solid #000000;padding-top:5px;width:100%;background-color: #f2f2f2;">

            <tr>
                <td>Institute Name</td>
                <td>{{ $institute_name }}</td>
            </tr>
            <tr>
                <td>Course Name</td>
                <td>{{ $course_name }}</td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>{{ $students->student_semester }}</td>
            </tr>
            <tr>
                <td>Session Year</td>
                <td>{{ $students->student_session_yr }}</td>
            </tr>



        </table>
    </div>
    <div class="footer">
        <p>Dear candidate,</p>
        <p>Based on your merit and choices of Institutions & Branch exercised by you during online Counselling.You have
            been provisionally alloted a seat in the above mentioned institute and branch.You can take following actions
            on this allotment.
        <p>1.pay the seat Acceptance Fee of Rs.1,000/-.<br>
            2.Download and take a priintout of the provisional Seat Allotment Letter.
            <br>
            3.Report To the Alloted Institute for document verification and admission process within the stipulated
            period,as mentioned in the counselling schedule otherwise alloted seat will be cancelled.
            <br>
            <br>
            This Allotment is provisional subject to physical verificationand security of documents by the
            institute/university.if at any stage it is found that you are otherwise ineligible or you have suppressed
            any material infformation or you have submitted false information documents,this allotment will stand
            cancelled.

        </p>
        </p>
    </div>







</body>

</html>
