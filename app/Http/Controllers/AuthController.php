<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class AuthController extends Controller
{
    public function oauth(Request $request)
    {
        $validator = Validator::make($request->all(), ['phone' => 'required'], ['phone.required' => 'Необходимо ввести номер телефона']);

        if(!$validator->errors()->messages()) {
            try{
                $user = User::where('phone', '=', $request->input('phone'))->first();

                if(!$user) {
                    $user = new User();
                    $user->phone = $request->input('phone');
                    $user->password = $request->input('phone');

                    $user->save();
                } 

                $token = auth()->login($user);

                if($token){
                    $user->token = $token;
                    $user->save();

                    return $this->responseWithToken($token);
                }
            }catch(\Throwable $e){
                return response()->json(['success' => null, 'errors' => ['message' => $e->getMessage()]], 500);
            }
        }

        return response()->json(['success' => null, 'errors' => $validator->errors()->messages()], 422);
    }

    public function registration(Request $request) 
    {
        $validator = $this->validation($request);

        if(!$validator->errors()->messages()) {
            $credentials = request(['phone', 'password']);

            $user = User::where('phone', '=', $credentials['phone'])->first();

            if($user){
                return response()->json(['success' => null, 'errors' => ['message' => 'Пользователь с таким номером уже зарегистрирован']], 400);
            }

            $user = new User();
            $user->phone = $credentials['phone'];
            $user->password = $credentials['password'];

            $user->save();

            return $this->login($request);
        }

        return response()->json(['success' => null, 'errors' => $validator->errors()->messages()], 422);
    }

    public function login(Request $request)
    {
        $validator = $this->validation($request);

        if(!$validator->errors()->messages()) {
            $credentials = request(['phone', 'password']);

            $user = User::where('phone', '=', $credentials['phone'])->first();

            if(!$user){
                return response()->json(['success' => null, 'errors' => ['phone' => 'Такого пользователя не существует']], 401);
            }

            if(!$user->validatePassword($credentials['password'])){
                return response()->json(['success' => null, 'errors' => ['password' => 'Неверный пароль']], 401);
            }

            try{
                $token = auth()->login($user);

                if($token){
                    $user->token = $token;
                    $user->save();

                    return $this->responseWithToken($token);
                }

                return response()->json(['success' => null, 'errors' => ['message' => 'Авторизация невозможна']], 401);
            }catch(\Throwable $e){
                return response()->json(['success' => null, 'errors' => ['message' => $e->getMessage()]], 500);
            }
        }

        return response()->json(['success' => null, 'errors' => $validator->errors()->messages()], 422);
    }

    public function logout()
    {
        $user = auth()->user();

        if($user){
            $user->token = null;
            $user->save();
        }

        auth()->logout();

        return response()->json(['success' => 'Вы успешно вышли', 'errors' => null]);
    }

    public function auth(Request $request)
    {
        $user = auth()->user();

        return $this->responseWithToken($user->token);
    }

    private function responseWithToken($token)
    {
        return response()->json(['success' => [
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL()
                    ]], 200);
    }

    private function validation(Request $request)
    {
        $rules = [
            'phone' => 'required',
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