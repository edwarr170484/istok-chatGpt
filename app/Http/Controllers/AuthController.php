<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class AuthController extends Controller
{
    public function registration(Request $request) 
    {
        $validator = $this->validation($request);

        if(!$validator->errors()->messages()) {
            $credentials = request(['phone', 'password']);

            $user = User::where('phone', '=', $credentials['phone'])->first();

            if($user){
                return response()->json(['Пользователь с таким номером уже зарегистрирован'], 400);
            }

            $user = new User();
            $user->phone($credentials['phone']);
            $user->password($credentials['password']);

            $user->save();

            $this->login($request);
        }

        return response()->json($validator->errors()->messages(), 422);
    }

    public function login(Request $request)
    {
        $validator = $this->validation($request);

        if(!$validator->errors()->messages()) {
            $credentials = request(['phone', 'password']);

            $user = User::where('phone', '=', $credentials['phone'])->first();

            if(!$user){
                return response()->json(['Неверный логин или пароль'], 401);
            }

            try{
                $token = auth()->login($user);

                if($token){
                    $user->token = $token;
                    $user->save();

                    $this->responseWithToken($token);
                }

                return response()->json(['Авторизация невозможна'], 401);
            }catch(\Throwable $e){
                return response()->json([$e->getMessage()], $e->getCode());
            }
        }

        return response()->json($validator->errors()->messages(), 422);
    }

    public function logout()
    {
        $user = auth()->user();

        if($user){
            $user->token = null;
            $user->save();
        }

        auth()->logout();

        return response()->json(['message' => 'Вы успешно вышли']);
    }

    public function auth(Request $request)
    {
        $user = auth()->user();

        return $this->responseWithToken($user->token);
    }

    private function responseWithToken($token)
    {
        return response()->json([
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL()
                    ], 200);
    }

    private function validation(Request $request)
    {
        $rules = [
            'phone' => 'requred',
            'password' => 'required'
        ];

        $messages = [
            'phone.required' => 'Необходимо ввести номер телефона',
            'password' => 'Необходимо ввести пароль' 
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        return $validator;
    }
}