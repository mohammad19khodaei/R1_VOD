<?php

namespace App\Services;

use App\User;
use App\Article;
use App\Comment;
use App\Enums\TransactionAmount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NotEnoughChargeException;

class CommentService
{
    public function addComment(Article $article, User $user, string $body): ?Comment
    {
        $comment = null;
        DB::beginTransaction();
        try {
            $commentCount = $user->comments()->lockForUpdate()->count();

            // check if the user can submit a comment
            if ($commentCount >= Comment::MAX_NUMBER_OF_FREE_COMMENT && $user->charge < 0) {
                throw new NotEnoughChargeException();
            }

            /** @var Comment $comment */
            $comment = $article->comments()->create([
                'body' => $body,
                'user_id' => $user->id,
            ]);

            if ($commentCount >= Comment::MAX_NUMBER_OF_FREE_COMMENT) {
                (new TransactionService())
                    ->withdraw($user, TransactionAmount::COMMENT_CREATION_WITHDRAW)
                    ->createFactor($comment);
            }

            DB::commit();
        } catch (NotEnoughChargeException $exception) {
            DB::commit();
            throw new NotEnoughChargeException();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $comment;
    }
}