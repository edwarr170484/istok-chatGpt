<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;

use App\Models\User;

class ChatController extends Controller
{
    public function questions(Request $request)
    {
        $questions = [
            "Я не знаю с чего начать. Помоги мне...",
            "Я не хочу ничего делать",
            "Мне часто бывает страшно",
            "Меня ничего не радует"
        ];

        return response()->json(['success' => ['answer' => $questions]], 200);
    }

    public function chat(Request $request)
    {
        if($request->filled('message')){
            $messages = [
                'role' => 'user',
                'content' => $request->input('message')
            ];

            try{
                /*$result = OpenAI::chat()->create([
                    'model' => 'gpt-3.5-turbo-instruct',
                    'messages' => $messages,
                ]);

                $result = Arr::get($result, 'choices.0.message')['content'] ?? '';

                return response()->json(['success' => ['answer' => $result]], 200);*/

                $response = Http::withOptions([
                    'proxy' => 'http://167.99.124.118:8080'
                ])->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ])->post(env('OPENAI_ENDPOINT'), [
                    'model' => 'gpt-3.5-turbo-instruct',
                    'prompt' => $request->input('message'),
                    'max_tokens' => 150,
                    'temperature' => 0.7,
                    'stop' => ['\n'],
                ]);

                return response()->json(['success' => ['answer' => $response]], 200); 

            }catch(\Throwable $e){
                return response()->json(['success' => null, 'errors' => ['message' => $e->getMessage()]], 500);
            }
        }

        return response()->json(['success' => ['answer' => 'Введите свое сообщение и отправьте мне в post-параметре message']], 200);
    }
}