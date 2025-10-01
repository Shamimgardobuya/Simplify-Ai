<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$misral_base_url = config('misral.base_url', 'https://misral.example.com');
$misral_api_key = config('misral.api_key', 'your_api_key_here');
$misral_agent_id = config('misral.default_agent_id', 'your_agent_id_here');
Route::get('/', function (Request $request) {
    $answers = DB::table('answers')->where('session_id', $request->session()->getId())->get();

    return view('chat_app')->with(
        [
            'answers' => $answers
        ]
    );
});


Route::post('/send-message', function (Request $request) use ($misral_base_url, $misral_api_key, $misral_agent_id) {
    try {
        $request->validate([
            'question' => 'required|string|max:100',
        ]);
        $data = [
            "messages" => [
                [
                    "role" => "user",
                    "content" => $request->question
                ]
            ],
            "agent_id" => $misral_agent_id
        ];
        $question_exists = DB::table('answers')
            ->where('question', $request->question)
            ->where('session_id', $request->session()->getId())
            ->exists();
        if ($question_exists) {
            return view('chat_app')->with(
                [
                    'message' => 'You have already asked this question in this session. Please ask a different question.',
                    'answers' => DB::table('answers')->where('session_id', $request->session()->getId())->get()
                ]
            );
        }
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$misral_api_key}",
            'Content-Type' => 'application/json',
        ])->post("{$misral_base_url}/v1/agents/completions", $data)
        ->throw()
        ->json();

        DB::beginTransaction();
        
        DB::table('answers')->insert(
            [
            'question'=> $request->question,
            'answer' => $response['choices'][0]['message']['content'],
            'session_id' => $request->session()->getId()
            ]
        );
        DB::commit();

        $answers = DB::table('answers')->where('session_id', $request->session()->getId())->get();
        return view('chat_app')->with(
            [
                'message' => $response['choices'][0]['message']['content'],
                'answers' => $answers
            ]
            );

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Invalid input', 'details' => $e->errors()], 422);
    } catch (\Illuminate\Http\Client\RequestException $e) {
        DB::rollBack();
        dd($e->getMessage());  
        return response()->json(['error' => 'Failed to communicate with Misral API', 'details' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'An unexpected error occurred', 'details' => $e->getMessage(), 'full_request' => $request->all()], 500);
    }

});