<?php

namespace App\Controllers;

use Lemon\Database\Database;
use Lemon\Http\Request;
use Lemon\Http\Response;
use Lemon\Http\Session;

class Auth
{
    public function login(Request $request, Database $database, Session $session): Response
    {
        $validation = $request->validate([
            'email' => 'email|max:255',
            'password' => 'max:255',
        ]);

        if (!$validation) {
            return template('login', error: 'Špatná data.');
        }

        $result = $database->query('SELECT * FROM users WHERE email=:email', email:$request->email)
                           ->fetchAll()
                        ;

        if (!$result) {
            return template('login', error: 'Tento uživatel neexistuje.');
        }

        $password = $request[0]['password'];

        if (!password_verify($request->password, $password)) {
            return template('login', error: 'Špatné heslo');
        }

        $session->set('email', $request->email);

        return redirect('/');
    }

    public function register(Request $request, Database $database, Session $session)
    {
        $validation = $request->validate([
            'email' => 'mail|max:255',
            'password' => 'max:255',
            'year' => 'max:16',
        ]);

        $year = $database->query('SELECT id FROM years WHERE name=:name', name: $request->year)
                         ->fetchAll()
                     ;

        if (!$validation || $year) {
            template('register', error: 'Špatná data.');
        }

        $database->query('INSERT INTO users (email, password, year) VALUES (:email, :password, :year)', 
            email: $request->email,
            password: password_hash($request->password, PASSWORD_ARGON2ID),
            year: $year[0]['id'],
        );

        $session->set('email', $request->email);

        return redirect('/');
    }
}
