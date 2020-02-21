<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    protected $dates = ['clock_in_time', 'clock_out_time'];
    protected $appends = ['clock_in_date'];
    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function getClockInDateAttribute()
    {
        return $this->clock_in_time->toDateString();
    }

    public static function attendanceByDate($date) {
        DB::statement("SET @attendance_date = '$date'");
        return User::withoutGlobalScope('active')
        ->leftJoin(
                'attendances', function ($join) use ($date) {
                    $join->on('users.id', '=', 'attendances.user_id')
                        ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $date)
                        ->whereNull('attendances.clock_out_time');
                }
            )
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->where('roles.name', '<>', 'client')
            ->select(
                DB::raw("( select count('atd.id') from attendances as atd where atd.user_id = users.id and DATE(atd.clock_in_time)  =  '".$date."' and DATE(atd.clock_out_time)  =  '".$date."' ) as total_clock_in"),
                DB::raw("( select count('atdn.id') from attendances as atdn where atdn.user_id = users.id and DATE(atdn.clock_in_time)  =  '".$date."' ) as clock_in"),
                'users.id',
                'users.name',
                'attendances.clock_in_ip',
                'attendances.clock_in_time',
                'attendances.clock_out_time',
                'attendances.late',
                'attendances.half_day',
                'attendances.working_from',
                'users.image',
                'employee_details.job_title',
                DB::raw('@attendance_date as atte_date'),
                'attendances.id as attendance_id'
            )
            ->groupBy('users.id')
            ->orderBy('users.name', 'asc');
    }

    public static function attendanceDate($date) {

        return User::with(['attendance' => function ($q) use ($date) {
            $q->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $date);
        }])
        ->withoutGlobalScope('active')
        ->join('role_user', 'role_user.user_id', '=', 'users.id')
        ->join('roles', 'roles.id', '=', 'role_user.role_id')
        ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
        ->where('roles.name', '<>', 'client')
        ->select(
            'users.id',
            'users.name',
            'users.image',
            'employee_details.job_title'
        )
        ->groupBy('users.id')
        ->orderBy('users.name', 'asc');
    }

    public static function attendanceHolidayByDate($date) {
        $holidays = Holiday::all();
        $user =  User::leftJoin(
                'attendances', function ($join) use ($date) {
                    $join->on('users.id', '=', 'attendances.user_id')
                        ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $date);
                }
            )
            ->withoutGlobalScope('active')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->where('roles.name', '<>', 'client')
            ->select(
                'users.id',
                'users.name',
                'attendances.clock_in_ip',
                'attendances.clock_in_time',
                'attendances.clock_out_time',
                'attendances.late',
                'attendances.half_day',
                'attendances.working_from',
                'users.image',
                'employee_details.job_title',
                'attendances.id as attendance_id'
            )
            ->groupBy('users.id')
            ->orderBy('users.name', 'asc')
        ->union($holidays)
        ->get();
        return $user;
    }

    public static function userAttendanceByDate($startDate, $endDate, $userId) {
        return Attendance::join('users', 'users.id', '=', 'attendances.user_id')
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('attendances.user_id', '=', $userId)
            ->orderBy('attendances.id', 'desc')
            ->select('attendances.*', 'users.*', 'attendances.id as aId')
            ->get();
    }

    public static function countDaysPresentByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->count();
    }

    public static function countDaysLateByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->where('late', 'yes')
            ->count();
    }

    public static function countHalfDaysByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->where('half_day', 'yes')
            ->count();
    }

    // Get User Clock-ins by date
    public static function getTotalUserClockIn($date, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $date)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $date)
            ->where('user_id', $userId)
            ->count();
    }

    // Attendance by User and date
    public static function attedanceByUserAndDate($date, $userId){
        return Attendance::where('user_id', $userId)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', "$date")->get();
    }

}
