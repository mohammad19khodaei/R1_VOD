<?php

namespace App\Services;

use App\Article;
use App\Enums\SettingKey;
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
        DB::beginTransaction();
        /** @var User $user */
        $user = User::query()->lockForUpdate()->whereKey($userId)->first();

        try {
            if (!(new UserChargeService($user))->canSubmitArticle()) {
                throw new NotEnoughChargeException();
            }

            /** @var Article $article */
            $article = $user->articles()->create($parameters);

            $transaction = (new TransactionService())->withdraw($user, setting(SettingKey::ARTICLE_CREATION_WITHDRAW));
            (new FactorService($transaction))->create($article);

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