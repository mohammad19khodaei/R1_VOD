<?php

namespace App\Services;

use App\Article;
use App\Enums\TransactionKey;
use App\Exceptions\NotEnoughChargeException;
use App\Setting;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    /**
     * @param int $userId
     * @param array $parameters
     * @return Article|null
     * @throws NotEnoughChargeException
     */
    public function createArticle(int $userId, array $parameters): ?Article
    {
        $article = null;
        DB::beginTransaction();
        /** @var User $user */
        $user = User::query()->lockForUpdate()->whereKey($userId)->first();
        try {
            if ($user->charge < 0) {
                throw new NotEnoughChargeException();
            }

            /** @var Article $article */
            $article = $user->articles()->create($parameters);
            (new TransactionService())
                ->withdraw($user, Setting::get(TransactionKey::ARTICLE_CREATION_WITHDRAW))
                ->createFactor($article);

            DB::commit();

        } catch (NotEnoughChargeException $exception) {
            DB::commit();
            throw $exception;
        } catch (\Exception $exception) {
            $article = null;
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $article;
    }
}