<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder as Macroable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\DocBlock\Description;

class BooksController extends Controller
{

    public function rules($request)
    {
        $response = [
            'status' => 'failed',
            'message' => 'one of the fields are incorrect!'
        ];

        $validation = Validator::make($request->all(), [
            'author'       => 'required|string',
//            'isbn'         => 'required|string',
            'title'        => 'required|string',
            'offset'       => 'required|integer',
        ]);

        if(!$validation->fails()) {
            return [
                'status' => 'success'
            ];
        }

        return $response;

    }

    public function index(Request $request)
    {
        $queryParams = ['api-key' => 'ybK4ratkqz2f1Ve3YoAGV0e0RjgmVo2A'];

        $response = Http::get(env('NYT_BOOKS_ENDPOINT'), $queryParams);

        $results = $response->json()['results'];

        $author = $request->get('author');
        $isbn = $request->get('isbn');
        $title = $request->get('title');


        return array_filter($results, function($element) use ($title, $author) {
            if($element['title'] == $title) {
                return response()->json([
                    $title,
                ]);
            }

            if($element['author'] == $author) {
                return response()->json([
                    $author,
                ]);
            }
        });

    }
}
