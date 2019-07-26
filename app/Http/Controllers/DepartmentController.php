<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use App\Department;

class DepartmentController extends Controller
{

    public function getDepartments()
    {
        $result = Department::get();

        $data = [
            'departments' => $result
        ];
        return response()->json($data, 200);
    }

    public function addDepartment()
    {
        $validator = Validator::make(Request::all(), [
            'departmentName' => 'required|max:255',
            'description' => 'max:255',
        ]);

        if ($validator->fails()) {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        $departmentName = Request::input('departmentName');
        $description = Request::input('description') ? Request::input('description') : '';

        $department = new Department();
        $department->departmentName = $departmentName;
        $department->description = $description;

        $result = $department->save();

        if ($result) {
            $status    = config('constants.http_status.HTTP_POST_SUCCESS');
            $message = trans('save success');
            return response()->json($message, $status);
        } else {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('save fail');
            return response()->json($message, $status);
        }
    }

    public function deleteDepartment()
    {
        $validator = Validator::make(Request::all(), [
            'departmentId' => 'required'
        ]);

        if ($validator->fails()) {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        // find department
        $departmentId = Request::input('departmentId');
        $department = Department::find($departmentId);
        if (!$department) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('department not found');
            return response()->json($message, $status);
        }

        // find user
        $users = User::where('department', $departmentId)->get();

        DB::beginTransaction();
        try {
            if ($users) {
                foreach ($users as $user) {
                    $userDelete = $user->delete();

                    if (!$userDelete) {
                        DB::rollback();
                        $message = 'delete department fail';
                        $code = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        return response()->json($message, $code);
                    }
                }
            }

            $result = $department->delete();
            if ($result) {
                DB::commit();
                $status    = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = trans('delete department success');
                return response()->json($message, $status);
            } else {
                DB::rollback();
                $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('delete department fail');
                return response()->json($message, $status);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $code    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = 'delete department fail';
            return response()->json($message, $code);
        }
    }

    public function updateDepartment()
    {
        $validator = Validator::make(Request::all(), [
            'id' => 'required',
            'departmentName' => 'required|max:255',
            'description' => 'max:255',
        ]);

        if ($validator->fails()) {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        // find department
        $department = Department::find(Request::input('id'));
        if (!$department) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('department not found');
            return response()->json($message, $status);
        }

        $department->departmentName = Request::input('departmentName');
        $department->description = Request::input('description') ? Request::input('description') : '';
        $result = $department->save();

        if ($result) {
            $status    = config('constants.http_status.HTTP_POST_SUCCESS');
            $message = trans('update department success');
            return response()->json($message, $status);
        } else {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('update department fail');
            return response()->json($message, $status);
        }
    }
}
