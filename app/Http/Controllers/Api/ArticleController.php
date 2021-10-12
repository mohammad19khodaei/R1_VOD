<?php

namespace App\Http\Controllers\Api;

use App\Article;
use App\Exceptions\NotEnoughBalanceException;
use App\Http\Requests\Api\CreateArticle;
use App\Http\Requests\Api\DeleteArticle;
use App\Http\Requests\Api\UpdateArticle;
use App\RealWorld\Filters\ArticleFilter;
use App\RealWorld\Paginate\Paginate;
use App\RealWorld\Transformers\ArticleTransformer;
use App\Services\ArticleService;
use App\Services\TagService;
use Illuminate\Support\Facades\Log;

class ArticleController extends ApiController
{
    /**
     * ArticleController constructor.
     *
     * @param ArticleTransformer $transformer
     */
    public function __construct(ArticleTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('auth.api')->except(['index', 'show']);
        $this->middleware('auth.api:optional')->only(['index', 'show']);
        $this->middleware('check.user.status')->only(['store']);
    }

    /**
     * Get all the articles.
     *
     * @param ArticleFilter $filter
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ArticleFilter $filter)
    {
        $articles = new Paginate(Article::loadRelations()->filter($filter));

        return $this->respondWithPagination($articles);
    }

    /**
     * Create a new article and return the article if successful.
     *
     * @param CreateArticle $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateArticle $request)
    {
        try {
            $article = (new ArticleService())->createArticle(auth()->id(), $request->getParameters());
        } catch (NotEnoughBalanceException $exception) {
            return $this->respondForbidden($exception->getMessage());
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->respondInternalError();
        }

        $inputTags = $request->input('article.tagList', []);
        (new TagService())->addArticleTags($article, $inputTags);

        return $this->respondWithTransformer($article);
    }

    /**
     * Get the article given by its slug.
     *
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Article $article)
    {
        return $this->respondWithTransformer($article);
    }

    /**
     * Update the article given by its slug and return the article if successful.
     *
     * @param UpdateArticle $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateArticle $request, Article $article)
    {
        if ($request->has('article')) {
            $article->update($request->get('article'));
        }

        return $this->respondWithTransformer($article);
    }

    /**
     * Delete the article given by its slug.
     *
     * @param DeleteArticle $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DeleteArticle $request, Article $article)
    {
        $article->delete();

        return $this->respondSuccess();
    }
}
