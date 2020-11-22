<?php

namespace App\Http\Controllers\Axios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\EndRequest;
use App\Http\Requests\Game\StartRequest;
use App\Http\Requests\Game\StepRequest;
use App\Models\Game;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GameController extends Controller
{
    /**
     * @param StartRequest $request
     * @return Application|ResponseFactory|Response
     */
    public function start(StartRequest $request)
    {
        $game = Game::create(['user_id' => auth()->user()->id, 'boxes_count' => $request->boxes_count]);

        return $game?
            response(['message' => 'Game started', 'game_id' => $game->id], 200):
            response(['message' => 'Oops! Something went wrong'], 400);
    }

    /**
     * @param StepRequest $request
     * @return Application|ResponseFactory|Response
     */
    public function step(StepRequest $request)
    {
        $game = Game::where('user_id', '=', auth()->user()->id)->find($request->game_id);

        return $game && $game->steps()->create(['x' => $request->x, 'y' => $request->y])?
            response(['message' => 'Step created'], 200):
            response(['message' => 'Oops! Something went wrong'], 400);
    }

    /**
     * @param EndRequest $request
     * @return Application|ResponseFactory|Response
     */
    public function end(EndRequest $request)
    {
        $game = Game::where('user_id', '=', auth()->user()->id)->find($request->game_id);
        $data = ['game_id' => $request->game_id, 'wined' => $request->wined, 'ended' => true, 'draw' => $request->draw];

        return $game && $game->update($data)?
            response(['message' => 'Game ended'], 200):
            response(['message' => 'Oops! Something went wrong'], 400);
    }
}
