<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use App\Models\User;

class AuthController extends BaseController {
    use ResponseTrait;

    public function login() {
        $userModel  = new User();
        $request    = $this->request->getJSON();

        $email      = $request->email;
        $password   = $request->password;

        $checking   = $userModel->where('email', $email)->first();

        if (is_null($checking)) {
            return $this->respond([
                'code'      => 401,
                'status'    => 'Unauthorized',
                'message'   => 'Invalid username or password'
            ], 401);
        }

        if ($checking['password'] != sha1($password)) {
            return $this->respond([
                'code'      => 401,
                'status'    => 'Unauthorized',
                'message'   => 'Invalid username or password'
            ], 401);
        }

        // Authentication for JWT 
        $key = getenv('JWT_SEC_KEY');
        $iat = time();
        $exp = $iat + 3600;
        $payload = array(
            "iat"   => $iat,
            "exp"   => $exp,
            "email" => $checking['email']
        );
        
        $token = JWT::encode($payload, $key, 'HS256');

        $response = array(
            'code'          => 201,
            "status"        => "Success",
            "message"       => "Login Successfully",
            "access_token"  =>  $token
        );

        return $this->respond($response, 201);
    }

    public function register() {
        if ($this->request->getJSON() != NULL) {
            $userModel  = new User();
            $request = $this->request->getJSON();
            $request->password = sha1($request->password);
            $data = array(
                'email' => $request->email,
                'password' => $request->password
            );

            $insert = $userModel->save($data);
            return $this->respond(array(
                "code"      => 201,
                "status"    => "Success",
                "message"   => "Registered Successfully",
            ), 201);
        } else {
            $response = array(
                'code'      => 401,
                'status'    => $this->validator->getErrors(),
                'message'   => 'Invalid Inputs'
            );

            return $this->fail([$response], 401);
        }
    }

    public function listUsers() {
        $userModel  = new User();
        $getAll = $userModel->findAll();

        return $this->respond(array(
            "code"      => 201,
            "status"    => "Success",
            "message"   => "List user available",
            "result"    => $getAll
        ));
    }
}
