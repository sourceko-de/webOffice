<?php

namespace App\Http\Controllers\Member;

use App\ModuleSetting;
use App\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberCalendarController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.taskCalendar');
        $this->pageIcon = 'icon-calender';
        $this->middleware(function ($request, $next) {
            if(!in_array('tasks',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index() {
        $this->tasks = Task::where('status', 'incomplete')->where('user_id', $this->user->id)->get();
        return view('member.task-calendar.index', $this->data);
    }

    public function show($id) {
        $this->task = Task::findOrFail($id);
        return view('member.task-calendar.show', $this->data);
    }
}
