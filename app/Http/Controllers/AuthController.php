<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class AuthController extends Controller
{
    public function login()
    {
        return view('site/site_login');
    }

    public function logout()
    {
        session()->forget('user');
        return redirect()->to('/login');
    }

    public function loginSubmit(Request $request)
    {
        //validação de formulário
        $request->validate(
        //erros
        [
            'email' => 'required|email',
            'senha' => 'required|min:3|max:16'
        ]
        ,
        //mensagens
        [
            'email.required' => "É obrigatório ter um e-mail para o logar",
            'email.email' => "Você deve usar um e-mail válido",
            'senha.required' => "É obrigatório uma senha para acesso",
            'senha.min' => "A senha deve conter no minimo :min caracteres",
            'senha.max' => "A senha deve conter no máximo :max caracteres"
        ]
        );

        //pegar os dados
        $username = $request->input('email');
        $password = $request->input('senha');

        //Checar se o Usuário existe
        $user = User::where('email', $username)
                    ->where('deleted_at',NULL)
                    ->first();

        if(!$user){
            return redirect()
            ->back()
            ->withInput()
            ->with('loginError', 'Login ou senha inválidos');
        }

        //verificar se o password está correto
        if(!password_verify($password, $user->senha)){
            return redirect()
            ->back()
            ->withInput()
            ->with('loginError', 'Login ou senha inválidos');
        }

        //Atualização de ultimo login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        //colocar os dados do usuario na sessão
        session([
            'user' => [
                'id' => $user->id,
                'username' => $user->email
            ]
        ]);

        //buscar todos os usuarios com classe já existente
        //Pode ser feito assim
        //*** $users = User::all()->toArray();
        //Ou pode ser feito assim
        //*** $userModel = new User();
        //*** $users = $userModel->all()->toArray();

        return redirect()->to('/main');

    }

    public function forgout()
    {
        return view('site/site_forgout');
    }

    public function forgoutSubmit(Request $request)
    {
        echo "fougout";
    }
}
