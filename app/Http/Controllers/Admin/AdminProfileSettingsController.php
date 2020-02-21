<?php

namespace App\Http\Controllers\Admin;

use App\EmployeeDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminProfileSettingsController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-user';
        $this->pageTitle = __('app.menu.profileSettings');
        $this->tutorialUrl = 'https://www.youtube.com/watch?v=GXtfJ3DVCmQ';
    }

    public function index(){
        $this->userDetail = $this->user;
        $this->employeeDetail = EmployeeDetails::where('user_id', '=', $this->userDetail->id)->first();

        return view('admin.profile.index', $this->data);
    }

}
