<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Services\Operations;
use Illuminate\Http\Request;


class MainController extends Controller
{
    public function index()
    {

       /*
        $id = session('user.id');
       // $user = User::find($id)->toArray();
        $notes = User::find($id)
                        ->notes()
                        ->whereNull('deleted_at')
                        ->get()                        
                        ->toArray();
*/
        //show view
        //return view('home', ['notes'=> $notes]);
        return view('frontend/front_main');

    }

    public function newNote()
    {
        return view('new_note');
    }

    public function newNoteSubmit(Request $request)
    {
        //validação de formulário
        $request->validate(
        //erros
        [
            'text_title' => 'required|min:3|max:200',
            'text_note' => 'required|min:3|max:3000'
        ]
        ,
        //mensagens
        [
            'text_title.required' => "É obrigatório um título para a nota",
            'text_title.min' => "O título da nota deve conter no minimo :min caracteres",
            'text_title.max' => "O título da nota deve conter no máximo :max caracteres",
            'text_note.required' => "É obrigatório um texto para a nota",
            'text_note.min' => "O texto da nota deve conter no minimo :min caracteres",
            'text_note.max' => "O texto da nota deve conter no máximo :max caracteres"
        ]
        );

        //Get user
        $id = session('user.id');

        //Save the dados
        $note = New Note();
        $note->user_id = $id;
        $note->title = $request->text_title;
        $note->text = $request->text_note;
        $note->save();

        //redirect to home
        return redirect()->route('home');
    }

    public function editNote($id)
    {
        $id = Operations::decryptId($id);
        //load note
        $note = Note::find($id);
        //chama a view
        return view('edit_note', ['note' => $note]);
    }

    public function editNoteSubmit(Request $request)
    {
        //validação de formulário
        $request->validate(
        //erros
        [
            'text_title' => 'required|min:3|max:200',
            'text_note' => 'required|min:3|max:3000'
        ]
        ,
        //mensagens
        [
            'text_title.required' => "É obrigatório um título para a nota",
            'text_title.min' => "O título da nota deve conter no minimo :min caracteres",
            'text_title.max' => "O título da nota deve conter no máximo :max caracteres",
            'text_note.required' => "É obrigatório um texto para a nota",
            'text_note.min' => "O texto da nota deve conter no minimo :min caracteres",
            'text_note.max' => "O texto da nota deve conter no máximo :max caracteres"
        ]
        );

        //check note_id Existe
        if($request->note_id == null){
            return redirect()->route('home');
        }

        //decript id
        $id = Operations::decryptId($request->note_id);

        //load note
        $note = Note::find($id);
        //Atualização dos dados       
        $note->title = $request->text_title;
        $note->text = $request->text_note;
        //salvando os dados na base de dados
        $note->save();

        //redirect to home
        return redirect()->route('home');
    }

    public function deleteNote($id)
    {
        $id = Operations::decryptId($id);
        
        $note = Note::find($id);

        return view('delete_note', ['note' => $note]);
    }

    public function deleteNoteConfirm($id)
    {
        $id = Operations::decryptId($id);
        
        $note = Note::find($id);

        //hard delete
        //remove fisicamente o registro
        //$note->delete();

        //soft delete
        //$note->deleted_at = date('Y-m-d H:i:s');
        //$note->save();

        //soft Delete com Use SoftDelete
        $note->delete();

        //força a deleção fisica 
        //$note->forcedelete();

        //redirect to home
        return redirect()->route('home');
    }

}
