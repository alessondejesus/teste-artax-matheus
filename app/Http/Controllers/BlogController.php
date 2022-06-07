<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\User;

class BlogController extends Controller
{

    protected $model;

    public function __construct() 
    {
        $this->model = new Blog();
    }

    /**
     * Display a listing of the resource
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->model->paginate();
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
     * @param  \App\Http\Requests\StoreBlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogRequest $request)
    {
        try {
            $request['owner_id'] = \Auth::user()->id;

            $this->model->create($request->all());
            
            return [
                'status' => true,
                'message' => 'Dado registrado com sucesso!'
            ];
        } catch(\Exception $exception) {
            return [
                'status' => false,
                'message' => 'Não foi possível registrar o dado!',
                'error' => $exception->getMessage()
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     * @param  \Illuminate\Http\UpdateBlogRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateBlogRequest $request)
    {
        $blog = $this->model->findOrFail($id);

        if(\Auth::user()->id == $blog->owner_id) {
            $blog->update($request->all());

            return ['status' => true, 'message' => 'Dados atualizados com sucesso!'];
        } else {
            abort(401, 'Você não tem acesso!');
        }
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
}
