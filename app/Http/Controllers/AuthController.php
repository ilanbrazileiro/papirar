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
        return view('login');
    }

    public function logout()
    {
        echo "logout";
    }

    public function loginSubmit(Request $request)
    {
        //validação de formulário
        $request->validate(
        //erros
        [
            'text_username' => 'required|email',
            'text_password' => 'required|min:6|max:16'
        ]
        ,
        //mensagens
        [
            'text_username.required' => "É obrigatório um e-mail",
            'text_username.email' => "O Username deve ser um e-mail válido",
            'text_password.required' => "É obrigatório uma senha para acesso",
            'text_password.min' => "O password deve conter no minimo :min caracteres",
            'text_password.max' => "O password deve conter no máximo :max caracteres"
        ]
        );

        //pegar os dados
        $username = $request->input('text_username');
        $password = $request->input('text_password');

        //Checar se o Usuário existe
        $user = User::where('username', $username)
                    ->where('delete_at',NULL)
                    ->first();

        if(!$user){
            return redirect()
            ->back()
            ->withInput()
            ->with('loginError', 'Login ou senha inválidos');
        }

        //verificar se o password está correto
        if(!password_verify($password, $user->password)){
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
                'username' => $user->username
            ]
        ]);

        //buscar todos os usuarios com classe já existente
        //Pode ser feito assim
        //*** $users = User::all()->toArray();
        //Ou pode ser feito assim
        //*** $userModel = new User();
        //*** $users = $userModel->all()->toArray();

        echo 'login com sucesso';

        //teste da base de dados
        /*
        try {
            DB::connection()->getPdo();
            echo "Connection is successfull";
        } catch (\PDOException $e) {
            echo "A conexão falhou:" . $e->getMessage();            
        }
        */
        //VARDUMP
        //dd($request);

    }
}
