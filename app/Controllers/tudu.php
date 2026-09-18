<?php

namespace App\Controllers;

use App\Models\TuduModel;

class Tudu extends BaseController
{

    public function show($id)
    {
        $tudumodel = new Tudumodel();

        echo view('tudu/show',[
            'tudu' =>$tudumodel->find($id)
        ]);

    }

    public function create()
    {

        $tudumodel = new TuduModel();

        $tudumodel->insert([
            'titulo'=>$this->request->getPost('titulo'),
            'descripcion'=>$this->request->getPost('descripcion'),
        ]);

        echo 'creado';
        
    }

    public function edit($id)
    {
        $tuduModel = new TuduModel();

        echo view('tudu/edit',[
            'tudu' => $tuduModel->find($id),
        ]);
    }

    public function update($id)
    {

       $tuduModel = new TuduModel();

       $tuduModel->update($id,[
        'titulo'=> $this->request->getPost('titulo'),
        'descripcion'=> $this->request->getPost('descripcion')
       ]);

       echo 'update';
    }

    public function delete($id)
    {
        $tuduModel = new TuduModel();
        $tuduModel->delete($id);

        echo 'delete';
    }

    public function new()
    {
        echo view('tudu/new',[
            'tudu' =>[
                'titulo' => '',
                'descripcion' => ''
            ]
        ]);
    }



    public function index()
    {
    
        $tuduModel = new TuduModel();

        echo view('tudu/index',[
            'tudu'=>$tuduModel->findAll(),
        ]);
    }

}
    