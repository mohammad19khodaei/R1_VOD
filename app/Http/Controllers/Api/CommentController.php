<?php

namespace App\Http\Controllers\Api;

use App\Article;
use App\Comment;
use App\Exceptions\NotEnoughBalanceException;
use App\Http\Requests\Api\CreateComment;
use App\Http\Requests\Api\DeleteComment;
use App\RealWorld\Transformers\CommentTransformer;
use App\Services\CommentService;
use App\User;

class CommentController extends ApiController
{
    /**
     * CommentController constructor.
     *
     * @param CommentTransformer $transformer
     */
    public function __construct(CommentTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('auth.api')->except('index');
        $this->middleware('auth.api:optional')->only('index');
        $this->middleware('check.user.status')->only(['store']);
    }

    /**
     * Get all the comments of the article given by its slug.
     *
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Article $article)
    {
        $comments = $article->comments()->get();

        return $this->respondWithTransformer($comments);
    }

    /**
     * Add a comment to the article given by its slug and return the comment if successful.
     *
     * @param CreateComment $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateComment $request, Article $article)
    {
        /** @var User $user */
        $user = auth()->user();
        try {
            $comment = (new CommentService())->addComment(
                $article,
                $user,
                $request->input('comment.body')
            );
        } catch (NotEnoughBalanceException $exception) {
            return $this->respondForbidden($exception->getMessage());
        }

        if ($comment === null) {
            return $this->respondInternalError();
        }

        return $this->respondWithTransformer($comment);
    }

    /**
     * Delete the comment given by its id.
     *
     * @param DeleteComment $request
     * @param $article
     * @param Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DeleteComment $request, $article, Comment $comment)
    {
        $comment->delete();

        return $this->respondSuccess();
    }
}
