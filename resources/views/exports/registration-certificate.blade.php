<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Certificate:</title>

    <style>
        body {
            background-image: url("assets/logo_bg.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 35%;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        td {
            font-size: 14px;
        }

        .left {
            float: left;
            margin-right: 20px;
            margin-left: 15px;
        }

        .right {
            float: right;
        }

        .header {
            text-align: left;
          
        }

        .main-section {
            
            position: relative;
            text-align: left;
           font-style: italic;
            /* margin-top: 20px;  */
        }

        .logo-container img {
            width: 70px;
            height: auto;
            display: block;
            margin-left: 20px;
        }

        /* .test td{
             border: 1px solid black;
        } */
        .center-horizontally {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .center-both {
            display: flex;
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            /* Center vertically */
            height: 100vh;
            /* Full height of the viewport */
        }

        .rectangle {
            width: 150px;
            height: 150px;

            background-color: white;
            border: 2px solid black;
            margin-left: 17px;
            margin-top: 20px;
        }
         .rectangle-image {
            width: 120px;
            height: 150px;
            margin-right: 10px;
            border: 1px solid #ccc;
        }

        .rectangle1 {
            width: 180px;
            height: 50px;
            position: center;
            background-color: white;
            border: 2px solid black;
            margin-right: 10px;
            display: inline-block;
            margin-top: 20px;
        }
        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 200px; /* Adjust the underline length */
            height: 18px;
            vertical-align: bottom;
        }
        .underline large{
             display: inline-block;
            border-bottom: 1px solid #000;
            width: 500px; /* Adjust the underline length */
            height: 18px;
            vertical-align: bottom;

        }
    </style>
</head>

<body>
    
        @php
           
            $student_name = $student['student_name'];
            $student_image = $student['student_image'];
            $parent_name = $student['parent_name'];
            $reg_no = $student['reg_no'];
            $reg_year = $student['reg_year'];
            $date = $student['date'];
            $course = $student['course_name'];
        
         
           
        @endphp

        <div class="header" style="position:relative;">
       <div class="header-text" style="text-align: left; margin-right: 40px;">
    <p style="margin: 0; padding: 0; line-height: 1; margin-right: 80px;">
        <span style="color: black; font-family: Arial, sans-serif; font-size: 14px;">
            <span style="font-stretch: 115%;">
                <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND SKILL DEVELOPMENT</strong>
            </span>
        </span>
    </p>

    <div class="right">
        <div class="image-sign">
            <div class="rectangle-image">
                <img src="{{ asset('storage/' . $student_image) }}" style="width: 150px; height: 150px; object-fit: cover;">
            </div>
        </div>
    </div>

    <p style="margin: 0; padding: 0; line-height: 1; margin-right: 80px;">
        <span style="font-size: 13px; font-weight: bold;">
            (Formerly WEST BENGAL STATE COUNCIL OF TECHNICAL EDUCATION)
        </span>
    </p>

    <p style="margin: 0; padding: 0; line-height: 1; margin-right: 80px;">
        <span style="font-size: 12px; font-weight: bold;">
            "Karigari Bhavan", 4th Floor, Plot No. B/7, Action Area-III, Newtown, Rajarhat, Kolkata-700160
        </span>
    </p>
</div>

        <div>
          <h2 style="text-align:center; letter-spacing: 10px;margin-bottom:20px;padding:10px;">REGISTRATION CERTIFICATE</h2>

        </div>
    </div>    
    <div class="main-section"> 
            <div style="margin-top: 10px;">
                <span class="label">Certified that Sri/Smt</span>
                <span class="underline"style=" width: 700px;text-align:center;">{{ $student_name }}</span>
            </div>
            <div style="margin-top: 10px;">
                <span class="label">son/daughter of</span>
                <span class="underline"style=" width: 830px;text-align:center;"> {{ $parent_name }} </span>has

            </div>
            <div style="margin-top: 10px;">
                <span class="label">been registered as a student under the West bengal State Council of Technical & Vocational Education</span>
               
            </div>
            <div style="margin-top: 10px;">
                <span class="label">and Skill Development for study</span>
                <span class="underline"style=" width: 800px;text-align:center;">{{ $course }}</span>
            </div>
            <div style="margin-top: 10px;">
                <span class="label">His/Her Registration number is</span>
                <span class="underline"style=" width: 760px;text-align:center;">{{ $reg_no }} </span>for the
            </div>
            
            <div style="margin-top: 10px;">
                <span class="label">Academic Session</span>
                <span class="underline"style=" width: 500px;text-align:center;">{{ $reg_year }}</span>
            </div>
            
    </div>
    
    <div class="footer" style="margin-top: 200px;">
            <div>
                <span style="font-style:italic;">Kolkata,the</span>
                <span style="text-align:center;">{{ $date}}</span>
                <span class="right"style="font-size:16px;"> Administrative officer(Registration)
                    <span style="display: block;text-align:center;">WBSCT&VE&SD</span>
                </span>
                
            </div>
    </div> 
    
</body>

</html>