<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course_duration }} Diploma in Vocational in {{ $course_name }} ({{ $semester }})</title>
    <style>
        @page {
            margin-top: 140px;
        }

        .main {
            text-align: center;

        }

        table {
            /* border: 1px solid #000000; */
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
            width: 281mm;
            font-size: 11px;

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
            top: -120px;
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

//echo $th_cnt .'===='.$ses_cnt; die();
$tot_paper_cnt = $th_cnt + $ses_cnt;
$row_span = $tot_paper_cnt > 7 ? 6 : 5;
//echo $th_cnt die();
?>

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
            <p style="text-align: center"><strong>Center: {{ $institute_name }}<strong></p>

        </div>
        <div class="float-right">
            <p style="text-align: center"><strong>Held in: {{ $examYear }}</strong></p>


        </div>
    </div>

    <div class="main">

        <table>
            <tbody>
                <thead>
                    <tr>
                        <th colspan="1" rowspan="2">REG</th>
                        <th colspan="1" rowspan="2"> {{ $institute_code }} /
                            {{ $course_code }} / {{ $course_name }} / {{ $semester }}</th>
                        <th colspan="5" rowspan="1">THEORITICAL</th>
                        <th colspan="3" rowspan="1">SESSIONAL</th>
                        <th colspan="1" rowspan="2"style="font-weight: bold;">TOTAL</th>
                        <th colspan="1" rowspan="2"style="font-weight: bold;">REMARKS</th>

                    </tr>
                    <tr>
                        @foreach ($papers as $key => $row)
                            <td>
                                {{ $row->paper_name }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="2" rowspan="1"style="font-weight: bold;">Full Marks</td>
                        @foreach ($papers as $k => $val)
                            @php
                                $total[] = $val->paper_full_marks;
                                $totalFullMarks = array_sum($total);
                            @endphp
                            <td>{{ $val->paper_full_marks }}</td>
                        @endforeach

                        <td>{{ $totalFullMarks }}</td>
                        <td>&nbsp;</td>

                    </tr>
                    <tr>
                        <td colspan="2" rowspan="1"style="font-weight: bold;">Pass Marks</td>
                        @foreach ($papers as $pkey => $pval)
                            <td>{{ $pval->paper_pass_marks }}</td>
                        @endforeach
                        <td>-</td>
                        <td colspan="1" rowspan="4">&nbsp;</td>
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
                        <td>INT</td>
                        <td colspan="1" rowspan="4">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                    </tr>
                    <tr>
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
                        <td>TOT</td>
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
                        <td colspan="10" rowspan="1">&nbsp;</td>
                    </tr>
                </thead>

                @foreach ($students as $skey => $student)
                    <tr>

                        <td>{{ $student['student_reg_no'] }}</td>
                        <td>{{ $student['student_name'] }}</td>
                        <td>{{ $student['sub1_th_int_marks'] }}</td>
                        <td>{{ $student['sub2_th_int_marks'] }}</td>
                        <td>{{ $student['sub3_th_int_marks'] }}</td>
                        <td>{{ $student['sub4_th_int_marks'] }}</td>
                        <td>{{ $student['sub5_th_int_marks'] }}</td>
                        <td>{{ $student['sub9_sess_int_marks'] }}</td>
                        <td>{{ $student['sub10_sess_int_marks'] }}</td>
                        <td>{{ $student['sub11_sess_int_marks'] }}</td>
                        <td colspan="1" rowspan="4">{{ $student['GRAND_TOTAL'] }}</td>
                        <td colspan="1" rowspan="4">{{ $student['RESULT'] }}</td>
                    </tr>
                    <tr>
                        <td rowspan="1">&nbsp;</td>
                        <td rowspan="1">&nbsp;</td>
                        <td>{{ $student['sub1_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub2_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub3_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub4_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub5_th_int_attd_marks'] }}</td>
                        <td>{{ $student['sub9_sess_int_attd_marks'] }}</td>
                        <td>{{ $student['sub10_sess_int_attd_marks'] }}</td>
                        <td>{{ $student['sub11_sess_int_attd_marks'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{{ $student['sub1_th_ext_marks'] }}</td>
                        <td>{{ $student['sub2_th_ext_marks'] }}</td>
                        <td>{{ $student['sub3_th_ext_marks'] }}</td>
                        <td>{{ $student['sub4_th_ext_marks'] }}</td>
                        <td>{{ $student['sub5_th_ext_marks'] }}</td>
                        <td>{{ $student['sub9_ext_session_marks'] }}</td>
                        <td>{{ $student['sub10_ext_session_marks'] }}</td>
                        <td>{{ $student['sub11_ext_session_marks'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        @if ($student['SUB1_OB_MK'] == 0)
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
                        @if ($student['SUB5_OB_MK'] == 0)
                            <td></td>
                        @else<td>
                                {{ $student['SUB5_OB_MK'] }}
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
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                        {{-- <td>0</td> --}}
                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>
</body>
