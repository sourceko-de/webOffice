<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ProjectTimeLog extends Model
{
    use Notifiable;

    protected $dates = ['start_time', 'end_time'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function editor() {
        return $this->belongsTo(User::class, 'edited_by_user')->withoutGlobalScopes(['active']);
    }

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task() {
        return $this->belongsTo(Task::class, 'task_id');
    }

    protected $appends = [ 'hours', 'duration', 'timer'];

    public function getDurationAttribute(){
        $finishTime = Carbon::now();
        if(!is_null($this->start_time)){
            return $finishTime->diff($this->start_time)->format('%d days %H Hrs %i Mins %s Secs');
        }
        return "";
    }

    public function getHoursAttribute(){
        $timeLog = intdiv($this->total_minutes, 60).' hrs ';

        if(($this->total_minutes % 60) > 0){
            $timeLog.= ($this->total_minutes % 60).' mins';
        }

        return $timeLog;
    }

    public function getTimerAttribute(){
        $finishTime = Carbon::now();
        $settings = Setting::findOrFail(1);
        $startTime = Carbon::parse($this->start_time)->timezone($settings->timezone);
        $days = $finishTime->diff($startTime)->format('%d');
        $hours = $finishTime->diff($startTime)->format('%H');
        if($hours < 10){
            $hours = '0'.$hours;
        }
        $mins = $finishTime->diff($startTime)->format('%i');
        if($mins < 10){
            $mins = '0'.$mins;
        }
        $secs = $finishTime->diff($startTime)->format('%s');
        if($secs < 10){
            $secs = '0'.$secs;
        }
        return ($days*24)+$hours.":".$mins.":".$secs;
    }

    public static function projectActiveTimers($projectId) {
        return ProjectTimeLog::whereNull('end_time')
            ->where('project_id', $projectId)
            ->get();
    }

    public static function projectTotalHours($projectId) {
        return ProjectTimeLog::where('project_id', $projectId)
            ->sum('total_hours');
    }

    public static function memberActiveTimer($memberId) {
        return ProjectTimeLog::where('user_id', $memberId)
            ->whereNull('end_time')
            ->first();
    }

}
