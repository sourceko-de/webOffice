@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">

<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.css') }}">

<style>
    .fc-event{
        font-size: 10px !important;
    }
</style>
@endpush

@section('content')

    <div class="row">

        <div class="col-md-3">
            <div class="white-box p-t-10 p-b-10 bg-warning">
                <h3 class="box-title text-white">@lang('modules.leaves.pendingLeaves')</h3>
                <ul class="list-inline two-part">
                    <li><i class="icon-logout text-white"></i></li>
                    <li class="text-right"><span id="pendingLeaves" class="counter text-white">{{ count($pendingLeaves) }}</span></li>
                </ul>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="white-box">
                <div class="row">
                    <h3 class="box-title col-md-3">@lang('app.menu.leaves')</h3>

                    <div class="col-md-9">
                        <a href="{{ route('admin.leaves.create') }}" class="btn btn-sm btn-success waves-effect waves-light  pull-right">
                            <i class="ti-plus"></i> @lang('modules.leaves.assignLeave')
                        </a>

                    </div>

                </div>


                <div id="calendar"></div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('modules.leaves.pendingLeaves')</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <ul class="list-task list-group" data-role="tasklist">
                            @forelse($pendingLeaves as $key=>$pendingLeave)
                                <li class="list-group-item" data-role="task">
                                    {{ ($key+1) }}. <strong>{{ ucwords($pendingLeave->user->name) }}</strong> for {{ $pendingLeave->leave_date->format($global->date_format) }} ({{ $pendingLeave->leave_date->format('l') }})
                                    <br>
                                    <strong>@lang('app.reason'): </strong>{{ $pendingLeave->reason }}
                                    <br>
                                    <div class="m-t-10"></div>
                                    <a href="javascript:;" data-leave-id="{{ $pendingLeave->id }}" data-leave-action="approved" class="btn btn-xs btn-success btn-rounded m-r-5 leave-action"><i class="fa fa-check"></i> @lang('app.accept')</a>

                                    <a href="javascript:;" data-leave-id="{{ $pendingLeave->id }}" data-leave-action="rejected" class="btn btn-xs btn-danger btn-rounded leave-action-reject"><i class="fa fa-times"></i> @lang('app.reject')</a>
                                </li>
                            @empty
                                @lang('messages.noPendingLeaves')
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- .row -->

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="eventDetailModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn blue">Save changes</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    {{--Ajax Modal Ends--}}

@endsection

@push('footer-script')

<script>
    var taskEvents = [
        @foreach($leaves as $leave)
        {
            id: '{{ ucfirst($leave->id) }}',
            title: '{{ ucfirst($leave->user->name) }}',
            start: '{{ $leave->leave_date }}',
            end:  '{{ $leave->leave_date }}',
            className: 'bg-{{ $leave->type->color }}'
        },
        @endforeach
];

    var getEventDetail = function (id) {
        var url = '{{ route('admin.leaves.show', ':id')}}';
        url = url.replace(':id', id);

        $('#modelHeading').html('Event');
        $.ajaxModal('#eventDetailModal', url);
    }

    var calendarLocale = '{{ $global->locale }}';

    $('.leave-action-reject').click(function () {
        var action = $(this).data('leave-action');
        var leaveId = $(this).data('leave-id');
        var searchQuery = "?leave_action="+action+"&leave_id="+leaveId;
        var url = '{!! route('admin.leaves.show-reject-modal') !!}'+searchQuery;
        $('#modelHeading').html('Reject Reason');
        $.ajaxModal('#eventDetailModal', url);
    });

    $('.leave-action').on('click', function() {
        var action = $(this).data('leave-action');
        var leaveId = $(this).data('leave-id');
        var url = '{{ route("admin.leaves.leaveAction") }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: { 'action': action, 'leaveId': leaveId, '_token': '{{ csrf_token() }}' },
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        });

    })
</script>

<script src="{{ asset('plugins/bower_components/calendar/jquery-ui.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/calendar/dist/jquery.fullcalendar.js') }}"></script>
<script src="{{ asset('plugins/bower_components/calendar/dist/locale-all.js') }}"></script>
<script src="{{ asset('js/event-calendar.js') }}"></script>

@endpush
