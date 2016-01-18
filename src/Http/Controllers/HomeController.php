<?php

namespace DraperStudio\TurnKey\Http\Controllers;

use DraperStudio\TurnKey\Contracts\TurnKeyRepository;
use DraperStudio\TurnKey\Http\Requests\DeleteAccountRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\SessionManager;

class HomeController extends BaseController
{
    protected $auth;

    protected $turnkey;

    public function __construct(Guard $auth, TurnKeyRepository $turnkey, SessionManager $session)
    {
        parent::__construct($session);

        $this->middleware('auth');

        $this->auth = $auth;
        $this->turnkey = $turnkey;
    }

    public function index()
    {
        return view('turnkey::form');
    }

    public function handle(DeleteAccountRequest $request)
    {
        $identifier = config('turnkey.identifier');

        $credentials = [
            $identifier => $this->auth->user()->$identifier,
            'password' => $request->get('password'),
        ];

        if ($this->auth->validate($credentials)) {
            $this->turnkey->erase($this->auth->id());

            $this->auth->logout();

            $next = config('turnkey.feedback.active') ? 'turnkey.feedback'
                                                      : 'turnkey.goodbye';

            $this->flashSessionKey();

            return redirect()->route($next);
        }

        return redirect(config('turnkey.urls.index'))
                    ->withErrors([
                        'invalid' => 'These credentials do not match our records.',
                    ]);
    }
}