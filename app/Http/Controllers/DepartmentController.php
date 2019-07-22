<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use App\Department;

class DepartmentController extends Controller
{

    public function getDepartments()
    {
        $result = Department::select('id', 'departmentName')->get();
        return response()->json($result, 200);
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
        $department = Department::find(Request::input('departmentId'));
        if (!$department) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('department not found');
            return response()->json($message, $status);
        }

        $result = $department->delete();

        if ($result) {
            $status    = config('constants.http_status.HTTP_POST_SUCCESS');
            $message = trans('delete department success');
            return response()->json($message, $status);
        } else {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('delete department fail');
            return response()->json($message, $status);
        }
    }

    public function updateDepartment()
    {
        $validator = Validator::make(Request::all(), [
            'departmentId' => 'required',
            'departmentName' => 'required|max:255',
            'description' => 'max:255',
        ]);

        if ($validator->fails()) {
            $status    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('validate fail');
            return response()->json($message, $status);
        }

        // find department
        $department = Department::find(Request::input('departmentId'));
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
