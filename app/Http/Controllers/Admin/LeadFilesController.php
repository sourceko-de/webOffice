<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Lead;
use App\LeadFiles;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class LeadFilesController extends AdminBaseController
{

    private $mimeType = [
        'txt' => 'fa-file-text',
        'htm' => 'fa-file-code-o',
        'html' => 'fa-file-code-o',
        'php' => 'fa-file-code-o',
        'css' => 'fa-file-code-o',
        'js' => 'fa-file-code-o',
        'json' => 'fa-file-code-o',
        'xml' => 'fa-file-code-o',
        'swf' => 'fa-file-o',
        'flv' => 'fa-file-video-o',

        // images
        'png' => 'fa-file-image-o',
        'jpe' => 'fa-file-image-o',
        'jpeg' => 'fa-file-image-o',
        'jpg' => 'fa-file-image-o',
        'gif' => 'fa-file-image-o',
        'bmp' => 'fa-file-image-o',
        'ico' => 'fa-file-image-o',
        'tiff' => 'fa-file-image-o',
        'tif' => 'fa-file-image-o',
        'svg' => 'fa-file-image-o',
        'svgz' => 'fa-file-image-o',

        // archives
        'zip' => 'fa-file-o',
        'rar' => 'fa-file-o',
        'exe' => 'fa-file-o',
        'msi' => 'fa-file-o',
        'cab' => 'fa-file-o',

        // audio/video
        'mp3' => 'fa-file-audio-o',
        'qt' => 'fa-file-video-o',
        'mov' => 'fa-file-video-o',
        'mp4' => 'fa-file-video-o',
        'mkv' => 'fa-file-video-o',
        'avi' => 'fa-file-video-o',
        'wmv' => 'fa-file-video-o',
        'mpg' => 'fa-file-video-o',
        'mp2' => 'fa-file-video-o',
        'mpeg' => 'fa-file-video-o',
        'mpe' => 'fa-file-video-o',
        'mpv' => 'fa-file-video-o',
        '3gp' => 'fa-file-video-o',
        'm4v' => 'fa-file-video-o',

        // adobe
        'pdf' => 'fa-file-pdf-o',
        'psd' => 'fa-file-image-o',
        'ai' => 'fa-file-o',
        'eps' => 'fa-file-o',
        'ps' => 'fa-file-o',

        // ms office
        'doc' => 'fa-file-text',
        'rtf' => 'fa-file-text',
        'xls' => 'fa-file-excel-o',
        'ppt' => 'fa-file-powerpoint-o',
        'docx' => 'fa-file-text',
        'xlsx' => 'fa-file-excel-o',
        'pptx' => 'fa-file-powerpoint-o',


        // open office
        'odt' => 'fa-file-text',
        'ods' => 'fa-file-text',
    ];

    /**
     * ManageLeadFilesController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.leadFiles');
        if(config('filesystems.default') == 's3') {
            $this->url = "https://".config('filesystems.disks.s3.bucket').".s3.amazonaws.com/";
        }
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
     * @param Request $request
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $storage = config('filesystems.default');
            $file = new LeadFiles();
            $file->user_id = $this->user->id;
            $file->lead_id = $request->lead_id;
            switch($storage) {
                case 'local':
                    $request->file->storeAs('user-uploads/lead-files/'.$request->lead_id, $request->file->getClientOriginalName());
                    break;
                case 's3':
                    Storage::disk('s3')->putFileAs('lead-files/'.$request->lead_id, $request->file, $request->file->getClientOriginalName(), 'public');
                    break;
                case 'google':
                    $dir = '/';
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                    $dir = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', 'lead-files')
                        ->first();

                    if(!$dir) {
                        Storage::cloud()->makeDirectory('lead-files');
                    }

                    $directory = $dir['path'];
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                    $directory = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', $request->lead_id)
                        ->first();

                    if ( ! $directory) {
                        Storage::cloud()->makeDirectory($dir['path'].'/'.$request->lead_id);
                        $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                        $directory = $contents->where('type', '=', 'dir')
                            ->where('filename', '=', $request->lead_id)
                            ->first();
                    }

                    Storage::cloud()->putFileAs($directory['basename'], $request->file, $request->file->getClientOriginalName());

                    $file->google_url = Storage::cloud()->url($directory['path'].'/'.$request->file->getClientOriginalName());

                    break;
                case 'dropbox':
                    Storage::disk('dropbox')->putFileAs('lead-files/'.$request->lead_id.'/', $request->file, $request->file->getClientOriginalName());
                    $dropbox = new Client(['headers' => ['Authorization' => "Bearer ".config('filesystems.disks.dropbox.token'), "Content-Type" => "application/json"]]);
                    $res = $dropbox->request('POST', 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings',
                        [\GuzzleHttp\RequestOptions::JSON => ["path" => '/lead-files/'.$request->lead_id.'/'.$request->file->getClientOriginalName()]]
                    );
                    $dropboxResult = $res->getBody();
                    $dropboxResult = json_decode($dropboxResult, true);
                    $file->dropbox_link = $dropboxResult['url'];
                    break;
            }

            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $request->file->hashName();
            $file->size = $request->file->getSize();
            $file->save();
        }

        $this->lead = Lead::findOrFail($request->lead_id);
        $this->icon($this->lead);
        if($request->view == 'list') {
            $view = view('admin.lead.lead-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.lead.lead-files.thumbnail-list', $this->data)->render();
        }
        return Reply::successWithData(__('messages.fileUploaded'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->lead = Lead::findOrFail($id);
        return view('admin.lead.lead-files.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    /**
     * @param Request $request
     * @param $id
     * @return array
     * @throws \Throwable
     */
    public function destroy(Request $request, $id)
    {
        $storage = config('filesystems.default');
        $file = LeadFiles::findOrFail($id);
        switch($storage) {
            case 'local':
                File::delete('user-uploads/lead-files/'.$file->lead_id.'/'.$file->filename);
                break;
            case 's3':
                Storage::disk('s3')->delete('lead-files/'.$file->lead_id.'/'.$file->filename);
                break;
            case 'google':
                Storage::disk('google')->delete('lead-files/'.$file->lead_id.'/'.$file->filename);
                break;
            case 'dropbox':
                Storage::disk('dropbox')->delete('lead-files/'.$file->lead_id.'/'.$file->filename);
                break;
        }
        LeadFiles::destroy($id);
        $this->lead = Lead::findOrFail($file->lead_id);
        $this->icon($this->lead);
        if($request->view == 'list') {
            $view = view('admin.lead.lead-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.lead.lead-files.thumbnail-list', $this->data)->render();
        }
        return Reply::successWithData(__('messages.fileDeleted'), ['html' => $view]);
    }


    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id) {
        $storage = config('filesystems.default');
        $file = LeadFiles::findOrFail($id);
        switch($storage) {
            case 'local':
                return response()->download('user-uploads/lead-files/'.$file->lead_id.'/'.$file->filename);
                break;
            case 's3':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('lead-files/'.$file->lead_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
            case 'google':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $dir = '/';
                $recursive = false; // Get subdirectories also?
                $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                $directory = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', 'lead-files')
                    ->first();

                $direct = $directory['path'];
                $recursive = false;
                $contents = collect(Storage::cloud()->listContents($direct, $recursive));
                $directo = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', $file->lead_id)
                    ->first();

                $readStream = Storage::cloud()->getDriver()->readStream($directo['path']);
                return response()->stream(function () use ($readStream) {
                    fpassthru($readStream);
                }, 200, [
                    'Content-Type' => $ext,
                    'Content-disposition' => 'attachment; filename="'.$file->filename.'"',
                ]);
                break;
            case 'dropbox':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('lead-files/'.$file->lead_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
        }
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function thumbnailShow(Request $request)
    {
        $this->lead = Lead::with('files')->findOrFail($request->id);
        $this->icon($this->lead);
        $view = view('admin.lead.lead-files.thumbnail-list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }


    /**
     * @param $leads
     */
    private function icon($leads) {
        foreach ($leads->files as $lead) {
            $ext = pathinfo($lead->filename, PATHINFO_EXTENSION);
            if ($ext == 'png' || $ext == 'jpe' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'gif' || $ext == 'bmp' ||
                $ext == 'ico' || $ext == 'tiff' || $ext == 'tif' || $ext == 'svg' || $ext == 'svgz' || $ext == 'psd')
            {
                $lead->icon = 'images';
            } else {
                $lead->icon = $this->mimeType[$ext];
            }
        }
    }
}
