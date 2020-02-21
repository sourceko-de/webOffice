<?php

namespace App;

use App\Traits\CustomFieldsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use CustomFieldsTrait; use SoftDeletes;

    protected $dates = ['start_date', 'deadline'];

    protected $appends = ['isProjectAdmin'];

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScopes(['active']);
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id')->orderBy('id', 'desc');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id')->orderBy('id', 'desc');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'project_id')->orderBy('id', 'desc');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'project_id')->orderBy('id', 'desc');
    }

    public function times()
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_id')->orderBy('id', 'desc');
    }

    /**
     * @return bool
     */
    public function checkProjectUser()
    {
        $project = ProjectMember::where('project_id', $this->id)
            ->where('user_id', auth()->user()->id)
            ->count();

        if($project > 0)
        {
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @return bool
     */
    public function checkProjectClient()
    {
        $project = Project::where('id', $this->id)
            ->where('client_id', auth()->user()->id)
            ->count();

        if($project > 0)
        {
            return true;
        }
        else{
            return false;
        }
    }

    public static function clientProjects($clientId) {
        return Project::where('client_id', $clientId)->get();
    }

    public static function byEmployee($employeeId) {
        return Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', $employeeId)
            ->get();
    }

    public function scopeCompleted($query){
        return $query->where('completion_percent', '100');
    }

    public function scopeinProcess($query){
        return $query->where('completion_percent', '<>', '100');
    }

    public function scopeOverdue($query){
        $setting = Setting::first();
        return $query->where('completion_percent', '<>', '100')
            ->where('deadline', '<', Carbon::today()->timezone($setting->timezone));
    }

    public function getIsProjectAdminAttribute(){
        if($this->project_admin == auth()->user()->id){
            return true;
        }
        return false;
    }

}
