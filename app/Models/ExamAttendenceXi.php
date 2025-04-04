<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttendenceXi extends Model
{
    use HasFactory;

    protected $table        =   'wbscte_council_student_master_xi_tbl';
    protected $primaryKey   =   'exam_xi_att_id_pk';
    public $timestamps      =   false; 

    protected $fillable = [
        "exam_xi_att_id_pk"	,
        "exam_xi_att_session",
        "exam_xi_att_reg_no",
        "exam_xi_att_vtc_code",
        "exam_xi_att_discipline_id",
        "exam_xi_att_group_code",
        "exam_xi_att_room_no",
        "exam_xi_att_exam_year",
        "exam_xi_att_subj_papr_code",
        "exam_xi_att_is_present",
        "exam_xi_att_is_absent",
        "exam_xi_att_is_ra"	,
        "exam_xi_att_is_final_submit",
        "exam_xi_att_created_on",
        "exam_xi_att_created_by",
        "exam_xi_att_modified_on",
        "exam_xi_att_modified_by",
        "exam_xi_att_final_submit_by"
    ];
}
