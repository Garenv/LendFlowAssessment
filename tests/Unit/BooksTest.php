<?php

namespace Tests\Unit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BooksTest extends TestCase
{

    // Test works without internet connection
    public function test_nyt_books_api()
    {
        Http::fake();

        // Change the values of the keys to valid values consistent with the api response.
        Http::get(route('best_sellers_uri'), [
//            'author' => 'David Adams Richards',
//            'isbn' => '0671003542',
//            'title' => '#ASKGARYVEE',
            'offset' => 20
        ]);

        Http::assertSent(function (Request $request) {
            // Make sure the return value is consistent with the variable's value that's being tested in Http::get(...) above
            return $request['offset'] == 20;
        });

    }
}


