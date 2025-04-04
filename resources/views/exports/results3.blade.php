<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course_duration }} Diploma in Vocational in {{ $course_name }} ({{ $semester }})</title>
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

<body>
    <div class="header">
        <p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
            <span style="font-family:Cambria;font-size:14px;">
                <span style="font-stretch:115%; text-transform: uppercase;">
                    <strong>west bengal state council of technical and vocational education and skill
                        development</strong>
                </span>
            </span>
        </p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                {{ $course_duration }} Diploma in Vocation in {{ $course_name }} &#40;{{ $semester }}&#41;
            </span>
        </p>

        <div class="float-left">
            <p style="text-align: center"><strong>Center:{{ $institute_name }} <strong></p>

        </div>
        <div class="float-right">
            <p style="text-align: center"><strong>Held in: {{ $examYear }}</strong></p>
        </div>
    </div>
    <div class="main">
        <table>
            <thead>
                <tr>
                    <th colspan="1" rowspan="2">REG</th>
                    <th colspan="1" rowspan="2">{{ $institute_code }} /
                        {{ $course_code }} / {{ $course_name }} / {{ $semester }}</th>
                    <th colspan="{{ $th_cnt }}" rowspan="1">THEORITICAL</th>
                    <th colspan="3" rowspan="1">SESSIONAL</th>
                    <th colspan="1" rowspan="2"style="font-weight: bold;">TOTAL</th>
                    <<th colspan="1" rowspan="2"style="font-weight: bold;">REMARKS</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    @foreach ($papers as $key => $row)
                        <td>
                            {{ $row->paper_name }}
                        </td>
                    @endforeach

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
                    <td>{{ $totalFullMarks }}</td>
                    <td colspan="1" rowspan="5">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" rowspan="1">Pass Marks</td>
                    @foreach ($papers as $pkey => $pval)
                        <td>{{ $pval->paper_pass_marks }}</td>
                    @endforeach>
                    <td>-</td>
                </tr>
                <tr>
                    <td colspan="2" rowspan="4">&nbsp;</td>
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
                    <td>ATD</td>
                    <td>ATD</td>
                    <td>ATD</td>
                    <td>ATD</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>EXT</td>
                    <td>EXT</td>
                    <td>EXT</td>
                    <td>EXT</td>
                    <td>EXT</td>
                    <td>EXT</td>
                    <td>EXT</td>
                </tr>
                <tr>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>TOT</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>NUMBER</td>
                    <td>NAME OF THE CANDIDATE</td>
                    <td colspan="8" rowspan="1">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    //Start Student Dynamic
                    
                @endphp
            
                @foreach ($students as $skey => $student)
                       @php
                    //Start Student Dynamic
                    $sub1_int_marks =   $student['sub1_th_int_marks'];
                    $sub2_int_marks =   $student['sub2_th_int_marks'];
                    $sub3_int_marks =   $student['sub3_th_int_marks'];
                    $sub4_int_marks =   $student['sub4_th_int_marks'];
                   
                    $sub9_int_marks =   $student['sub9_sess_int_marks'];
                    $sub10_int_marks =   $student['sub10_sess_int_marks'];
                    $sub11_int_marks =   $student['sub11_sess_int_marks'];

                    $sub1_ext_marks =   $student['sub1_th_ext_marks'];
                    $sub2_ext_marks =   $student['sub2_th_ext_marks'];
                    $sub3_ext_marks =   $student['sub3_th_ext_marks'];
                    $sub4_ext_marks =   $student['sub4_th_ext_marks'];
                   
                    $sub9_ext_marks =   $student['sub9_ext_session_marks'];
                    $sub10_ext_marks =   $student['sub10_ext_session_marks'];
                    $sub11_ext_marks =   $student['sub11_ext_session_marks'];

                    $sub1_ob_marks =   $student['SUB1_OB_MK'];
                    $sub2_ob_marks =   $student['SUB2_OB_MK'];
                    $sub3_ob_marks =   $student['SUB3_OB_MK'];
                    $sub4_ob_marks =   $student['SUB4_OB_MK'];
                    $sub9_ob_marks =   $student['SUB9_OB_MK'];
                    $sub10_ob_marks =   $student['SUB10_OB_MK'];
                    $sub11_ob_marks =   $student['SUB11_OB_MK'];
                  
                @endphp
             
                    <tr>
                        <td colspan="1" rowspan="4">{{ $student['student_reg_no'] }}</td>
                        <td colspan="1" rowspan="4">{{ $student['student_name'] }}</td>
                        <td>{{ $sub1_int_marks == 0 ? 'AB' : $sub1_int_marks  }}</td>
                        <td>{{ $sub2_int_marks == 0 ? 'AB' : $sub2_int_marks }}</td>
                        <td>{{ $sub3_int_marks == 0 ? 'AB' : $sub3_int_marks }}</td>
                        <td>{{ $sub4_int_marks == 0 ? 'AB' : $sub4_int_marks }}</td>
                        <td>{{ $sub9_int_marks == 0 ? 'AB' : $sub9_int_marks }}</td>
                        <td>{{ $sub10_int_marks == 0 ? 'AB' : $sub10_int_marks }}</td>
                        <td>{{ $sub11_int_marks == 0 ? 'AB' : $sub11_int_marks }}</td>
                        @php
                            $grandtotal = $student['GRAND_TOTAL'];
                        @endphp
                        {{-- <td colspan="1" rowspan="4">{{ $student['GRAND_TOTAL'] }}</td> --}}
                        <td colspan="1" rowspan="4">{{ $grandtotal == 0 ? 'Absent' : $grandtotal }}</td>
                        <td colspan="1" rowspan="4">{{ $student['RESULT'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $student['sub1_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub2_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub3_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub4_th_int_attd_marks'] }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                    <td>{{ $sub1_ext_marks == 0 ? 'AB' : $sub1_ext_marks  }}</td>
                        <td>{{ $sub2_ext_marks == 0 ? 'AB' : $sub2_ext_marks  }}</td>
                        <td>{{ $sub3_ext_marks == 0 ? 'AB' : $sub3_ext_marks  }}</td>
                        <td>{{ $sub4_ext_marks == 0 ? 'AB' : $sub4_ext_marks  }}</td>
                        <td>{{ $sub9_ext_marks == 0 ? 'AB' : $sub9_ext_marks  }} </td>
                        <td>{{ $sub10_ext_marks == 0 ? 'AB' : $sub10_ext_marks  }}</td>
                        <td>{{ $sub11_ext_marks == 0 ? 'AB' : $sub11_ext_marks  }}</td>
                    </tr>
                    <tr>
                        <td> {{ $sub1_ob_marks == 0 ? 'AB' : $sub1_ob_marks  }}</td>
                        <td> {{ $sub2_ob_marks == 0 ? 'AB' : $sub2_ob_marks  }}</td>
                        <td> {{ $sub3_ob_marks == 0 ? 'AB' : $sub3_ob_marks  }}</td>
                        <td> {{ $sub4_ob_marks == 0 ? 'AB' : $sub4_ob_marks  }}</td>
                        <td> {{ $sub9_ob_marks == 0 ? 'AB' : $sub9_ob_marks  }}</td>
                        <td> {{ $sub10_ob_marks == 0 ? 'AB' : $sub10_ob_marks  }}</td>
                        <td> {{ $sub11_ob_marks == 0 ? 'AB' : $sub11_ob_marks  }}</td>
                    </tr>
                    {{-- @if ($student['SUB1_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB1_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB2_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB2_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB3_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB3_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB4_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB4_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB6_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB6_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB7_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB7_OB_MK'] }}
                        </td>
                        @endif
                          @if ($student['SUB8_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB8_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB9_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB9_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB10_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB10_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB11_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB11_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB12_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB12_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB13_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB13_OB_MK'] }}
                        </td>
                        @endif
                        @if ($student['SUB14_OB_MK'] == 0)
                        <td></td>
                        @else<td>
                        {{ $student['SUB14_OB_MK'] }}
                        </td>
                        @endif
                    </tr> --}}

            </tbody>
            @endforeach
            @php
                //End Student Dynamic
            @endphp
        </table>



    </div>


</body>

</html>
