<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Tasks\StoreTask;
use App\ModuleSetting;
use App\Notifications\NewClientTask;
use App\Notifications\NewTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskUpdated;
use App\Notifications\TaskUpdatedClient;
use App\Project;
use App\SubTask;
use App\Task;
use App\TaskboardColumn;
use App\TaskCategory;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;

class ManageTasksController extends AdminBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.projects');
        $this->middleware(function ($request, $next) {
            if (!in_array('tasks', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTask $request)
    {
        $task = new Task();
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();

        $task->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->project_id = $request->project_id;
        $task->priority = $request->priority;
        $task->board_column_id = $taskBoardColumn->id;
        $task->task_category_id = $request->category_id;
        $task->save();

        // Send notification to user
        $notifyUser = User::withoutGlobalScope('active')->findOrFail($request->user_id);
        $notifyUser->notify(new NewTask($task));

        if ($task->project_id != null) {
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->project->client_id);
                $notifyUser->notify(new NewClientTask($task));
            }
        }

        $this->logProjectActivity($request->project_id, __('messages.newTaskAddedToTheProject'));

        $this->project = Project::findOrFail($task->project_id);
        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        //log search
        $this->logSearchEntry($task->id, 'Task: ' . $task->heading, 'admin.all-tasks.edit');

        return Reply::successWithData(__('messages.taskCreatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id);
        $this->categories = TaskCategory::all();

        return view('admin.projects.tasks.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->task = Task::findOrFail($id);
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->categories = TaskCategory::all();
        $view = view('admin.projects.tasks.edit', $this->data)->render();
        return Reply::dataOnly(['html' => $view]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTask $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->priority = $request->priority;
        $task->task_category_id = $request->category_id;
        $task->board_column_id = $request->status;

        $taskBoardColumn = TaskboardColumn::findOrFail($request->status);
        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::now();
        } else {
            $task->completed_on = null;
        }

        $task->save();

        //  Send notification to user
        $notifyUser = User::findOrFail($request->user_id);
        $notifyUser->notify(new TaskUpdated($task));

        if ($task->project_id != null) {
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->project->client_id);
                $notifyUser->notify(new TaskUpdatedClient($task));
            }
        }

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        $this->project = Project::findOrFail($task->project_id);

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.taskUpdatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $taskId = $request->taskId;
        $status = $request->status;
        $taskBoardColumn = TaskboardColumn::where('slug', $status)->first();
        $task = Task::with('project')->findOrFail($taskId);
        $task->board_column_id = $taskBoardColumn->id;
        //        $task->status = $status;

        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::now();

            // send task complete notification
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->user_id);
            $notifyUser->notify(new TaskCompleted($task));

            if ($task->project_id != null) {
                if ($task->project->client_id != null  && $task->project->allow_client_notification == 'enable') {
                    $notifyClient = User::findOrFail($task->project->client_id);
                    $notifyClient->notify(new TaskCompleted($task));
                }
            }

            $admins = User::allAdmins($task->user_id);

            Notification::send($admins, new TaskCompleted($task));
        } else {
            $task->completed_on = null;
        }


        $task->save();
        
        if ($task->project_id != null) {
            if ($task->project->calculate_task_progress == "true") {
                //calculate project progress if enabled
                $this->calculateProjectProgress($task->project_id);
            }
            $this->project = Project::find($task->project_id);
            $this->project->tasks = Task::whereProjectId($this->project->id)->orderBy($request->sortBy, 'desc')->get();   
        }

        $this->task = $task;

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.taskUpdatedSuccessfully'), ['html' => $view, 'textColor' => $task->board_column->label_color, 'column' => $task->board_column->column_name]);
    }

    public function sort(Request $request)
    {
        $projectId = $request->projectId;
        $this->sortBy = $request->sortBy;
        $taskBoardColumn = TaskboardColumn::where('slug', 'completed')->first();
        $this->project = Project::findOrFail($projectId);
        if ($request->sortBy == 'due_date') {
            $order = "asc";
        } else {
            $order = "desc";
        }

        $tasks = Task::whereProjectId($projectId)->orderBy($request->sortBy, $order);

        if ($request->hideCompleted == '1') {
            $tasks->where('board_column_id', '!=', $taskBoardColumn->id);
        }

        $this->project->tasks = $tasks->get();

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::dataOnly(['html' => $view]);
    }

    public function checkTask($taskID)
    {
        $task = Task::findOrFail($taskID);
        $subTask = SubTask::where('task_id', $taskID)->count();

        return Reply::dataOnly(['taskCount' => $subTask, 'lastStatus' => $task->board_column->slug]);
    }
}
