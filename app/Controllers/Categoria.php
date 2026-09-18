<?php

namespace App\Controllers;

use App\Models\CategoriasModel;

class Categoria extends BaseController
{

    public function show($id)
    {
        $categoriasmodel = new CategoriasModel();

        echo view('categoria/show',[
            'categoria' =>$categoriasmodel->find($id)
        ]);

    }

public function new()
    {
        echo view('categoria/new',[
            'categoria' =>[
                'titulo' => '',
            ]
        ]);
    }
    public function create()
    {

        $categoriasmodel = new CategoriasModel();

        $categoriasmodel->insert([
            'titulo'=>$this->request->getPost('titulo'),
        ]);

        echo 'creado';
        
    }

    public function edit($id)
    {
        $categoriasModel = new CategoriasModel();

        echo view('categoria/edit',[
            'categoria' => $categoriasModel->find($id),
        ]);
    }

    public function update($id)
    {

       $categoriasModel = new CategoriasModel();

       $categoriasModel->update($id,[
        'titulo'=> $this->request->getPost('titulo'),
       ]);

       echo 'update';
    }

    public function delete($id)
    {
        $categoriasModel = new CategoriasModel();
        $categoriasModel->delete($id);

        echo 'delete';
    }

    



    public function index()
    {
    
        $categoriasModel = new CategoriasModel();

        echo view('categoria/index',[
            'categoria'=>$categoriasModel->findAll(),
        ]);
    }

}
    