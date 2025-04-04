<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1year Diploma in Vocational ingthy bhy</title>
    <style>
 
        @page {
            margin-top: 160px;
        }
              .main {
            text-align: center;
         
        }
           table {
            /* border: 1px solid #000000; */
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
            width: 281mm;
            font-size: 9px;

        }
          table th {
            padding: 3px;
            border: 1px solid #000000;
            font-weight: bold;
            text-align: center
        }

        table td {
            padding: 1px;
            border: 1px solid #000000;
        }
        table tr {
            text-align: center;
            border-bottom: 1px solid #000000;
        }
         .header {
            position: fixed;
            top: -100px;
            text-align: center;
        }
        .float-left {
            float: left;
            margin-right: 20px; 
        }
        .float-right {
            float: right;
            margin-right: 10px;
            
        }
    </style>
</head>
<?php

//when theory paper 5 but no sessional;
$tot_paper_cnt = $th_cnt + $ses_cnt;
$row_span = $tot_paper_cnt > 7 ? 6 : 5;
//echo $th_cnt die();
?>


<body>
<div class="header">
		<p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
				<span style="font-family:Cambria;font-size:14px;">
					<span style="font-stretch:115%; text-transform: uppercase;">
						<strong>west bengal state council of technical and vocational education and skill development</strong>
					</span>
				</span>
		</p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                {{ $course_duration }} Diploma in Vocation in {{ $course_name }} &#40;{{ $semester }}
            </span>
        </p>

        <div class="float-left">
            <p style="text-align: center"><strong>Center: institute_name <strong></p>
           
        </div>
        <div class="float-right">
            <p style="text-align: center"><strong>Held in:   2024</strong></p>
        </div>
    </div>
    {{-- 8 theory 6 sessional --}}
<div class="main">
    <table border="1" cellpadding="1" cellspacing="1" >
        <tbody>
            <tr>
                <td colspan="1" rowspan="2">REG</td>
                <td colspan="1" rowspan="2">{{ $institute_code }} /
                    {{ $course_code }} / {{ $course_name }} / {{ $semester }}</td>
                <td colspan="8" rowspan="1">THEORITICAL</td>
                <td colspan="6" rowspan="1">SESSIONAL</td>
                <td colspan="1" rowspan="2">TOTAL</td>
                <td colspan="1" rowspan="2">REMARKS</td>
            </tr>
            <tr>
                @foreach ($papers as $key => $row)
                <td>
                     {{ $row->paper_name }}
                </td>
        @endforeach 
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">Full Marks</td>
                @foreach ($papers as $k => $val)
                                            @php
                                                $total[] = $val->paper_full_marks;
                                                $totalFullMarks = array_sum($total);
                                            @endphp
                                            <td>{{ $val->paper_full_marks }}</td>
                            @endforeach
                {{-- <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td> --}}
                <td>{{ $totalFullMarks }}</td>
                <td colspan="1" rowspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">Pass marks</td>
                @foreach ($papers as $pkey => $pval)
                <td>{{ $pval->paper_pass_marks }}</td>
        @endforeach
                {{-- <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td> --}}
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">&nbsp;</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td>INT</td>
                <td colspan="1" rowspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">&nbsp;</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>ATD</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">&nbsp;</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
                <td>EXT</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1">&nbsp;</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td>TOT</td>
                <td colspan="1" rowspan="2">&nbsp;</td>
            </tr>
            <tr style="">
                <td>Number</td>
                <td>Name of The candidate</td>
                <td colspan="15" rowspan="1">&nbsp;</td>
            </tr>
            <tr>
                @php
                //Start Student Dynamic
            @endphp
            @foreach ($students as $skey => $student)
                <tr>
                    <td>{{ $student['student_reg_no'] }}</td>
                    <td colspan="1" rowspan="5">{{ $student['student_name'] }}</td>
                    <td>{{ $student['sub1_th_int_marks'] }}</td>
                    <td>{{ $student['sub2_th_int_marks'] }}</td>
                    <td>{{ $student['sub3_th_int_marks'] }}</td>
                    <td>{{ $student['sub4_th_int_marks'] }}</td>
                    <td>{{ $student['sub5_th_int_marks'] }}</td>
                    <td>{{ $student['sub6_th_int_marks'] }}</td>
                    <td>{{ $student['sub7_th_int_marks'] }}</td>
                    <td>{{ $student['sub8_th_int_marks'] }}</td>
                    <td>{{ $student['sub9_th_int_marks'] }}</td>
                    <td>{{ $student['sub10_th_int_marks'] }}</td>
                    <td>{{ $student['sub11_th_int_marks'] }}</td>
                    <td>{{ $student['sub12_th_int_marks'] }}</td>
                    <td>{{ $student['sub13_th_int_marks'] }}</td>
                    <td>{{ $student['sub14_th_int_marks'] }}</td>
                    <td>&nbsp;</td>
                    <td colspan="1" rowspan="5">{{ $student['GRAND_TOTAL'] }}</td>
                    <td colspan="1" rowspan="5">{{ $student['RESULT'] }}</td>
                </tr>
                <tr>
                    <td colspan="1" rowspan="4">&nbsp;</td>
                    <td>{{ $student['sub1_th_int_attd_marks'] }}</td>
                    <td>{{ $student['sub2_th_int_attd_marks'] }}</td>
                    <td>{{ $student['sub3_th_int_attd_marks'] }}</td>
                    <td>{{ $student['sub4_th_int_attd_marks'] }}</td>
                    <td>{{ $student['sub5_th_int_attd_marks'] }}</td>
                    <td>{{ $student['sub6_th_int_attd_marks'] }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>{{ $student['sub1_th_ext_marks'] }}</td>
                    <td>{{ $student['sub2_th_ext_marks'] }}</td>
                    <td>{{ $student['sub3_th_ext_marks'] }}</td>
                    <td>{{ $student['sub4_th_ext_marks'] }}</td>
                    <td>{{ $student['sub5_th_ext_marks'] }}</td>
                    <td>{{ $student['sub6_th_ext_marks'] }}</td>
                    <td>{{ $student['sub9_ext_session_marks'] }}</td>
                    <td>{{ $student['sub10_ext_session_marks'] }}</td>
                    <td>{{ $student['sub11_ext_session_marks'] }}</td>
                    <td>{{ $student['sub12_ext_session_marks'] }}</td>
                    <td>{{ $student['sub13_ext_session_marks'] }}</td>
                    <td>{{ $student['sub14_ext_session_marks'] }}</td>

                   @if($student['SUB8_OB_MK'] == 0)
                    <td style="padding: 7px 0;"></td>
                    @else<td>
                    {{ $student['SUB8_OB_MK'] }}
                    </td>
                    @endif
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                @if($student['SUB1_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB1_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB2_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB2_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB3_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB3_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB4_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB4_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB6_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB6_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB7_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB7_OB_MK'] }}
                    </td>
                    @endif
                      @if($student['SUB8_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB8_OB_MK'] }}
                    </td>
                    @endif
                    @if($student['SUB9_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB9_OB_MK'] }}
                    </td>
                    @if($student['SUB10_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB10_OB_MK'] }}
                    </td>
                    @if($student['SUB11_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB12_OB_MK'] }}
                    </td>
                    @if($student['SUB13_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB13_OB_MK'] }}
                    </td>
                    @if($student['SUB14_OB_MK'] == 0)
                    <td></td>
                    @else<td>
                    {{ $student['SUB14_OB_MK'] }}
                    </td>
                    
                </tr>
            @endforeach
                @php
                //End Student Dynamic
                @endphp
            </tr>
        </tbody>
    </table>
</div>  
</body>

</html>  