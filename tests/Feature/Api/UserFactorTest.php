<?php

namespace Tests\Feature\Api;

use App\Article;
use App\Enums\SettingKey;
use App\Services\FactorService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserFactorTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_return_an_array_of_factors()
    {
        $articles = factory(Article::class)->times(2)->raw();
        foreach ($articles as $articleData) {
            $article = $this->loggedInUser->articles()->create($articleData);
            $transaction = (new TransactionService())
                ->withdraw($this->loggedInUser, setting(SettingKey::ARTICLE_CREATION_WITHDRAW));
            (new FactorService($transaction))->create($article);
        }

        $factors = $this->loggedInUser->factors()->with('transaction')->get();

        $response = $this->getJson('api/user/factors', $this->headers);
        $response->assertStatus(200)
            ->assertJson([
                'factors' => [
                    [
                        'product_id' => $factors[0]['product_id'],
                        'product_type' => 'Article',
                        'factor_number' => $factors[0]['factor_number'],
                        'amount' => $factors[0]['transaction']['amount']
                    ],
                    [
                        'product_id' => $factors[1]['product_id'],
                        'product_type' => 'Article',
                        'factor_number' => $factors[1]['factor_number'],
                        'amount' => $factors[1]['transaction']['amount']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_an_empty_array_of_fators_when_there_are_none_in_database()
    {
        $response = $this->getJson('/api/user/factors', $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'factors' => []
            ]);
        $this->assertEmpty($response->json()['factors'], 'Expected empty factors array');
    }
}