<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Out Center</title>
    <style>
        body {
            font-family: Arial, sans-serif;

        }

        .container {
            max-width: 1200px;
            margin-top: 90px;
            overflow: hidden;
            /* Clearfix */
        }

        .left {
            float: left;
            margin-bottom: 20px;
            margin-right: 20px;
        }

        .middle {
            float: left;
            width: 80%;
            margin-right: 5px;
        }

        .right {
            float: right;

            margin-right: 10 0px;
            /* Adjust width as needed */
        }

        .content {
            margin-left: 80px;
        }

        .underline {
            display: inline-block;
            padding-bottom: 5px;
            width: 180px;
            text-align: center;
        }

        .footer {
            position: relative;
            text-align: right;
            padding: 20px;
            margin-right: 20px;
            margin-top: 50px;
            /* Pushes the footer to the bottom */
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-right: none;
            /* Keep the right border */
            border-top: 1px solid black;
            /* Keep the top border */
            border-bottom: 1px solid black;
            /* Keep the bottom border */
            border-left: none;

        }

        .custom-table td {
            border: 1px solid black;
            /* Apply border to all table cells */

        }

        .custom-table td:first-child {
            border-left: none;
            /* Remove left border from the first column */
        }

        .custom-table td:last-child {
            border-right: none;
            /* Remove right border from the last column */
        }

        /* .centeral-table {
            width: 100%;
            border-collapse: collapse;
        }

        .centeral-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;

        } */
    </style>
</head>

<body>
    <div class="header"style="border-bottom: 2px solid black; padding-bottom: 10px;">
        <div style="text-align: center; margin-top: 10px;">
            <img src="images/logo.png" alt="Center Logo"
                style="display: block; margin: 0 auto; width: 50px; height: auto;">
        </div>

        <div class="header-text" style="margin-top:10px;">
            <p style="line-height:1;margin-left:80px;font-size:12px;text-align:center;text-indent:0px;">
                <span">
                    <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND SKILL
                        DEVELOPMENT</strong>
                    </span>
            </p>
            <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:12px;">
                    (A Statutory Body under Government of West Bengal Act XXVI of 2013)
                </span>
            </p>
            <p style="line-height:11.53px;margin:0px 100px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:11px;">
                    Department of Technical Education,Training Skill Development,Government of West Bengal
                </span>
            </p>
            <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                    Karigari Bhavan, 4th Floor, Plot No. B/7, Action Area-III, Newtown, Rajarhat, Kolkata–700160
                </span>
            </p>
        </div>
    </div>
    <div class="container">
        <div class="left">
            <span>Memo No:</span>
        </div>
        <div class="right">
            <span>Date:<span style="margin-left: 10px;"> 21/03/23</span></span>
        </div>

    </div>
    <div class="footer">
        <div class="left">
            <span class="underline">
                Counter Signature of the Coordinator of Centralized evaluation Camp/Nodal Officer
            </span>
            {{-- <p style="padding-bottom: 10px;">Signature of center in charge with seat & Date</p> --}}
        </div>
        <div class="right">
            <span class="underline">
                (Parthasarathi Pandit)
                OSD (Vocational Education)
                WBSCT&VE&SD
            </span>
            {{-- <p style="padding-bottom: 5px;">Signature of the Invigilator</p> --}}
        </div>
    </div>

</body>

</html>
