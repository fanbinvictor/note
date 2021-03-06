<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;
use App\Models\User;
use App\Models\Link;

class TopicsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic, User $user, Link $link)
    {
        $topics = $topic->withOrder($request->order)->paginate(20);
        $active_users = $user->getActiveUsers();
        $links = $link->getAllCached();
        $categories=$this->menu;
        return view('topics.index', compact('topics', 'active_users', 'links','categories'));
    }

    public function show(Request $request, Topic $topic)
    {
        // URL 矫正
        if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }
        $categories=$this->menu;
        return view('topics.show', compact('topic', 'categories'));
    }

    public function create(Topic $topic)
    {
        $categories=$this->menu;
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();

        return redirect()->to($topic->link())->with('success', '成功创建主题！');
    }

	public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);
        $categories=$this->menu;
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->to($topic->link())->with('success', '更新成功！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', '成功删除！');
	}

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认是失败的
//        $data = [
//            'success'   => false,
//            'msg'       => '上传失败!',
//            'file_path' => ''
//        ];
        $data= [
            "uploaded" => 0,
            "fileName" => '',
            "url" => '',
            "error" => [
                "message" => '上传失败'
            ]
        ];
        // 判断是否有上传文件，并赋值给 $file
        if ($file = $request->upload) {
            // 保存图片到本地
            $result = $uploader->save($request->upload, 'topics', \Auth::id(), 1024);
            // 图片保存成功的话
            if ($result) {
//                $data['file_path'] = $result['path'];
//                $data['msg']       = "上传成功!";
//                $data['success']   = true;
                $data= [
                    "uploaded" => 1,
                    "fileName" => '',
                    "url" =>$result['path'],
                    "error" => [
                        "message" => '上传成功'
                    ]
                ];
            }
        }
        return $data;
    }
}
