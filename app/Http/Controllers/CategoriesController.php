<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriesCollection;
use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index(){
        return new CategoriesCollection(Categories::all());
    }
}
