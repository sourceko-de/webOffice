<?php

namespace App\Http\Controllers\Member;

use App\Helper\Reply;
use App\Http\Controllers\Member\MemberBaseController;
use App\Http\Requests\Tasks\StoreTask;
use App\ModuleSetting;
use App\Notifications\NewTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskUpdated;
use App\Project;
use App\ProjectMember;
use App\Task;
use App\TaskboardColumn;
use App\TaskCategory;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\NewClientTask;

class MemberAllTasksController extends MemberBaseController
{
    use ProjectProgress;

    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.tasks');
        $this->pageIcon = 'ti-layout-list-thumb';
        $this->middleware(function ($request, $next) {
            if (!in_array('tasks',$this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });

    }

    public function index() {
        if($this->user->can('view_tasks')){
            $this->projects = Project::all();
        }
        else{
            $this->projects = Project::byEmployee($this->user->id);
        }

        return view('member.all-tasks.index', $this->data);
    }

    public function data($startDate = null, $endDate = null, $hideCompleted = null, $projectId = null) {
        $taskBoardColumn = TaskboardColumn::where('slug', 'completed')->first();
        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name', 'users.image', 'tasks.due_date', 'taskboard_columns.column_name', 'taskboard_columns.label_color', 'tasks.project_id');

        if(!is_null($startDate)){
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '>=', $startDate);
        }

        if(!is_null($endDate)){
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '<=', $endDate);
        }

        if($projectId != 0){
            $tasks->where('tasks.project_id', '=', $projectId);
        }

        if($hideCompleted == '1'){
            $tasks->where('tasks.board_column_id', $taskBoardColumn->id);
        }

        if(!$this->user->can('view_tasks')){
            $tasks->where('tasks.user_id', '=', $this->user->id);
        }

        $tasks->get();

        return DataTables::of($tasks)
            ->addColumn('action', function($row){
                $action = '';

                if($this->user->can('edit_tasks')) {
                    $action .= '<a href="' . route('member.all-tasks.edit', $row->id) . '" class="btn btn-info btn-circle"
                      data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                if($this->user->can('delete_tasks')) {
                    $action .= '&nbsp;&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-task-id="' . $row->id . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }
                return $action;
            })
            ->editColumn('due_date', function($row){
                if($row->due_date->isPast()) {
                    return '<span class="text-danger">'.$row->due_date->format($this->global->date_format).'</span>';
                }
                return '<span class="text-success">'.$row->due_date->format($this->global->date_format).'</span>';
            })
            ->editColumn('name', function($row){
                return ($row->image) ? '<img src="'.asset('user-uploads/avatar/'.$row->image).'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name) : '<img src="'.asset('default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name);
            })
            ->editColumn('heading', function($row){
                return '<a href="javascript:;" data-task-id="'.$row->id.'" class="show-task-detail">'.ucfirst($row->heading).'</a>';
            })
            ->editColumn('column_name', function($row){
                return '<label class="label" style="background-color: '.$row->label_color.'">'.$row->column_name.'</label>';
            })
            ->editColumn('project_name', function ($row) {
                if(is_null($row->project_id)){
                    return "";
                }
                return '<a href="' . route('member.projects.show', $row->project_id) . '">' . ucfirst($row->project_name) . '</a>';
            })
            ->rawColumns(['column_name', 'action', 'project_name', 'due_date', 'name', 'heading'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('label_color')
            ->make(true);
    }

    public function edit($id) {
        if(!$this->user->can('edit_tasks')){
            abort(403);
        }
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->task = Task::findOrFail($id);
        $this->projects = Project::all();
        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        return view('member.all-tasks.edit', $this->data);
    }

    public function update(StoreTask $request, $id)
    {
        $task = Task::findOrFail($id);
        $oldStatus = TaskboardColumn::findOrFail($task->board_column_id);

        $task->heading = $request->heading;
        if($request->description != ''){
            $task->description = $request->description;
        }
        $task->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->priority = $request->priority;
        $task->board_column_id = $request->status;
        $task->task_category_id = $request->category_id;

        $taskBoardColumn = TaskboardColumn::findOrFail($request->status);
        if($taskBoardColumn->slug == 'completed'){
            $task->completed_on = Carbon::today()->format('Y-m-d');
        }else{
            $task->completed_on = null;
        }

        $task->project_id = $request->project_id;
        $task->save();

        if($oldStatus->slug == 'incomplete'  && $taskBoardColumn->slug == 'completed'){
            // notify user
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($request->user_id);
            $notifyUser->notify(new TaskCompleted($task));
        }
        else{

            //Send notification to user
            $notifyUser = User::findOrFail($request->user_id);
            $notifyUser->notify(new TaskUpdated($task));
        }

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        return Reply::redirect(route('member.all-tasks.index'), __('messages.taskUpdatedSuccessfully'));
    }

    public function destroy($id) {
        $task = Task::findOrFail($id);
        Task::destroy($id);

        //calculate project progress if enabled
        $this->calculateProjectProgress($task->project_id);

        return Reply::success(__('messages.taskDeletedSuccessfully'));
    }


    public function create() {
        if(!$this->user->can('add_tasks')){
            abort(403);
        }
        $this->projects = Project::all();
        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        return view('member.all-tasks.create', $this->data);
    }

    public function membersList($projectId){
        $this->members = ProjectMember::byProject($projectId);
        $list = view('member.all-tasks.members-list', $this->data)->render();
        return Reply::dataOnly(['html' => $list]);
    }

    public function store(StoreTask $request) {
        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();

        $task = new Task();
        $task->heading = $request->heading;
        if($request->description != ''){
            $task->description = $request->description;
        }
        $task->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->project_id = $request->project_id;
        $task->priority = $request->priority;
        $task->board_column_id = $taskBoardColumn->id;
        $task->task_category_id = $request->category_id;

        if($request->board_column_id){
            $task->board_column_id = $request->board_column_id;
        }
        $task->save();

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

//      Send notification to user
        $notifyUser = User::withoutGlobalScope('active')->findOrFail($request->user_id);
        $notifyUser->notify(new NewTask($task));

        if($task->project_id != null){
            if($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->project->client_id);
                $notifyUser->notify(new NewClientTask($task));
            }
        }

        if(!is_null($request->project_id)){
            $this->logProjectActivity($request->project_id, __('messages.newTaskAddedToTheProject'));
        }

        //log search
        $this->logSearchEntry($task->id, 'Task '.$task->heading, 'admin.all-tasks.edit');

        if($request->board_column_id){
            return Reply::redirect(route('member.taskboard.index'), __('messages.taskCreatedSuccessfully'));
        }
        return Reply::redirect(route('member.all-tasks.index'), __('messages.taskCreatedSuccessfully'));
    }

    public function ajaxCreate($columnId){
        $this->projects = Project::all();
        $this->columnId = $columnId;
        $this->employees = User::allEmployees();
        return view('member.all-tasks.ajax_create', $this->data);
    }

    public function show($id){
        $this->task = Task::findOrFail($id);
        $view = view('member.all-tasks.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

}
