<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PharIo\Manifest\NoEmailAddressException;

class TesteController extends Controller
{
    public function teste()
    {

        /*** 
         * 
         * Teste de conecção com o bando de dados
         * 
         * */   
        /*try {
            DB::connection()->getPdo();
            echo 'conexão ok';
        } catch (\PDOException $e) {
            echo 'conexão falhou:'. $e->getMessage();
        } 
            */   

        //echo bcrypt('246135');

    }
}
