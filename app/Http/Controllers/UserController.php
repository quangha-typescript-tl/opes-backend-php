<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use JWTAuth;
use App\User;
use App\Department;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function login()
    {
        $credentials = Request::only('email', 'password');
        try {
            if ($token = Auth::guard()->attempt($credentials)) {

                $user = User::where('email', Request::input('email'))->first();

                if ($user) {
                    $user->status = 1;
                    $userUpdate = $user->save();

                    if ($userUpdate) {
                        return $this->respondWithToken($token, $user);
                    } else {
                        $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        $message = trans('update status user fail');
                        return response()->json($message, $status);
                    }
                } else {
                    $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                    $message = trans('user not found');
                    return response()->json($message, $status);
                }
            } else {
                return response()->json(['invalid_email_or_password'], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
    }

    public function logout()
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        $code = config('constants.http_status.HTTP_GET_SUCCESS');
        return response()->json('Logout', $code);
    }

    public function refreshToken()
    {
        $data       = Request::input('data');
        $user       = User::getEmailUser($data['email']);
        $new_token  = JWTAuth::fromUser($user);
        return $this->respondWithToken($new_token);
    }

    public function respondWithToken($token, $user)
    {
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard()->factory()->getTTL() * 60
        ]);
    }

    public function getUserSession() {
        $userId = JWTAuth::parseToken()->authenticate()->id;

        $result = User::getUserSession($userId);

        if ($result) {
            $code = config('constants.http_status.HTTP_GET_SUCCESS');
            $data = [
                'user' => $result
            ];
            return response()->json($data, $code);
        } else {
            $message = trans('get userSession fail');
            $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            return response()->json($message, $code);
        }
    }

    public function changePassword() {
        $validate =  Validator::make(Request::all(), [
            'password' => 'required|min:6|max:16|regex:/(^[a-zA-Z0-9_]+$)/',
            'passwordConfirm' => 'required|same:password',
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        $userId = JWTAuth::parseToken()->authenticate()->id;

        $user = User::find($userId);
        $user->password = bcrypt(Request::input('password'));

        $result = $user->save();

        if ($result) {
            $message = 'change password success';
            $code = config('constants.http_status.HTTP_POST_SUCCESS');
            return response()->json($message, $code);
        } else {
            $message = 'change password user fail';
            $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            return response()->json($message, $code);
        }
    }

    public function registerUser()
    {
        $validate =  Validator::make(Request::all(), [
            'userName' => 'required|min:6|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6|max:16',
            'password_confirmation' => 'required|min:6|max:16|same:password',
            'department' => 'required',
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        // check email exit
        $email = User::where('email', trim(Request::input('email')))->first();

        if ($email) {
            $message = 'email is exit';
            $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            return response()->json($message, $code);
        }

        $newUser = new User();
        $newUser->userName = Request::input('userName');
        $newUser->email = Request::input('email');
        $newUser->password = bcrypt(Request::input('password'));
        $newUser->department = Request::input('department');

        $result = $newUser->save();
        if (!$result) {
            $message = 'register user fail';
            $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            return response()->json($message, $code);
        } else {
            $message = 'register user success';
            $code = config('constants.http_status.HTTP_POST_SUCCESS');
            return response()->json($message, $code);
        }
    }

    public function getUsers()
    {
        $result = User::select('users.*', 'de.departmentName')
            ->leftJoin('departments as de', 'de.id', '=', 'users.department')
            ->orderBy('userName')
            ->get();

        if ($result) {
            $data = [
                'users' => $result
            ];
            $code = config('constants.http_status.HTTP_GET_SUCCESS');
            return response()->json($data, $code);
        } else {
            $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = 'get list user fail';
            return response()->json($message, $code);
        }
    }

    public function getDetailUser()
    {
        $data = User::select('users.*', 'de.departmentName')
            ->leftJoin('departments as de', 'de.id', '=', 'users.department')
            ->first();
        return response()->json($data, 200);
    }

    public function addUsers()
    {
        $users = Request::input('users');

        foreach ($users as $user) {
            $validate =  Validator::make($user, [
                'userName' => 'required|min:6|max:255',
                'email' => 'required|email',
                'password' => 'required|min:6|max:16',
//                'password_confirmation' => 'required|min:6|same:password',
                'department' => 'required',
            ]);

            if ($validate->fails()) {
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('validate fail');
                return response()->json($message, $status);
                break;
            }
        }

        $check_add_user = true;
        $message = '';

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                // check email exit
                $email = User::where('email', trim($user['email']))->first();

                if ($email) {
                    $message = 'email is exit';
                    $check_add_user = false;
                    break;
                }

                $newUser = new User();
                $newUser->userName = $user['userName'];
                $newUser->email = $user['email'];
                $newUser->password = bcrypt($user['password']);
                $newUser->department = $user['department'];

                $result = $newUser->save();
                if (!$result) {
                    $check_add_user = false;
                    $message = 'add user fail';
                    break;
                }
            }

            if ($check_add_user) {
                DB::commit();
                $code = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = 'add users success';
                return response()->json($message, $code);
            } else {
                DB::rollback();
                $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                return response()->json($message, $code);
            }

        } catch (\Exception $e) {
            DB::rollback();
            $code    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = 'add user fail 1';
            return response()->json($message, $code);
        }
    }

    public function deleteUser()
    {
        $validate =  Validator::make(Request::all(), [
            "userId" => 'required',
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('userId is null');
            return response()->json($message, $status);
        }

        // find user
        $user = User::find(Request::input('userId'));
        if (!$user) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('user not found');
            return response()->json($message, $status);
        } else {
            $result = $user->delete();

            if ($result) {
                $status = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = trans('delete user success');
                return response()->json($message, $status);
            } else {
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('delete user fail');
                return response()->json($message, $status);
            }
        }
    }

    public function updateUser()
    {
//        $login_user = JWTAuth::parseToken()->authenticate();
        $validate =  Validator::make(Request::all(), [
            "userId" => 'required',
            'userName' => 'required|min:6|max:255',
            'email' => 'required|email',
            'department' => 'required',
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('userId is null');
            return response()->json($message, $status);
        }

        // find user
        $user = User::find(Request::input('userId'));
        if (!$user) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('user not found');
            return response()->json($message, $status);
        } else {
            $user->userName = Request::input('userName');
            $user->email = Request::input('email');
            $user->department = Request::input('department');

            $result = $user->save();

            if ($result) {
                $status = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = trans('update user success');
                return response()->json($message, $status);
            } else {
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('update user fail');
                return response()->json($message, $status);
            }
        }
    }
}
