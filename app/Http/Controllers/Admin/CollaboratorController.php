<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class CollaboratorController extends Controller {
 public function index(){ $collaborators=User::query()->whereIn('role',['admin','moderator','finance','marketing','content'])->latest('id')->paginate(20); return view('admin.collaborators.index', compact('collaborators')); }
 public function create(){ return view('admin.collaborators.create'); }
 public function store(Request $request){ $data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','role'=>'required|in:admin,moderator,finance,marketing,content']); User::create($data+['password'=>Hash::make('12345678'),'is_active'=>true]); return redirect()->route('admin.collaborators.index')->with('success','Colaborador criado.'); }
 public function edit(User $collaborator){ return view('admin.collaborators.edit', compact('collaborator')); }
 public function update(Request $request, User $collaborator){ $data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email,'.$collaborator->id,'role'=>'required|in:admin,moderator,finance,marketing,content','is_active'=>'nullable|boolean']); $collaborator->update($data+['is_active'=>$request->boolean('is_active')]); return redirect()->route('admin.collaborators.edit',$collaborator)->with('success','Colaborador atualizado.'); }
 public function destroy(User $collaborator){ $collaborator->delete(); return redirect()->route('admin.collaborators.index')->with('success','Colaborador removido.'); }
}
