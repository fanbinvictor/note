<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $menu = null;//菜单数据
    public function __construct()
    {
        $this->menu = Category::all();
    }

    public function view($blade,$pageData=[]){
        $pageData['categories']=$this->menu;
        return view($blade, $pageData);
    }
}
