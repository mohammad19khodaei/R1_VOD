<?php

namespace App\Services;

use App\Article;
use App\Comment;
use App\Enums\SettingKey;
use App\Exceptions\NotEnoughChargeException;
use App\Setting;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * @param Article $article
     * @param User $user
     * @param string $body
     * @return Comment|null
     * @throws NotEnoughChargeException
     */
    public function addComment(Article $article, User $user, string $body): ?Comment
    {
        DB::beginTransaction();
        $commentCount = $user->comments()->lockForUpdate()->count();

        try {
            if (!(new UserChargeService($user))->canSubmitComment($commentCount)) {
                throw new NotEnoughChargeException();
            }

            /** @var Comment $comment */
            $comment = $article->comments()->create([
                'body' => $body,
                'user_id' => $user->id,
            ]);

            if ($commentCount >= setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT)) {
                $transaction = (new TransactionService())
                    ->withdraw($user, setting(SettingKey::COMMENT_CREATION_WITHDRAW));
                (new FactorService($transaction))->create($comment);
            }

            DB::commit();
        } catch (NotEnoughChargeException $exception) {
            DB::commit();
            throw $exception;
        } catch (\Exception $exception) {
            $comment = null;
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $comment;
    }
}