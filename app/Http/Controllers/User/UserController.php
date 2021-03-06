<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
//        return response()->json(['data' => $users], 200);
        return $this->showAll($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
          'name' => 'required',
          'email' => 'required|email|unique:users',
          'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request,$rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        return response()->json(['data'=> $user],201);




    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(User $user)
    {
//        $user = User::findOrFail($id);
//        return response()->json(['data' => $user], 200);
        return $this->showOne($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */

    public function update(Request $request, User $user)
    {
//        $user = User::findOrFail($id);

        $rules = [
            'email' => 'email|unique:users,email,'. $user->id,
            'password' => 'min:6|confirmed',
            'admin'=> 'in:'. User::REGULAR_USER . ',' . User::ADMIN_USER,
        ];

        if ($request->has('name')){
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email){
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if ($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')){
            if (!$user->isVerified()){
//                return response()->json(['error' => '인증된 사용자만이 admin 필드수정이 가능합니다', 'code' => 409],409);
                return $this->errorResponse('인증된 사용자만이 admin 필드수정이 가능합니다',409);
            }

             $user->admin = $request->admin;
        }

        if (!$user->isDirty()){
//            return response()->json(['error' => '업데이트  정보를 입력해주세요', 'code' => 422],422);
            return $this->errorResponse('업데이트  정보를 입력해주세요',422);
        }
        $user->save();

        return response()->json(['data' => $user],200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroy(User $user)
    {
//        $user = User::findOrFail($id);

        $user->delete();

        return response()->json(['data' => $user],200);

    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('사용자 인증이 완료 되었습니다');
    }

    public function resend(User $user)
    {
        if ($user->isVerified()){
            return $this->errorResponse('이미 인증된 사용자입니다',409);
        }

        retry(5, function() use($user){
            Mail::to($user)->send(new UserCreated($user));
        },100);

        return $this->showMessage('인증메일이 재전송 되었습니다');
    }
}
