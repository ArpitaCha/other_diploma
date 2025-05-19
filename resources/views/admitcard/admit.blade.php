<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card</title>
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
            text-align: center;
          
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
            $student_image = $student['student_profile_pic'];
       
            $parent_name = $student['parent_name'];
            $reg_no = $student['reg_no'];
            $reg_year = $student['reg_year'];
            $roll = $student['roll_no'];
            $course = $student['course_name'];
            $inst_name = $student['inst_name'];
             $inst_address = $student['inst_address'];
            $exam_month = $student['exam_month'];
             $venue = $student['venue'];
            $paper = $student['paper'];
            $date = $student['date'];
            $semester = $student['semester'];
            $semester_label = $student['semester_label'];
           
        @endphp

    <div class="header" style="position:relative;">
        <div class="logo-container"style="position:absolute; margin-top:5px; margin-left:15px;">
            <img src="{{ public_path('images/logo.png') }}" alt="Left Logo">
        </div>
        <div class="header-text" style="text-align: left;margin-right: 40px;">
            <p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
                <span style="color:black;font-family:Arial, sans-serif;font-size:14px;">
                    <span style="font-stretch:115%;">
                        <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND SKILL
                            DEVELOPMENT</strong>
                    </span>
                </span>
            </p>
             <div class="right">
                <div class="image-sign">
                    <div class="rectangle-image">
                        <img src="{{ asset('storage/' . $student_image) }}"style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                </div>
            </div>
            

          
            <p style="line-height: 1.4; margin: 0 130px 4px 130px; text-align: left;">
                <span style="font-size: 13px; font-weight: bold;">
                    (Formerly WEST BENGAL STATE COUNCIL OF TECHNICAL EDUCATION)
                </span>
            </p>
        
            <p style="line-height: 1.4; margin: 0 130px; text-align: left;">
                <span style="font-size: 12px;font-weight: bold;">
                    "Karigari Bhavan", 4th Floor, Plot No. B/7, Action Area-III, Newtown, Rajarhat, Kolkata-700160
                </span>
            </p>

            
        </div>
        <div>
          <h2 style="text-align:center; letter-spacing: 10px;">ADMIT</h2>

        </div>
    </div>    
        <div class="main-section"> 
            <div>
                <span class="label">Roll</span>
                <span class="underline">{{ $roll }} </span>
                <span class="label">Number</span>
                <span class="underline">{{ $reg_no}}</span>
                <span class="label">Part/Semester</span>
                <span class="underline">{{ $semester_label }}</span>

            </div>
            <div style="margin-top: 10px;">
                <span class="label">Branch/Trade</span>
                <span class="underline"style=" width: 905px;">{{ $course }}</span>
               

            </div>
            <div style="margin-top: 10px;">
                <span class="label">Name of the Student</span>
                <span class="underline"style=" width: 850px;">{{ $student_name }}</span>
                

            </div>
            <div style="margin-top: 10px;">
                <span class="label">Father's/Mother's Name</span>
                <span class="underline"style=" width: 830px;"> {{ $parent_name }} </span>
                

            </div>
            <div style="margin-top: 10px;">
                <span class="label">Institute From where appearing</span>
                <span class="underline"style=" width: 780px;">{{ $inst_name }}</span>
                

            </div>
            <div style="margin-top: 10px;">
                <span class="label">Month of Examination</span>
                <span class="underline">{{ $exam_month }}</span>
                <span class="label">Year of Examination</span>
                <span class="underline" style="width: 480px;">{{ $reg_year }}</span>
                
                

            </div>
        </div>
        <div>
            <p style="text-decoration: underline;font-style:italic;">Examination centre with address </p>
            <p style="font-style:italic;">{{ $venue }},{{ $inst_address }}</p>
        </div>
        <div class="footer" style="margin-top: 200px;">
            <div>
                <span style="font-style:italic;">Dated,Kolkate,the</span>
                <span class="underline">{{ $date}}</span>
                <span class="right"style="font-size:16px;">Senior Administrative officer
                    <span style="display: block;text-align:center;">(Examination)</span>
                </span>
                
            </div>
        </div> 
 

    


</body>

</html>
