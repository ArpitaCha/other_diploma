<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> MarkSheet</title>
    <style>
        body {

            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: -5;
            border: 1px solid black;
        }


        .left {
            float: left;


        }

        .right {
            float: right;
        }

        .header {
            width: 100%;
            position: relative;
            padding: 10px 0;
        }

        .footer {
            position: relative;
            text-align: right;
            padding: 20px;

            margin-top: auto;
            /* Pushes the footer to the bottom */
        }

        .underline {
            display: inline-block;
            padding-bottom: 5px;
            width: 180px;
            border-top: 2px solid black;
            text-align: center;
        }



        .logo-container {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container img {
            width: 60px;
            display: block;
            margin: 0 auto;
        }

        .date-container {
            width: 100%;
            text-align: right;
            margin-bottom: 10px;
            /* Align date to the right */
            font-size: 14px;
            padding-right: 20px;

        }


        /* tr {
            border:1px solid black;
        } */
    </style>

</head>

<body>
    <div class="header" style="position:relative;">
        <div class="date-container">
            <span>SL.No.:<span style="margin-left: 10px;"> 21/03/23</span></span>
        </div>
        <div class="logo-container">
            <img src="images/logo.png" alt="Logo">
        </div>



        <div class="header-text" style="text-align: center;flex-grow: 1;margin-bottom: 10px;">
            <p style="line-height: 1; margin: 5px 70px 0px 70px; text-align: center; font-size: 12px;">
                <span style="font-stretch: 115%; font-size: 15px;">
                    <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND SKILL DEVELOPMENT</strong>
                </span>
            </p>

            <p
                style="line-height: 11.53px; margin: 0px 70px 0px 70px; text-align: center; font-family: 'Trebuchet MS', Helvetica, sans-serif; font-size: 14px; font-weight: bold;">
                <strong>MARKSHEET</strong>
            </p>
            <p
                style="line-height: 11.53px; margin: 0px 70px 0px 70px; text-align: center; font-family: 'Trebuchet MS', Helvetica, sans-serif; font-size: 14px; font-weight: bold;">
                <strong>OF</strong>
            </p>
            <p
                style="line-height: 11.53px; margin: 0px 70px 0px 70px; text-align: center; font-family: 'Trebuchet MS', Helvetica, sans-serif; font-size: 12px;">
                <strong>(x+2)LEVEL HIGHER SECONDARY (VOCATIONAL) EXAMINATION, 2023</strong>
            </p>
        </div>

        <div class="content">
            <div style="padding: 10px; text-align: left;">
                <span style="font-weight: bold;">Name of the Student:</span> <span style="margin-left: 10px;">
                    MAHADEB GHOSH</span>
                <br>
                <span style="font-weight: bold;">Roll No.:</span> <span style="margin-left: 10px;"> 123456</span>

                <span style="font-weight: bold;margin-left:100px;">Registration No.:</span> <span
                    style="margin-left: 10px;">
                    123456</span>

                <span style="font-weight: bold;margin-left:80px;">of:</span> <span style="margin-left: 10px;">
                    123456</span>
                <br>
                <span style="font-weight: bold;">Name of the Institution:</span> <span style="margin-left: 10px;">
                    123456</span>
                <br>
                <span style="font-weight: bold;margin-left: 500px;">institution Code:</span> <span
                    style="margin-left: 10px;">
                    123456</span>
                <span style="font-weight: bold;">Discipline:</span> <span style="margin-left: 10px;"> ENGINEERING &
                    TECHNOLOGY</span>

                <span style="font-weight: bold;margin-left:200px;">Goup Code:</span> <span style="margin-left: 10px;">
                    123456</span>
            </div>
        </div>

    </div>
    <div>
        <table border="1" cellpadding="1" cellspacing="1"style="border-collapse: collapse;">

            <tbody>
                <tr>
                    <td colspan="1"
                        rowspan="2"style="width: 40px; height: 100px; position: relative;text-align:center;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;">
                            Group
                        </div>
                    </td>

                    {{-- <td colspan="1" rowspan="2" style="transform: rotate(90deg); text-align: center;">group</td> --}}
                    <td colspan="1" rowspan="2"
                        style="width: 40px; height: 100px; position: relative;text-align:center;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;">
                            paper
                        </div>
                    </td>
                    <td colspan="1" rowspan="2"style="text-align:center;">paper code</td>
                    <td colspan="2" rowspan="1" style="text-align:center;">Theory Marks</td>
                    <td colspan="2" rowspan="1" style="text-align:center;">Practical Marks</td>
                    <td colspan="2" rowspan="1" style="text-align:center;">Project Marks</td>
                    <td style="text-align:center;">Total Marks</td>
                </tr>
                <tr>
                    <td style="text-align:center;">Full</td>
                    <td style="text-align:center;">Obtained
                        /grade</td>
                    <td style="text-align:center;">Full</td>
                    <td style="text-align:center;">Obtained
                        /grade</td>
                    <td style="text-align:center;">Full</td>
                    <td style="text-align:center;">Obtained
                        /Grade</td>
                    <td style="text-align:center;">Obtained
                        /Grade</td>
                </tr>
                <tr>
                    <td colspan="1" rowspan="2"style="width: 40px; height: 70px; position: relative;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;font-size: 10px;">
                            Language
                        </div>
                    </td>
                    <td style="text-align:center;">1</td>
                    <td style="text-align:center;">BEN2</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:center;">2</td>
                    <td style="text-align:center;">ENG2</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="1" rowspan="2"style="width: 40px; height: 70px; position: relative;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;font-size: 12px;">
                            Vocational
                        </div>
                    </td>
                    {{-- <td colspan="1" rowspan="2">Vocational</td> --}}
                    <td style="text-align:center;">3</td>
                    <td style="text-align:center;">CCMA</td>
                    <td style="text-align:center;">12</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:center;">4</td>
                    <td style="text-align:center;">SEMT</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="1" rowspan="2"style="width: 40px; height: 70px; position: relative;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;font-size: 12px;">
                            Compulsory
                            <br>Academic
                            <br>Elective
                        </div>
                    </td>
                    {{-- <td colspan="1" rowspan="2">Compulsory Academic Elect</td> --}}
                    <td style="text-align:center;">5</td>
                    <td style="text-align:center;">MTH2</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:center;">6</td>
                    <td style="text-align:center;">CHEM</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40px; height: 50px; position: relative;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;font-size: 12px;">
                            Optional<br> Elective

                        </div>
                    </td>
                    <td style="text-align:center;">7</td>
                    <td style="text-align:center;">phys</td>
                    <td style="text-align:center;">70</td>
                    <td style="text-align:center;">30/B</td>
                    <td style="text-align:center;">23</td>
                    <td style="text-align:center;">23/A</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">63/B+</td>
                </tr>
                <tr>
                    <td style="width: 40px; height: 50px; position: relative;">
                        <div
                            style="position: absolute; top: 100%; left: 0; transform: rotate(-90deg); transform-origin: left top; white-space: nowrap;font-size: 12px;">
                            Common

                        </div>
                    </td>
                    <td style="text-align:center;">8</td>
                    <td style="text-align:center;">ENST</td>
                    <td style="text-align:center;">80</td>
                    <td style="text-align:center;">53/B+</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">20</td>
                    <td style="text-align:center;">19/O</td>
                    <td style="text-align:center;">72/A</td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="1"style="text-align:center;">Total Marks:</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">242</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">104</td>
                    <td>&nbsp;</td>
                    <td style="text-align:center;">72</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
            </tbody>
        </table>
        <table border="1" cellpadding="1" cellspacing="1"
            style="width: 500px; margin-top: 15px; border-collapse: collapse;">

            <tbody>
                <tr>
                    <td rowspan="2"style="text-align:center;">GRAND<br />
                        TOTAL</td>
                    <td style="text-align:center;">Full marks</td>
                    <td style="text-align:center;"> Marks Obtained</td>
                </tr>
                <tr>
                    <td style="text-align:center;">500</td>
                    <td style="text-align:center;">418</td>
                </tr>
            </tbody>
        </table>
        <table border="1" cellpadding="1" cellspacing="1"
            style="width: 700px; margin-top: 15px; border-collapse: collapse;">

            <tbody>
                <tr>
                    <td style="text-align:center;">Result</td>

                    <td style="text-align:center;">OverAll Percentage/grade</td>
                </tr>
                <tr>
                    <td style="text-align:center;">Passed</td>

                    <td style="text-align:center;">83.60/A+</td>
                </tr>
            </tbody>
    </div>
    <div class="footer">
        <div class="left">
            <p>Dated:<span style="margin-left: 5px;">26th May,2023</span></p>

            <p style="margin-right:50px;">Place:<span style="margin-left: 5px;">Kolkata</span></p>
            <p style="margin-right: 20px;">Please See Reverse</p>



        </div>
        <div class="right"style="margin-top: 80px;">
            <span style="font-weight: bold;margin-top:50px;">Senior Administrative Officer(TED)</span>
        </div>
    </div>




</body>


</html>
